<?php

namespace App\Http\Controllers;

use App\Models\AdminDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AdminDocumentController extends Controller
{
    /**
     * Menampilkan halaman dokumen untuk semua role
     */
 public function index()
{
    // Admin melihat semua, User hanya yang is_visible
    $docs = (Auth::user()->role === 'admin')
            ? AdminDocument::latest()->get()
            : AdminDocument::where('is_visible', true)->latest()->get();

    // Arahkan ke file view yang Anda inginkan (misal di folder user)
    return view('user.dokumen.index', compact('docs'));
}

    /**
     * Simpan dokumen baru (Admin Only)
     */
    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'file' => 'required|mimes:pdf,doc,docx|max:5120'
        ]);

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('admin_docs', 'public');

            AdminDocument::create([
                'judul' => $request->judul,
                'file_path' => $path,
                'uploaded_by' => Auth::id(),
                'is_visible' => true,
            ]);

            return back()->with('success', 'Dokumen berhasil dipublikasikan!');
        }
        return back()->with('error', 'Gagal mengunggah file.');
    }

    /**
     * Sembunyikan atau Tampilkan dokumen (Admin Only)
     */
    public function toggleVisibility($id)
    {
        $doc = AdminDocument::findOrFail($id);
        $doc->is_visible = !$doc->is_visible;
        $doc->save();

        $status = $doc->is_visible ? 'ditampilkan' : 'disembunyikan';

        // Tetap di halaman yang sama agar data tidak hilang dari layar admin
        return back()->with('success', "Dokumen '{$doc->judul}' berhasil $status.");
    }

    /**
     * Proses Download
     */
    public function download($id)
    {
        $doc = AdminDocument::findOrFail($id);

        // Keamanan: Cegah user biasa unduh file yang sedang disembunyikan via URL
        if (Auth::user()->role !== 'admin' && !$doc->is_visible) {
            abort(403);
        }

        if (!Storage::disk('public')->exists($doc->file_path)) {
            return back()->with('error', 'Berkas fisik tidak ditemukan.');
        }

        $ext = pathinfo($doc->file_path, PATHINFO_EXTENSION);
        return Storage::disk('public')->download($doc->file_path, $doc->judul . '.' . $ext);
    }

}
