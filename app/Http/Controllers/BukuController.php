<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBukuRequest;
use App\Http\Requests\UpdateBukuRequest;
use App\Models\Buku;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BukuController extends Controller
{
    public function index()
    {
        // Ambil data buku dari database beserta kategorinya
        $books = Buku::with('kategori')->get();

        // Kirim variabel 'books' ke view 'buku.index'
        return view('buku.index', ['books' => $books]);
    }

    public function create()
    {
        return $this->index();
    }

    public function store(StoreBukuRequest $request)
    {
        $buku = Buku::create($request->validated())->load('kategori');

        return response()->json([
            'message' => 'Buku baru berhasil ditambahkan!',
            'data' => $buku,
        ], 201);
    }

    public function update(UpdateBukuRequest $request, Buku $buku)
    {
        $buku->update($request->validated());

        return response()->json([
            'message' => 'Data buku berhasil diperbarui!',
            'data' => $buku->load('kategori'),
        ]);
    }

    public function destroy(Buku $buku)
    {
        $buku->delete();

        return response()->json(['message' => 'Buku berhasil dihapus.']);
    }

    public function search(Request $request)
    {
        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:150'],
        ]);

        // $books = DB::select(
        //     "SELECT * FROM bukus WHERE judul = '" . $validated['judul'] . "'"
        // );

        $books = DB::select(
            'SELECT * FROM bukus WHERE judul = ?',
            [$validated['judul']]
        );

        return response()->json($books);
    }

    public function show(Buku $buku): View
    {
        return view('buku.show', [
            'buku' => $buku->load('kategori'),
        ]);
    }
}
