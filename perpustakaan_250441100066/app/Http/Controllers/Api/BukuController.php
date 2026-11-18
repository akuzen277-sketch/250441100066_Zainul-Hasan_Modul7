<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BukuResource;
use App\Models\Buku;

class BukuController extends Controller
{
    public function index()
    {
        return BukuResource::collection(
            Buku::with('kategori')->latest()->get()
        );
    }

    public function show(int $id)
    {
        $buku = Buku::with('kategori')->find($id);

        if (! $buku) {
            return response()->json(['message' => 'not found'], 404);
        }

        return new BukuResource($buku);
    }
}
