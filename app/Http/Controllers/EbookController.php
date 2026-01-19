<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use App\Models\Ebook;

class EbookController extends Controller
{
    /* ======================================================
       1. LIST ALL EBOOKS
    ====================================================== */
   public function index()
{
    $ebooks = Ebook::latest()
        ->get()
        ->groupBy('title'); // 👈 GROUP BY MANUAL EBOOK NAME

    return view('ebook.index', compact('ebooks'));
}


    /* ======================================================
       2. UPLOAD PDF(s)
    ====================================================== */
    public function store(Request $request)
    {
        $request->validate([
            'ebook_name' => 'required|string|max:255',
            'pdfs.*' => 'required|mimes:pdf|max:20480',
        ]);

        if (!$request->hasFile('pdfs')) {
            return back()->with('error', 'No PDF files uploaded.');
        }

        $files = $request->file('pdfs');
        if (!is_array($files)) {
            $files = [$files];
        }

        // Sort (important for folder upload)
        usort($files, fn ($a, $b) =>
            strcmp($a->getClientOriginalName(), $b->getClientOriginalName())
        );

        $created = 0;
        $manualTitle = $request->ebook_name;
        foreach ($files as $file) {

            if (!$file->isValid()) continue;

            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeTitle    = Str::title(str_replace('_', ' ', $originalName));

            $folder = Str::slug($originalName) . '_' . time() . '_' . Str::random(4);
            $basePath = public_path("ebooks/$folder");

            File::makeDirectory($basePath, 0755, true);

            $pdfName = Str::slug($originalName) . '.pdf';
            $file->move($basePath, $pdfName);

            Ebook::create([
                'title'       => $manualTitle, // ✅ Gate Flipbook
                'file_title'  => $safeTitle,
                'pdf_path'    => "ebooks/$folder/$pdfName",
                'folder_path' => $folder,
                'page_count'  => 0,
                'uploaded_by' => auth()->id() ?? 1,
            ]);

            $created++;
        }

        return back()->with(
            $created ? 'success' : 'error',
            $created ? "$created ebook(s) created successfully." : 'No valid PDF files found.'
        );
    }
// public function store(Request $request)
// {
//     $request->validate([
//         'title'   => 'required|string|max:255', // ✅ manual ebook name
//         'pdfs.*'  => 'required|mimes:pdf|max:20480',
//     ]);

//     if (!$request->hasFile('pdfs')) {
//         return back()->with('error', 'No PDF files uploaded.');
//     }

//     $files = $request->file('pdfs');
//     if (!is_array($files)) {
//         $files = [$files];
//     }

//     // Sort files (important for folder upload)
//     usort($files, fn ($a, $b) =>
//         strcmp($a->getClientOriginalName(), $b->getClientOriginalName())
//     );

//     $created = 0;

//     // ✅ Manual Ebook Name (same for all files)
//     $manualTitle = $request->title;

//     // ✅ Common file title (from FIRST file only)
//     $firstFileName = pathinfo($files[0]->getClientOriginalName(), PATHINFO_FILENAME);
//     $fileTitle = Str::slug($firstFileName); // flipbook

//     // ✅ ONE folder for all files
//     $folder = $fileTitle . '_' . time() . '_' . Str::random(4);
//     $basePath = public_path("ebooks/$folder");

//     File::makeDirectory($basePath, 0755, true);

//     foreach ($files as $file) {

//         if (!$file->isValid()) continue;

//         $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
//         $pdfName = Str::slug($originalName) . '.pdf';

//         $file->move($basePath, $pdfName);

//         Ebook::create([
//             'title'       => $manualTitle,   // ✅ Gate Flipbook
//             'file_title'  => $fileTitle,     // ✅ flipbook (common)
//             'pdf_path'    => "ebooks/$folder/$pdfName",
//             'folder_path' => $folder,
//             'page_count'  => 0,
//             'uploaded_by' => auth()->id() ?? 1,
//         ]);

//         $created++;
//     }

//     return back()->with(
//         $created ? 'success' : 'error',
//         $created ? "$created file(s) added to ebook successfully." : 'No valid PDF files found.'
//     );
// }

    /* ======================================================
       3. CORE METHOD – ENSURE PAGES EXIST
       (USED BY VIEW + SHARE)
    ====================================================== */
    public function ensurePagesExist(Ebook $ebook)
    {
        $pagesPath = public_path("ebooks/{$ebook->folder_path}/pages");

        // ✅ Pages already exist
        if (is_dir($pagesPath) && count(glob($pagesPath . '/*.jpg')) > 0) {
            return true;
        }

        $pdfFile = public_path($ebook->pdf_path);
        if (!file_exists($pdfFile)) {
            return false;
        }

        if (!file_exists($pagesPath)) {
            mkdir($pagesPath, 0777, true);
        }

        $magick = "C:\\Program Files\\ImageMagick-7.1.2-Q16-HDRI\\magick.exe";
        if (!file_exists($magick)) {
            abort(500, 'ImageMagick not found');
        }

        $output = $pagesPath . "\\page_%03d.jpg";
        exec("\"$magick\" -density 200 \"$pdfFile\" -quality 92 \"$output\"");

        return count(glob($pagesPath . '/*.jpg')) > 0;
    }

    /* ======================================================
       4. VIEW FLIPBOOK (ADMIN / DASHBOARD)
    ====================================================== */
    public function view($id)
    {
        $ebook = Ebook::findOrFail($id);

        if (!$this->ensurePagesExist($ebook)) {
            abort(500, 'PDF conversion failed');
        }

        $images = glob(public_path("ebooks/{$ebook->folder_path}/pages/*.jpg"));
        natsort($images);

        $ebook->page_count = count($images);
        $ebook->save();

        $pages = array_map(
            fn ($img) => asset("ebooks/{$ebook->folder_path}/pages/" . basename($img)),
            $images
        );

        return view('ebook.flipbook', compact('ebook', 'pages'));
    }

    /* ======================================================
       5. PUBLIC VIEW (OPTIONAL)
    ====================================================== */
    public function publicView($id)
    {
        return $this->view($id);
    }

    /* ======================================================
       6. DELETE EBOOK
    ====================================================== */
    public function delete($id)
    {
        $ebook = Ebook::findOrFail($id);

        $folderPath = public_path("ebooks/{$ebook->folder_path}");
        if (File::exists($folderPath)) {
            File::deleteDirectory($folderPath);
        }

        $ebook->delete();

        return back()->with('success', 'Ebook deleted successfully!');
    }
}
