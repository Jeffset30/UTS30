<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manajemen Produk</title>
  <script src="https://cdn.tailwindcss.com"></script>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 p-6">
  <div class="max-w-6xl mx-auto bg-white p-6 rounded shadow">
    <!-- Header -->
    <div class="flex justify-between items-center mb-4">
      <h1 class="text-2xl font-bold">Manajemen Produk</h1>
      <a href="{{ route('produk.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">+ Tambah Produk</a>
    </div>

    <!-- Filter dan Pencarian -->
    <form method="GET" action="{{ route('produk.index') }}" class="flex flex-col md:flex-row gap-4 mb-6">
      <div class="w-full md:w-1/2">
        <input type="text" name="search" placeholder="Cari produk..." 
               value="{{ request('search') }}" 
               class="w-full border px-3 py-2 rounded">
      </div>
      <div class="w-full md:w-1/4">
        <select name="kategori" class="w-full border px-3 py-2 rounded">
          <option value="all">Semua Kategori</option>
          @foreach($kategoriList as $kategori)
            <option value="{{ $kategori }}" {{ request('kategori') == $kategori ? 'selected' : '' }}>
              {{ $kategori }}
            </option>
          @endforeach
        </select>
      </div>
      <div class="w-full md:w-1/4 flex gap-2">
        <input type="number" name="min_harga" placeholder="Harga Min" 
               value="{{ request('min_harga') }}" 
               class="w-1/2 border px-3 py-2 rounded" min="0">
        <input type="number" name="max_harga" placeholder="Harga Max" 
               value="{{ request('max_harga') }}" 
               class="w-1/2 border px-3 py-2 rounded" min="0">
      </div>
      <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Filter</button>
    </form>

    <!-- Tabel Daftar Produk -->
    <div>
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold">Daftar Produk</h2>
        <div class="flex gap-2">
          <span class="text-sm text-gray-600">Urutkan:</span>
          <select onchange="window.location.href = updateQueryStringParameter('sort_by', this.value)" 
                  class="border px-2 py-1 rounded text-sm">
            <option value="nama" {{ request('sort_by') == 'nama' ? 'selected' : '' }}>Nama</option>
            <option value="harga" {{ request('sort_by') == 'harga' ? 'selected' : '' }}>Harga</option>
            <option value="created_at" {{ !request('sort_by') || request('sort_by') == 'created_at' ? 'selected' : '' }}>Terbaru</option>
          </select>
          <select onchange="window.location.href = updateQueryStringParameter('sort_dir', this.value)" 
                  class="border px-2 py-1 rounded text-sm">
            <option value="asc" {{ request('sort_dir') == 'asc' ? 'selected' : '' }}>A-Z</option>
            <option value="desc" {{ !request('sort_dir') || request('sort_dir') == 'desc' ? 'selected' : '' }}>Z-A</option>
          </select>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left border">
          <thead class="bg-gray-200">
            <tr>
              <th class="p-3">Nama</th>
              <th class="p-3">Harga</th>
              <th class="p-3">Kategori</th>
              <th class="p-3">Stok</th>
              <th class="p-3">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($produk as $item)
              <tr class="border-t hover:bg-gray-50 {{ session('highlight') == $item->id ? 'bg-blue-50' : '' }} 
                  {{ session('deleted') == $item->id ? 'bg-red-50' : '' }}">
                <td class="p-3 font-medium">{{ $item->nama }}</td>
                <td class="p-3">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                <td class="p-3">{{ $item->kategori }}</td>
                <td class="p-3">{{ $item->stok }}</td>
                <td class="p-3 space-x-2">
                  <a href="{{ route('produk.edit', $item->id) }}" 
                     class="bg-yellow-400 px-3 py-1 rounded text-white hover:bg-yellow-500">Edit</a>
                  <form action="{{ route('produk.destroy', $item->id) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?')"
                            class="bg-red-600 px-3 py-1 rounded text-white hover:bg-red-700">Hapus</button>
                  </form>
                  <a href="{{ route('produk.show', $item->id) }}" 
                     class="bg-green-600 px-3 py-1 rounded text-white hover:bg-green-700">Detail</a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="p-3 text-center text-gray-500">Tidak ada produk ditemukan</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination -->
    <div class="flex justify-between items-center mt-4 text-sm text-gray-600">
      <span>Menampilkan {{ $produk->firstItem() }} - {{ $produk->lastItem() }} dari {{ $produk->total() }} produk</span>
      <div class="flex gap-1">
        @if ($produk->onFirstPage())
          <span class="px-2 py-1 border rounded text-gray-400">&laquo;</span>
        @else
          <a href="{{ $produk->previousPageUrl() }}" class="px-2 py-1 border rounded hover:bg-gray-100">&laquo;</a>
        @endif

        @foreach ($produk->getUrlRange(1, $produk->lastPage()) as $page => $url)
          @if ($page == $produk->currentPage())
            <span class="px-3 py-1 border rounded bg-blue-600 text-white">{{ $page }}</span>
          @else
            <a href="{{ $url }}" class="px-3 py-1 border rounded hover:bg-gray-100">{{ $page }}</a>
          @endif
        @endforeach

        @if ($produk->hasMorePages())
          <a href="{{ $produk->nextPageUrl() }}" class="px-2 py-1 border rounded hover:bg-gray-100">&raquo;</a>
        @else
          <span class="px-2 py-1 border rounded text-gray-400">&raquo;</span>
        @endif
      </div>
    </div>
  </div>

  <!-- Flash Message -->
  @if(session('success'))
    <div class="fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg">
      {{ session('success') }}
    </div>
    <script>
      setTimeout(() => document.querySelector('.fixed').remove(), 3000);
    </script>
  @endif

  @if(session('error'))
    <div class="fixed bottom-4 right-4 bg-red-500 text-white px-4 py-2 rounded shadow-lg">
      {{ session('error') }}
    </div>
    <script>
      setTimeout(() => document.querySelector('.fixed').remove(), 3000);
    </script>
  @endif

  <script>
    function updateQueryStringParameter(key, value) {
      const url = new URL(window.location.href);
      url.searchParams.set(key, value);
      return url.toString();
    }
  </script>
</body>
</html>