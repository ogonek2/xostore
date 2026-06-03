<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Media\AdminMediaPaths;
use App\Support\Media\AdminMediaUpload;
use App\Support\Media\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaPreviewController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $path = $request->query('path');

        if (! is_string($path) || ! AdminMediaPaths::isAllowed($path)) {
            abort(404);
        }

        $disk = Storage::disk(Media::disk());

        if (! $disk->exists($path)) {
            abort(404);
        }

        $mime = AdminMediaUpload::mimeTypeForPath($path);

        if (method_exists($disk, 'response')) {
            /** @var StreamedResponse $response */
            $response = $disk->response($path);

            $response->headers->set('Content-Type', $mime);
            $response->headers->set('Cache-Control', 'private, max-age=300');

            return $response;
        }

        return response($disk->get($path), 200, [
            'Content-Type' => $mime,
            'Cache-Control' => 'private, max-age=300',
        ]);
    }
}
