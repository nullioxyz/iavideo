<?php

namespace App\Http\Controllers;

use App\Support\FrontendAssetUrl;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicMediaController extends Controller
{
    public function image(string $token): StreamedResponse
    {
        return $this->streamMedia($token, 'image/');
    }

    public function video(string $token): StreamedResponse
    {
        return $this->streamMedia($token, 'video/');
    }

    private function streamMedia(string $token, string $requiredMimePrefix): StreamedResponse
    {
        $mediaId = FrontendAssetUrl::decodeMediaToken($token);
        abort_if(! is_numeric($mediaId), 404);

        /** @var Media|null $media */
        $media = Media::query()->find((int) $mediaId);
        abort_if(! $media instanceof Media, 404);

        $mimeType = (string) $media->mime_type;
        abort_if($mimeType === '' || ! str_starts_with($mimeType, $requiredMimePrefix), 404);

        $disk = Storage::disk((string) $media->disk);
        $relativePath = (string) $media->getPathRelativeToRoot();
        abort_if($relativePath === '' || ! $disk->exists($relativePath), 404);

        $stream = $disk->readStream($relativePath);
        abort_if(! is_resource($stream), 404);

        $size = (int) ($media->size ?: 0);
        $headers = [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=31536000, immutable',
            'Content-Disposition' => 'inline; filename="'.addslashes((string) $media->file_name).'"',
        ];

        if ($size > 0) {
            $headers['Content-Length'] = (string) $size;
        }

        return response()->stream(function () use ($stream): void {
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, $headers);
    }
}
