<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProdukController extends Controller
{
    // Menampilkan daftar produk dengan filter, pencarian, dan sorting
    public function index(Request $request)
    {
        $query = Produk::query();

        // Pencarian berdasarkan nama/deskripsi
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('nama', 'like', "%{$searchTerm}%")
                  ->orWhere('deskripsi', 'like', "%{$searchTerm}%");
            });
        }

        // Filter kategori
        if ($request->filled('kategori') && $request->kategori !== 'all') {
            $query->where('kategori', $request->kategori);
        }

        // Filter harga
        if ($request->filled('min_harga')) {
            $query->where('harga', '>=', $request->min_harga);
        }
        if ($request->filled('max_harga')) {
            $query->where('harga', '<=', $request->max_harga);
        }

        // Sorting
        $sortField = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_dir', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $produk = $query->paginate(15)->withQueryString();

        // Data tambahan
        $kategoriList = Produk::select('kategori')->distinct()->orderBy('kategori')->pluck('kategori');
        $hargaRange = [
            'min' => Produk::min('harga'),
            'max' => Produk::max('harga')
        ];

        return view('produk.index', compact('produk', 'kategoriList', 'hargaRange'));
    }

    // Menampilkan form tambah produk
    public function create()
    {
        $topKategori = Produk::select('kategori')
            ->groupBy('kategori')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(5)
            ->pluck('kategori');

        return view('produk.form', [
            'action' => route('produk.store'),
            'method' => 'POST',
            'produk' => new Produk(),
            'topKategori' => $topKategori,
            'title' => 'Tambah Produk Baru'
        ]);
    }

    // Menyimpan produk baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:produk,nama',
            'harga' => 'required|numeric|min:0',
            'deskripsi' => 'required|string|min:10',
            'kategori' => 'required|string|max:100',
            'gambar' => 'nullable|image|max:2048',
            'stok' => 'required|integer|min:0'
        ]);

        if ($request->hasFile('gambar')) {
            $validated['gambar_path'] = $request->file('gambar')->store('produk-images', 'public');
        }

        $produk = Produk::create($validated);

        return redirect()->route('produk.index')
            ->with('success', 'Produk berhasil ditambahkan.')
            ->with('highlight', $produk->id);
    }

    // Menampilkan detail produk
    public function show(Produk $produk)
    {
        return view('produk.show', compact('produk'));
    }

    // Menampilkan form edit
    public function edit(Produk $produk)
    {
        $topKategori = Produk::select('kategori')
            ->groupBy('kategori')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(5)
            ->pluck('kategori');

        return view('produk.form', [
            'action' => route('produk.update', $produk),
            'method' => 'PUT',
            'produk' => $produk,
            'topKategori' => $topKategori,
            'title' => 'Edit Produk: ' . $produk->nama
        ]);
    }

    // Memperbarui data produk
    public function update(Request $request, Produk $produk)
    {
        $validated = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:255',
                Rule::unique('produk')->ignore($produk->id)
            ],
            'harga' => 'required|numeric|min:0',
            'deskripsi' => 'required|string|min:10',
            'kategori' => 'required|string|max:100',
            'gambar' => 'nullable|image|max:2048',
            'stok' => 'required|integer|min:0'
        ]);

        if ($request->hasFile('gambar')) {
            if ($produk->gambar_path) {
                Storage::disk('public')->delete($produk->gambar_path);
            }
            $validated['gambar_path'] = $request->file('gambar')->store('produk-images', 'public');
        }

        $produk->update($validated);

        return redirect()->route('produk.index')
            ->with('success', 'Produk berhasil diperbarui.')
            ->with('highlight', $produk->id);
    }

    // Menghapus produk
    public function destroy(Produk $produk)
    {
        try {
            if ($produk->gambar_path) {
                Storage::disk('public')->delete($produk->gambar_path);
            }

            $produk->delete();

            return redirect()->route('produk.index')
                ->with('success', 'Produk berhasil dihapus.')
                ->with('deleted', $produk->id);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus produk: ' . $e->getMessage());
        }
    }

    // Endpoint API produk
    public function apiIndex()
    {
        return response()->json([
            'data' => Produk::all(),
            'message' => 'Data produk berhasil diambil'
        ]);
    }
}
