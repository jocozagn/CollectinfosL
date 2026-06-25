<?php

namespace App\Http\Controllers;

use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ContentMediaController extends Controller
{
    public function stream(Request $request, Content $content): StreamedResponse
    {
        $this->ensureAccess($content);

        $path = $content->localMediaPath();

        if (! $path || ! Storage::disk('public')->exists($path)) {
            abort(404, 'Fichier média introuvable.');
        }

        $disposition = $request->boolean('download') ? 'attachment' : 'inline';

        return Storage::disk('public')->response($path, $content->downloadFilename(), [
            'Content-Type' => Storage::disk('public')->mimeType($path) ?: 'application/octet-stream',
            'Content-Disposition' => $disposition.'; filename="'.$content->downloadFilename().'"',
        ]);
    }

    public function download(Content $content): StreamedResponse
    {
        $this->ensureAccess($content);

        $path = $content->localMediaPath();

        if (! $path || ! Storage::disk('public')->exists($path)) {
            abort(404, 'Fichier introuvable pour le téléchargement.');
        }

        return Storage::disk('public')->download($path, $content->downloadFilename());
    }

    private function ensureAccess(Content $content): void
    {
        if (! $content->isPublished()) {
            abort(404);
        }

        if (! $content->userHasAccess(auth()->user())) {
            abort(403, 'Connectez-vous et achetez ce contenu pour y accéder.');
        }
    }
}
