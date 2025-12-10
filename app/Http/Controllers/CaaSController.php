<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;


use App\Models\Tag;
use App\Models\TagSynonym;
use App\Models\Media;
use Illuminate\Http\RedirectResponse;
use Nette\Utils\Json;

class CaaSController extends Controller
{
    private $request;

    private $tags = null;
    private $options = [];

    // The default type of media is an image
    // 'video' will return any gif, webm or mp4
    // 'image' will return any gif or picture
    protected static $typePrefixes = ['image', 'video', 'pic', 'gif', 'webm', 'mp4', 'fact', 'factimage', 'teapot'];
    private $type = 'image';

    public function serve($path, Request $request): JsonResponse | StreamedResponse | RedirectResponse
    {
        $this->request = $request;

        // Split the supplied path into segments
        $segments = explode('/', trim($path, '/'));

        if (count($segments) >= 100) {
            return $this->errorResponse(413, "Too many tags or options");
        }



        if (in_array($segments[0], self::$typePrefixes)) {
            $this->type = array_shift($segments);
        }

        if ($this->type == 'teapot') {
            return $this->errorResponse(418, "I'm a teapot");
        }

        // Species is the first segment, but if no segment is specified, use "cat" as a default
        $species = empty($segments) ? "random" : array_shift($segments);

        // Check if the species is in our database | 'random' returns a random species
        if (!$this->speciesExists($species) && $species != 'random') {
            return $this->errorResponse(404, "Unknown species: " . $species);
        }

        // Parse tags/options
        $optionIndex = array_search('options', $segments);
        $rawOptions = [];
        if ($optionIndex !== false) {
            $rawTags = array_slice($segments, 0, $optionIndex);
            $optionsInput = array_slice($segments, $optionIndex + 1);

            // Parse the options:key values
            foreach ($optionsInput as $opt) {
                if (str_contains($opt, ':')) {
                    [$key, $value] = explode(':', $opt, 2); //Important for docs: Only : per Option! If multiple parameters, they are divided differently. Example: size:100x100
                    $rawOptions[$key] = $value;
                }
            }
        } else {
            $rawTags = $segments;
        }

        // Process Tags and Options
        $resolvedTags = $this->resolveTags($rawTags);
        $this->setOptions($rawOptions);

        // Query Media
        $mediaQuery = Media::query();

        // Query: Species handling
        if ($species !== 'random') {

            // Get allowed species IDs (root and children)
            $speciesIds = $this->getSpeciesAndChildrenIds($species);

            if (!$speciesIds) {
                return $this->errorResponse(404, "Unknown species: " . $species);
            }

            $mediaQuery->whereIn('species_id', $speciesIds);
        }

        // Query: Tags (optional)
        if ($resolvedTags->isNotEmpty()) {
            $mediaQuery->whereHas('tags', function($q) use ($resolvedTags) {
                $q->whereIn('tags.id', $resolvedTags->pluck('id'));
            });
        }

        // Query: Must have URL or CF_ID
        $mediaQuery->where(function($q) {
            $q->whereNotNull('url')->orWhereNotNull('cf_id');
        });

        // Query: Random Result
        $media = $mediaQuery->first();

        if (!$media) {
            return $this->errorResponse(404, "No media found for the specified tags/species");
        }

        // TODO: Make the Cloudflare Accounthash a Setting
        $mediaUrl = $media->url ?? ($media->cf_id ? "https://imagedelivery.net/Cf5c8L40aEGMfUQww1u2A/{$media->cf_id}/public" : null);

        if (!$mediaUrl) {
            return $this->errorResponse(500, "Malformed database entry, try again later");
        }

        // Check if user is accessing with a browser directly and wants an actual image instead of a json response
        $isBrowserImageRequest = !$request->expectsJson() && str_contains($request->header('Accept'), 'image');

        if ($isBrowserImageRequest) {

            // Temporary fix
            // TODO: Signed URLs to fetch the images
            return redirect()->away($mediaUrl);

            // Try downloading the media file
            $imageContent = @file_get_contents($mediaUrl);

            // If download fails, stream the an error_cat instead
            if (!$imageContent) {
                $fallbackUrl = "https://http.cat/523.jpg";
                $imageContent = @file_get_contents($fallbackUrl);
                $mime = "image/jpeg";
            } else {
                // Detect MIME type of media
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_buffer($finfo, $imageContent);
                finfo_close($finfo);
            }

            return response()->stream(function () use ($imageContent) {
                echo $imageContent;
            }, 200, [
                'Content-Type' => $mime,
                'Content-Length' => strlen($imageContent),
                'Cache-Control' => 'no-cache',
            ]);

        }

        $responseData = [
            'id' => $media->id,
            'type' => $media->type,
            'species' => $species,
            'url' => $mediaUrl,
            'decription' => $media->description,
            'source' => $media->source,
            'tags' => $media->tags->pluck('name'),
        ];

        return response()->json($responseData, 200);

    }

    private function getSpeciesAndChildrenIds($species) {
        $root = \App\Models\Species::where('name', $species)->first();

        if (!$root) {
            return null;
        }

        $all = collect([$root->id]);

        $queue = [$root];

        while ($queue) {
            $current = array_shift($queue);

            $children = \App\Models\Species::where('parent_id', $current->id)->get();

            foreach ($children as $child) {
                $all->push($child->id);
                $queue[] = $child;
            }
        }

        return $all->unique();
    }

    private function resolveTags($rawTags): \Illuminate\Support\Collection
    {
        $resolved = collect();

        foreach ($rawTags as $suppliedTagName) {
            $suppliedTagName = trim(strtolower($suppliedTagName));

            if (!$suppliedTagName) continue;

            // Try to find tag directly
            $tag = Tag::where('name', $suppliedTagName)->first();

            // If no tag has been found, check synonyms of tags
            if (!$tag) {
                $tag = Tag::whereHas('synonyms', function($q) use ($suppliedTagName) {
                    $q->where('synonym', $suppliedTagName);
                })->first();
            }

            // Skip further processing if no tag has been found
            if (!$tag) continue;

            // Check if tag is whitelisted for the current type, skip if not (If no whitelist exists, it is allowed)
            if ($tag->whitelist && !in_array($this->type, $tag->whitelist)) continue;

            if (!$resolved->contains($tag)) {
                $resolved->push($tag);
            }
        }

        $this->tags = $resolved;
        return $this->tags;
    }

    private function setOptions($rawOptions): void
    {
        // TODO: Process Options
    }

    private function speciesExists(string $species): bool
    {
        //TODO: Implement DB Check
        return true;
    }

    private function errorResponse(int $code, string $message): JsonResponse | StreamedResponse
    {
        $request = $this->request;

        // Check if user is accessing with a browser directly and wants an actual image instead of a json response
        $isBrowserImageRequest = !$request->expectsJson() && str_contains($request->header('Accept'), 'image');

        if ($isBrowserImageRequest) {
            $mediaUrl = "https://http.cat/" . $code . ".jpg";
            $imageContent = @file_get_contents($mediaUrl);
            $mime = "image/jpeg";


            return response()->stream(function () use ($imageContent) {
                echo $imageContent;
            }, 200, [
                'Content-Type' => $mime,
                'Content-Length' => strlen($imageContent),
                'Cache-Control' => 'no-cache',
            ]);

        }

        return response()->json([
            'error' => $message,
            'code' => $code,
            'error_cat' => 'https://http.cat/images/' . $code . '.jpg'
        ], $code);
    }
}
