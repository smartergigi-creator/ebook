<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ebook;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Http\Controllers\EbookController;

class EbookShareController extends Controller
{
    /* ======================================================
       1. GENERATE SHARE LINK
    ====================================================== */
    public function generate($id)
    {
        $ebook = Ebook::find($id);

        if (!$ebook) {
            return response()->json(['error' => 'Ebook not found'], 404);
        }

        $ebook->update([
            'share_token'      => Str::random(40),
            'share_expires_at' => now()->addDays(7),
            'share_enabled'    => 1,
        ]);

        return response()->json([
            'publicLink' => url('/share/' . $ebook->share_token),
            'expires_at' => $ebook->share_expires_at
        ]);
    }

    /* ======================================================
       2. SHARE VIEW (MAIN ENTRY)
    ====================================================== */
    public function view($token)
    {
        $ebook = Ebook::where('share_token', $token)
            ->where('share_enabled', true)
            ->first();

        if (!$ebook) {
            return response()->view('ebook/errors.share-invalid', [], 403);
        }

        if ($ebook->share_expires_at && Carbon::now()->gt($ebook->share_expires_at)) {
            return response()->view('ebook/errors.share-expired', [], 403);
        }

        // 🔥 ENSURE PAGES EXIST (KEY FIX)
        $ebookController = new EbookController();

        if (!$ebookController->ensurePagesExist($ebook)) {
            return view('ebook.loading', compact('ebook'));
        }

        // Load pages
        $pagesPath = public_path("ebooks/{$ebook->folder_path}/pages");

        $pages = collect(glob($pagesPath . '/*.jpg'))
            ->sort()
            ->map(fn ($img) =>
                asset("ebooks/{$ebook->folder_path}/pages/" . basename($img))
            )
            ->values()
            ->toArray();

        // 🚨 FINAL SAFETY
        if (count($pages) === 0) {
            return view('ebook.loading', compact('ebook'));
        }

        return view('ebook.flipbook', compact('ebook', 'pages'));
    }
}
