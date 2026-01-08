@extends('layout')

@section('title', 'Kelola Segmen')

@section('content')
<div class="max-w-6xl mx-auto space-y-6 py-8 mt-16">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold">Kelola Segmen Lokasi</h1>
            <p class="text-sm text-gray-500 mt-1">Tambahkan lokasi tempat penyimpanan barang untuk mempermudah pengembalian per segmen.</p>
            <div class="mt-4 flex gap-3 text-sm text-gray-600">
                <div class="inline-flex items-center gap-3 bg-white rounded-xl px-4 py-2 shadow-sm border border-gray-100">
                    <div class="w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center">
                        <i class="fa-solid fa-map-location-dot text-indigo-600"></i>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Total Segmen</div>
                        <div class="font-bold text-gray-800">{{ $segments->count() }}</div>
                    </div>
                </div>

                <div class="inline-flex items-center gap-3 bg-white rounded-xl px-4 py-2 shadow-sm border border-gray-100">
                    <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center">
                        <i class="fa-solid fa-boxes text-green-600"></i>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Produk di Segmen</div>
                        <div class="font-bold text-gray-800">{{ $segments->reduce(function($carry, $s){ return $carry + $s->products->count(); }, 0) }}</div>
                    </div>
                </div>

                <div class="inline-flex items-center gap-3 bg-white rounded-xl px-4 py-2 shadow-sm border border-gray-100">
                    <div class="w-10 h-10 rounded-lg bg-yellow-50 flex items-center justify-center">
                        <i class="fa-solid fa-clock text-yellow-600"></i>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Menunggu Konfirmasi</div>
                        <div class="font-bold text-gray-800">{{ \App\Models\Transaction::where('status','returning')->whereHas('products', function($q){ $q->whereNotNull('segment_id'); })->count() }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <div class="hidden md:block">
                <input id="segment-search" type="search" placeholder="Cari segmen..." class="px-4 py-2 rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 outline-none w-64">
            </div>
            <a href="{{ route('segments.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-sm">Tambah Segmen</a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-50 border-l-4 border-green-500 rounded-xl">{{ session('success') }}</div>
    @endif

    <div id="segments-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($segments as $segment)
            <div class="segment-card bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-col justify-between min-h-[14rem]" data-name="{{ strtolower($segment->name) }}" data-code="{{ strtolower($segment->code) }}">
                <div>
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">{{ $segment->name }}</h3>
                            <p class="text-xs text-gray-500">Kode: <span class="font-mono">{{ $segment->code }}</span></p>
                        </div>
                        <div class="ml-4 flex-shrink-0 text-right">
                            <a href="{{ route('segments.qr.show', $segment->id) }}" title="Lihat QR Segmen">
                                <img src="https://chart.googleapis.com/chart?chs=120x120&cht=qr&chl={{ urlencode(url('/return/segment/'.$segment->id)) }}" alt="QR" class="w-20 h-20 border rounded-lg inline-block" />
                            </a>
                        </div>
                    </div>

                    <p class="text-sm text-gray-600 mt-3">{{ \Illuminate\Support\Str::limit($segment->description ?? '-', 160) }}</p>

                    <div class="mt-4 text-sm text-gray-500">Produk: <span class="font-semibold text-gray-700">{{ $segment->products->count() }}</span></div>
                </div>

                <div class="mt-4 flex items-center justify-between gap-3">
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('segments.return', $segment->id) }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-indigo-600 text-white text-sm">Kembalikan</a>
                        <a href="{{ route('segments.qr.show', $segment->id) }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-purple-50 text-purple-700 text-sm">Lihat QR</a>
                    </div>

                    <div class="flex items-center gap-2">
                        <button onclick="copySegmentLink('{{ urlencode(url('/return/segment/'.$segment->id)) }}')" class="px-3 py-2 rounded-xl bg-gray-50 text-gray-600 text-sm" title="Salin tautan segmen">Salin Link</button>
                        <a href="{{ route('segments.edit', $segment->id) }}" class="px-3 py-2 rounded-xl bg-yellow-50 text-yellow-700 text-sm">Edit</a>
                        <form action="{{ route('segments.destroy', $segment->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus segmen ini?');">
                            @csrf
                            @method('DELETE')
                            <button class="px-3 py-2 rounded-xl bg-red-50 text-red-700 text-sm">Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="sm:col-span-2 lg:col-span-3 text-center py-20">
                <i class="fa-solid fa-map-location-dot text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Belum ada segmen.</h3>
                <a href="{{ route('segments.create') }}" class="text-indigo-600 hover:text-indigo-700 font-medium"><i class="fa-solid fa-plus-circle mr-2"></i>Tambah segmen pertama</a>
            </div>
        @endforelse
    </div>
</div>

<script>
    function copySegmentLink(url) {
        navigator.clipboard.writeText(url).then(() => {
            const el = document.createElement('div');
            el.className = 'fixed top-4 right-4 z-50 p-3 rounded-lg bg-gradient-to-r from-green-500 to-emerald-500 text-white shadow-lg';
            el.innerText = 'Tautan segmen disalin ke clipboard';
            document.body.appendChild(el);
            setTimeout(() => el.remove(), 2500);
        }).catch(() => alert('Gagal menyalin tautan'));
    }

    document.addEventListener('DOMContentLoaded', function() {
        const search = document.getElementById('segment-search');
        if (!search) return;
        const cards = Array.from(document.querySelectorAll('.segment-card'));

        search.addEventListener('input', function() {
            const q = this.value.trim().toLowerCase();
            cards.forEach(c => {
                const name = c.dataset.name || '';
                const code = c.dataset.code || '';
                if (name.includes(q) || code.includes(q)) {
                    c.style.display = '';
                } else {
                    c.style.display = 'none';
                }
            });
        });
    });
</script>
@endsection
