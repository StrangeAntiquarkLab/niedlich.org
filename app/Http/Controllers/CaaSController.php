<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class CaaSController extends Controller
{
    public function serve($path, Request $request)
    {
        // Split the supplied path into segments
        $segments = explode('/', trim($path, '/'));

        if (count($segments) >= 100) {
            return $this->errorResponse(413, "Too many segments");
        }

        // The default type of media is an image
        // 'video' will return any gif, webm or mp4
        // 'image' will return any gif or picture
        $typePrefixes = ['image', 'video', 'pic', 'gif', 'webm', 'mp4', 'fact', 'factimage'];
        $type = 'image';

        if (in_array($segments[0], $typePrefixes)) {
            $type = array_shift($segments);
        }

        if ($type == 'teapot') {
            // Easteregg, don't remove
            return $this->errorResponse(418, "I'm a teapot");
        }

        // Species is the first segment, but if no segment is specified, use "cat" as a default
        if (empty($segments)) {
            $species = "cat";
        } else {
            $species = array_shift($segments);
        }

        // Check if the species is in our database
        if (!$this->speciesExists($species)) {
            return $this->errorResponse(404, "Unknown species: " . $species);
        }

        // Parse tags/options
        $tags = [];
        $options = [];

        $optionIndex = array_search('options', $segments);
        if ($optionIndex !== false) {
            $tags = array_slice($segments, 0, $optionIndex);
            $optionsRaw = array_slice($segments, $optionIndex + 1);

            // Parse the options:key values
            foreach ($optionsRaw as $opt) {
                if (str_contains($opt, ':')) {
                    [$key, $value] = explode(':', $opt, 2); //Important for docs: Only : per Option! If multiple parameters, they are divided differently. Example: size:100x100
                    $options[$key] = $value;
                }
            }
        } else {
            $tags = $segments;
        }

        // TODO: Resolve tags that are synonyms
        // TODO: Apply exclusions for tags prefixed with '-'

        // TODO: Query DB for random matching image, video or fact
        // For now just return placeholder JSON
        return response()->json([
            'type' => $type,
            'species' => $species,
            'tags' => $tags,
            'options' => $options
        ]);
    }

    private function speciesExists(string $species): bool
    {
        //TODO: Implement DB Check
        return true;
    }

    private function errorResponse(int $code, string $message): JsonResponse
    {
        return response()->json([
            'error' => $message,
            'code' => $code,
            'error_cat' => 'https://http.cat/images/' . $code . '.jpg'
        ], $code);
    }
}
