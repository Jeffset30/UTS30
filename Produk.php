<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProdukController extends Controller
{
    // Menampilkan daftar produk dengan fitur tambahan
    public function index(Request $request)
    {
        // Query dasar dengan eager loading jika ada relasi
        $query = Produk::query();

        // Pencarian multi-kolom
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('nama', 'like', "%{$searchTerm}%")
                  ->orWhere('deskripsi', 'like', "%{$searchTerm}%");
            });
        }

        // Filter kategori dengan validasi nilai
        if ($request->filled('kategori') && $request->kategori !== 'all') {
            $query->where('kategori', $request->kategori);
        }

        // Filter harga range
        if ($request->filled('min_harga')) {
            $query->where('harga', '>=', $request->min_harga);
        }
        if ($request->filled('max_harga')) {
            $query->where('harga', '<=', $request->max_harga);
        }

        // Sorting dinamis
        $sortField = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_dir', 'desc');
        $query->orderBy($sortField, $sortDirection);

        // Paginasi dengan query string
        $produk = $query->paginate(15)->withQueryString();

        // Data tambahan untuk view
        $kategoriList = Produk::select('kategori')
            ->distinct()
            ->orderBy('kategori')
            ->pluck('kategori');
            
        $hargaRange = [
            'min' => Produk::min('harga'),
            'max' => Produk::max('harga')
        ];

        return view('produk.index', compact('produk', 'kategoriList', 'hargaRange'));
    }

    // Menampilkan form untuk membuat produk baru
    public function create()
    {
        // Kategori yang sering digunakan untuk saran
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

    // Menyimpan produk baru dengan validasi lebih ketat
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:produk,nama',
            'harga' => 'required|numeric|min:0',
            'deskripsi' => 'required|string|min:10',
            'kategori' => 'required|string|max:100',
            'gambar' => 'nullable|image|max:2048',
            'stok' => 'required|integer|min:0' // Tambahan validasi stok
        ]);

        // Handle upload gambar jika ada
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

    // Menampilkan form untuk mengedit produk
    public function edit(Produk $produk)
    {
        // Kategori yang sering digunakan untuk saran
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

    // Memperbarui data produk dengan pengecekan unik
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
            'stok' => 'required|integer|min:0' // Tambahan validasi stok
        ]);

        // Handle update gambar jika ada
        if ($request->hasFile('gambar')) {
            // Hapus gambar lama jika ada
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
            // Hapus gambar terkait jika ada
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

    // API untuk mendapatkan produk
    public function apiIndex()
    {
        return response()->json([
            'data' => Produk::all(),
            'message' => 'Data produk berhasil diambil'
        ]);
    }
}