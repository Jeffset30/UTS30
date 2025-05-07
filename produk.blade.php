<!-- TODO: tuliskan tampilan view anda disini -->
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Produk</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">
{{-- FORM PENCARIAN DAN FILTER PRODUK --}}
<div class="mb-6 bg-white p-4 rounded-lg shadow flex flex-col sm:flex-row sm:items-center gap-4">
  <form action="{{ route('produk.index') }}" method="GET" class="flex flex-col sm:flex-row sm:items-center gap-4 w-full">

    {{-- Input Pencarian Produk --}}
    <div class="w-full sm:w-1/3">
      <input
        type="text"
        name="search"
        value="{{ request('search') }}"
        placeholder="Cari nama produk..."
        class="w-full p-2 border rounded text-sm sm:text-base"
      >
    </div>

    {{-- Dropdown Filter Kategori --}}
    <div class="w-full sm:w-1/4">
      <select name="kategori" class="w-full p-2 border rounded text-sm sm:text-base">
        <option value="">Semua Kategori</option>
        @php
          $kategoriList = \App\Models\Produk::select('kategori')->distinct()->pluck('kategori');
        @endphp
        @foreach ($kategoriList as $kategori)
          <option value="{{ $kategori }}" {{ request('kategori') == $kategori ? 'selected' : '' }}>
            {{ $kategori }}
          </option>
        @endforeach
      </select>
    </div>

    {{-- Tombol Aksi --}}
    <div class="flex gap-2">
      <button
        type="submit"
        class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition text-sm sm:text-base"
      >
        Cari
      </button>
      <a
        href="{{ route('produk.index') }}"
        class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition text-sm sm:text-base"
      >
        Tampilkan Semua
      </a>
    </div>
    
  </form>
</div>


  <div class="max-w-7xl mx-auto p-6">
    <h1 class="text-3xl sm:text-4xl font-semibold text-gray-800 mb-6">Produk</h1>

    {{-- Pesan sukses --}}
    @if(session('success'))
      <div class="mb-4 text-green-600">{{ session('success') }}</div>
    @endif

    {{-- Jika ada variabel $action, tampilkan form --}}
    @if(isset($action))
      {{-- FORM TAMBAH / EDIT --}}
      @if($errors->any())
        <div class="mb-4 text-red-600">
          <ul>
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form action="{{ $action }}" method="POST" class="space-y-4 bg-white p-6 rounded shadow">
        @csrf
        @if(isset($method) && $method == 'PUT')
          @method('PUT')
        @endif

        <div>
          <label class="block text-gray-700 text-sm sm:text-base">Nama Produk</label>
          <input type="text" name="nama" value="{{ old('nama', $produk->nama ?? '') }}" class="w-full p-2 border rounded text-sm sm:text-base">
        </div>

        <div>
          <label class="block text-gray-700 text-sm sm:text-base">Harga</label>
          <input type="number" name="harga" value="{{ old('harga', $produk->harga ?? '') }}" class="w-full p-2 border rounded text-sm sm:text-base">
        </div>

        <div>
          <label class="block text-gray-700 text-sm sm:text-base">Deskripsi</label>
          <textarea name="deskripsi" class="w-full p-2 border rounded text-sm sm:text-base">{{ old('deskripsi', $produk->deskripsi ?? '') }}</textarea>
        </div>

        <div>
          <label class="block text-gray-700 text-sm sm:text-base">Kategori</label>
          <input type="text" name="kategori" value="{{ old('kategori', $produk->kategori ?? '') }}" class="w-full p-2 border rounded text-sm sm:text-base">
        </div>

        <div class="flex flex-col sm:flex-row justify-between">
          <a href="{{ route('produk.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 mb-4 sm:mb-0">Kembali</a>
          <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            {{ isset($method) && $method == 'PUT' ? 'Update' : 'Simpan' }}
          </button>
        </div>
      </form>

    @else
      {{-- TABEL DAFTAR PRODUK --}}
      <div class="flex justify-between items-center mb-6">
        <a href="{{ route('produk.create') }}" class="bg-blue-600 text-white px-6 py-2 rounded-lg shadow hover:bg-blue-700 transition">Tambah Produk</a>
      </div>

      <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="w-full text-left border-collapse">
          <thead class="bg-gray-200">
            <tr>
              <th class="px-6 py-3 text-sm sm:text-base font-medium text-gray-700">Nama Produk</th>
              <th class="px-6 py-3 text-sm sm:text-base font-medium text-gray-700">Harga</th>
              <th class="px-6 py-3 text-sm sm:text-base font-medium text-gray-700">Deskripsi</th>
              <th class="px-6 py-3 text-sm sm:text-base font-medium text-gray-700">Kategori</th>
              <th class="px-6 py-3 text-sm sm:text-base font-medium text-gray-700">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            @forelse($produk as $item)
            <tr>
              <td class="px-6 py-4 text-sm sm:text-base text-gray-800">{{ $item->nama }}</td>
              <td class="px-6 py-4 text-sm sm:text-base text-gray-800">{{ $item->harga_format }}</td>
              <td class="px-6 py-4 text-sm sm:text-base text-gray-800">{{ $item->deskripsi }}</td>
              <td class="px-6 py-4 text-sm sm:text-base text-gray-800">{{ $item->kategori }}</td>
              <td class="px-6 py-4 space-x-2">
                <a href="{{ route('produk.edit', $item->id) }}" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 transition">Edit</a>
                <form action="{{ route('produk.destroy', $item->id) }}" method="POST" class="inline-block">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition" onclick="return confirm('Yakin ingin menghapus?')">Hapus</button>
                </form>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="5" class="px-6 py-4 text-sm sm:text-base text-gray-800 text-center">Tidak ada produk tersedia.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      @if($produk instanceof \Illuminate\Pagination\LengthAwarePaginator && $produk->hasPages())
      <div class="flex justify-between items-center mt-6 text-sm sm:text-base text-gray-600">
        <span>Menampilkan {{ $produk->firstItem() }} - {{ $produk->lastItem() }} dari {{ $produk->total() }} produk</span>
        <div class="flex gap-1">
          {{ $produk->links() }}
        </div>
      </div>
      @endif

    @endif

  </div>

</body>

</html>