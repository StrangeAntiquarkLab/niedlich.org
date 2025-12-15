<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

use Illuminate\Validation\ValidationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

use App\Http\Requests\StoreMediaRequest;

use App\Models\Media;
use App\Models\Enums\MediaType;
use App\Models\Enums\MimeType;
use App\Models\Tag;
use App\Models\TagSynonym;

class MediaController extends Controller
{
    public const UP_SERVICE = 'cf'; // cf = cloudflare, fs = server filesystem (not yet implemented)

    private UploadedFile $file;
    private MimeType $mimeType;
    private MediaType $mediaType;
    private array $data;
    private string $cf_id;
    // private string $url; // If server filesystem is used (Not yet implemented)

    private Media $media;
    private array $nonExistentTags = [];

    public function upload(StoreMediaRequest $request) {
        try {
            $this->createMedia($request->file('file'), $request->validated());

            if ($request->filled('tags')) {
                $this->addTags($request->tags);
            }

            return response()->json([
                'message' => 'Media uploaded successfully',
                'data' => $this->media->load(['id', 'species_id', 'creator', 'type', 'cf_id', 'url', 'description', 'source', 'tags']),
                'nonExistentTags' => $this->nonExistentTags,
            ], 201);
        } catch (ValidationException $e) {

            throw $e;
        } catch (\Exception $e) {
            // All other errors
            return response()->json([
                'message' => 'Failed to upload media',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }

    }

    private function addTags($tags) {
        foreach ($tags as $tag) {
            if (Tag::where('name', $tag)->exists()) {
                $this->media->tags()->attach(Tag::where('name', $tag)->first());
            } else if (TagSynonym::where('synonym', $tag)->exists()) {
                $this->media->tags()->attach(TagSynonym::where('synonym', $tag)->first()->tag);
            } else {
                $this->nonExistentTags[] = $tag;
            }
        }
    }

    private function createMedia(UploadedFile $file, array $data)
    {
        $this->file = $file;
        $this->data = $data;

        $this->validateType();
        $this->checkForAnimation();

        $this->store();

        $this->media = Media::create([
            'species_id' => $this->data['species_id'],
            'creator' => auth()->id,
            'type' => $this->mediaType->value,
            'cf_id' => $this->cf_id,
            'description' => $this->data['description'] ?? null,
            'source' => $this->data['source'] ?? null,
        ]);
    }

    private function store(): void {
        switch (self::UP_SERVICE) {
            case 'cf': $this->uploadToCF(); break;
            //case 'fs': $this->uploadToFS(); break;
            default:
                throw ValidationException::withMessages([
                    'file' => ["An Error occured while uploading your file, this is a server issue. Maybe try again later or report the issue on GitHub."],
                ]);
        }
    }

    private function uploadToCF(): void {
        $ext = $this->mimeType->extension();
        if ($this->mimeType->isImage()) {
            $name = time() . '-' . uniqid('img_', false) . '.' . $ext;
            $endpoint = 'images/v1';
        } else {
            $name = time() . '-' . uniqid('vid_', false) . '.' . $ext;
            $endpoint = 'stream';
        }

        $client = new Client();

        $apiToken = env('CF_API_KEY');
        $accountId = env('CF_ACC_ID');

        try {
            $response = $client->post("https://api.cloudflare.com/client/v4/accounts/{$accountId}/{$endpoint}", [
                'headers' => [
                    'Authorization' => "Bearer {$apiToken}",
                ],
                'multipart' => [
                    [
                        'name' => 'file',
                        'contents' => fopen($this->file->getRealPath(), 'rb'),
                        'filename' => $name
                    ]
                    ],
                'timeout' => 30
            ]);

            $body = json_decode($response->getBody(), true);

            if (!isset($body['success']) || !$body['success'] || !isset($body['result']) || !isset($body['result']['id'])) {
                Log::error('Cloudflare Images Upload Error: ' . $body['errors']);
                throw ValidationException::withMessages([
                    'file' => ["An Error occured while uploading your file, this is a server issue. Maybe try again later or report the issue on GitHub."],
                ]);
            }

            $id = $body['result']['id'];

            if ($this->mimeType->isImage()) {
                $this->cf_id = $id;
            } else {

                // Downloads have to be. The uid in the original result gives us only a player, not a file, if not activated
                $activateDownloads = $client->post("https://api.cloudflare.com/client/v4/accounts/{$accountId}/stream/{$id}/downloads/default", [
                    'headers' => [
                        'Authorization' => "Bearer {$apiToken}",
                    ]
                ]);
                $downloadInfo = json_decode($activateDownloads->getBody(), true);
                $this->cf_id = $id;
            }

        } catch (RequestException $e) {
            Log::error('HTTP Request to Cloudflare Images failed: ' . $e->getMessage(), [
                'request' => $e->getRequest(),
                'response' => $e->getResponse(),
            ]);
            throw ValidationException::withMessages([
                'file' => ["An Error occured while uploading your file, this is a server issue. Maybe try again later or report the issue on GitHub."],
            ]);
        } catch (\Exception $e) {
            Log::error('Internal Error while uploading to Cloudflare Image: ' . $e->getMessage());
            throw ValidationException::withMessages([
                'file' => ["An Error occured while uploading your file, this is a server issue. Maybe try again later or report the issue on GitHub."],
            ]);
        }
    }

    private function validateType(): void
    {
        $detectedMime = $this->file->getMimeType();
        $this->mimeType = MimeType::tryFrom($detectedMime);

        if (!$this->mimeType) {
            throw ValidationException::withMessages([
                'file' => ["Filetype not supported: {$detectedMime}"],
            ]);
        }

        $this->mediaType = MediaType::mimeToMediaType($this->mimeType);
    }

    private function checkForAnimation(): void
    {
        if (($this->mimeType == MimeType::WEBP) || ($this->mimeType == MimeType::GIF)) {
            $this->validateAnimation();
        }
    }

    private function validateAnimation(): void
    {
        match($this->mimeType) {
            MimeType::GIF => $this->checkGif(),
            MimeType::WEBP => $this->checkWebP(),
            default => false
        };
    }

    private function checkGif(): void
    {
        if (!$this->isAnimatedGif()) {
            throw ValidationException::withMessages([
                'file' => ["You can only upload GIFs as animation, not static images. Use JPEG/PNG instead."],
            ]);
        }
    }

    private function checkWebP(): void
    {
        if ($this->isAnimatedWebP()) {
            $this->convertWebPToGif();
        }
    }

    private function isAnimatedGif(): bool
    {
        $handle = fopen($this->file->getRealPath(), "rb");
        if (!$handle) {
            Log::error('File could not be opened for reading: ' . $this->file->getRealPath());
            throw ValidationException::withMessages([
                'file' => ["An Error occured while processing your GIF, if you think your file is okay, please open an Issue on GitHub."]
            ]);
        }

        $frames = 0;
        $buffer = '';

        while (!feof($handle) && $frames < 2) {
            $buffer .= fread($handle, 1024);

            // Each frame is GCE + Image Descriptor
            $frames += preg_match_all(
                "/\x21\xF9\x04.{4}\x00\x2C/s",  //Absolutely include x21, as Photoshop uses it in its headers, even thought it is not Spec!
                $buffer
            );

            // PRevent memory issues
            $buffer = substr($buffer, -20);
        }

        fclose($handle);

        return $frames >= 2;
    }

    private function isAnimatedWebP(): bool
    {
        $handle = fopen($this->file->getRealPath(), "rb");
        if (!$handle) {
            Log::error('File could not be opened for reading: ' . $this->file->getRealPath());
            throw ValidationException::withMessages([
                'file' => ["An Error occured while processing your WebP, if you think your file is okay, please open an Issue on GitHub."]
            ]);
        }

        $frames = 0;
        $buffer = '';

        while (!feof($handle) && $frames < 2) {
            $buffer .= fread($handle, 1024);

            // Count animation frame chunks
            $frames += substr_count($buffer, 'ANMF');

            // Prvent memory issues
            $buffer = substr($buffer, -16);
        }

        fclose($handle);

        return $frames >= 2;
    }

    private function convertWebPToGif(): void
    {
        $sourcePath = $this->file->getRealPath();
        $tmpPath = $sourcePath.'.gif';

        $imagick = new \Imagick();
        if (!$imagick->readImage($sourcePath)) {
            Log::error('Imagick could not read the WebP file: ' . $sourcePath);
            throw ValidationException::withMessages([
                'file' => ["Your WebP file is animated. We can't accept animated WebP files and couldn't convert it to a GIF file, because of an error (While reading the File).
                    Please upload a static image instead or convert it to a GIF manually."],
            ]);
        }

        if ($imagick->getNumberImages() > 1) {
            try {
                $imagick = $imagick->coalesceImages();
            } catch (\ImagickException $e) {
                Log::error('Imagick could not coalesce the WebP file: ' . $sourcePath);
                throw ValidationException::withMessages([
                    'file' => ["Your WebP file is animated. We can't accept animated WebP files and couldn't convert it to a GIF file, because of an error (While optimizing the WebP).
                        Please upload a static image instead or convert it to a GIF manually."],
                ]);
            }
        }

        foreach ($imagick as $frame) {
            $e = $frame->setImageFormat('gif');
            $e = $e && $frame->setImageCompression(\Imagick::COMPRESSION_LZW);
            $e = $e && $frame->setImageDispose(\Imagick::DISPOSE_BACKGROUND);
            if (!$e) {
                Log::error('Imagick could not convert the WebP file: ' . $sourcePath);
                throw ValidationException::withMessages([
                    'file' => ["Your WebP file is animated. We can't accept animated WebP files and couldn't convert it to a GIF file, because of an error (While converting the Frames).
                        Please upload a static image instead or convert it to a GIF manually."],
                ]);
            }
        }

        if (!$imagick->optimizeImageLayers()) {
            Log::error('Imagick could not optimize the converted GIF from WebP file: ' . $sourcePath);
            throw ValidationException::withMessages([
                'file' => ["Your WebP file is animated. We can't accept animated WebP files and couldn't convert it to a GIF file, because of an error (While optimizing the GIF).
                    Please upload a static image instead or convert it to a GIF manually."],
            ]);
        }

        if (!$imagick->writeImages($tmpPath, true)) {
            unlink($tmpPath);
            Log::error('Imagick could not write the converted GIF from WebP file: ' . $tmpPath);
            throw ValidationException::withMessages([
                'file' => ["Your WebP file is animated. We can't accept animated WebP files and couldn't convert it to a GIF file, because of an error (While writing the File).
                    Please upload a static image instead or convert it to a GIF manually."],
            ]);
        }
        $imagick->clear();

        // 4 MB cap
        // TODO: Make Configurable somewhere
        if (filesize($tmpPath) > 4 * 1024 * 1204) {
            unlink($tmpPath);
            throw ValidationException::withMessages([
                'file' => ["Your WebP file is animated. We can't accept animated WebP files and couldn't convert it to a GIF file,
                    because the resulting file would exceed the size limit of 4 MB. Please upload a static image instead or convert it to a smaller GIF manually."],
            ]);
        }

        // Atomic replace
        if (!rename($tmpPath, $sourcePath)) {
            Log::error('Imagick could not rename the converted GIF from WebP file: ' . $tmpPath . ' to ' . $sourcePath);
            throw ValidationException::withMessages([
                'file' => ["Your WebP file is animated. We can't accept animated WebP files and couldn't convert it to a GIF file, because of an error (While saving the File).
                    Please upload a static image instead or convert it to a GIF manually."],
            ]);
        }

        $this->validateType();
    }
}
