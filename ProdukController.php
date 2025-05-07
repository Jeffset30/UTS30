<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Http\Request;

//TODO: tuliskan kode controller untuk produk anda disini
class ProdukController extends Controller
{
    // Menampilkan daftar produk dengan pagination + pencarian + filter kategori
    public function index(Request $request)
    {
        $query = Produk::query();

        // Jika ada pencarian nama produk
        if ($request->filled('search')) {
            $query->where('nama', 'like', '%' . $request->search . '%');
        }

        // Jika ada filter kategori
        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        // Ambil produk terbaru + pagination + withQueryString agar pagination ikut query pencarian
        $produk = $query->latest()->paginate(10)->withQueryString();

        // Ambil semua kategori unik untuk dropdown filter
        $kategoriList = Produk::select('kategori')->distinct()->pluck('kategori');

        return view('produk', compact('produk', 'kategoriList'));
    }

    // Menampilkan form untuk membuat produk baru
    public function create()
    {
        return view('produk', [
            'action' => route('produk.store'),
            'method' => 'POST',
            'produk' => new Produk()
        ]);
    }

    // Menyimpan produk baru ke dalam database
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required',
            'harga' => 'required|numeric',
            'deskripsi' => 'required',
            'kategori' => 'required',
        ]);

        Produk::create($request->only(['nama', 'harga', 'deskripsi', 'kategori']));

        return redirect()->route('produk.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    // Menampilkan form untuk mengedit produk
    public function edit(Produk $produk)
    {
        return view('produk', [
            'action' => route('produk.update', $produk),
            'method' => 'PUT',
            'produk' => $produk
        ]);
    }

    // Memperbarui data produk
    public function update(Request $request, Produk $produk)
    {
        $request->validate([
            'nama' => 'required',
            'harga' => 'required|numeric',
            'deskripsi' => 'required',
            'kategori' => 'required',
        ]);

        $produk->update($request->only(['nama', 'harga', 'deskripsi', 'kategori']));

        return redirect()->route('produk.index')->with('success', 'Produk berhasil diperbarui.');
    }

    // Menghapus produk
    public function destroy(Produk $produk)
    {
        $produk->delete();
        return redirect()->route('produk.index')->with('success', 'Produk berhasil dihapus.');
    }
}