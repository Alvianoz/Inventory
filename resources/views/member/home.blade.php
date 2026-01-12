@extends('layout')

@section('title', 'Home - Dashboard Anggota')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
        <div class="grid grid-cols-1 gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Halo, {{ Auth::user()->name }} 👋</h2>
                <p class="text-sm text-gray-500 mt-1">Selamat datang di dashboard anggota. Di sini kamu bisa melihat status pinjaman dan melakukan aksi cepat.</p>
            </div>

            <div class="flex flex-col lg:flex-row justify-between items-center gap-4">
                    <div class="flex gap-3 flex-nowrap overflow-x-auto no-scrollbar">
                    <a href="{{ route('loan.borrow') }}" class="flex items-center gap-3 px-6 h-12 min-w-[180px] rounded-full text-white font-semibold tracking-wide shadow-md bg-gradient-to-r from-indigo-600 to-purple-600">
                        <span class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center">
                            <i class="fa-solid fa-box text-white text-lg"></i>
                        </span>
                        <span class="text-base">Pinjam Barang</span>
                    </a>

                    <a href="{{ route('loan.return') }}" class="flex items-center gap-3 px-5 h-12 min-w-[150px] rounded-xl text-white font-semibold tracking-wide shadow-sm bg-gradient-to-r from-emerald-500 to-teal-400">
                        <span class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center text-white">
                            <i class="fa-solid fa-rotate-left text-lg"></i>
                        </span>
                        <span class="font-medium text-sm">Kembalikan</span>
                    </a>

                    <a href="{{ route('history.index') }}" class="flex items-center gap-3 px-5 h-12 min-w-[150px] rounded-xl text-white font-semibold tracking-wide shadow-sm bg-gradient-to-r from-amber-400 to-orange-400">
                        <span class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center text-white">
                            <i class="fa-solid fa-clock-rotate-left text-lg"></i>
                        </span>
                        <span class="font-medium text-sm">Riwayat</span>
                    </a>

                    <a href="{{ route('profile.show') }}" class="flex items-center gap-3 px-5 h-12 min-w-[150px] rounded-xl text-white font-semibold tracking-wide shadow-sm bg-gradient-to-r from-sky-500 to-cyan-500">
                        <span class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center text-white">
                            <i class="fa-solid fa-user text-lg"></i>
                        </span>
                        <span class="font-medium text-sm">Profil</span>
                    </a>
                </div>

                <div class="flex gap-4 mt-2 lg:mt-0">
                    <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 w-44 flex items-center gap-3">
                        <div class="w-12 h-12 rounded-lg flex items-center justify-center bg-indigo-50 text-indigo-700">
                            <i class="fa-solid fa-user-clock text-lg"></i>
                        </div>
                        <div class="text-left">
                            <div class="text-xs text-gray-500">Sedang Dipinjam</div>
                            <div class="text-2xl font-bold text-indigo-800">{{ $activeLoans }}</div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 w-44 flex items-center gap-3">
                        <div class="w-12 h-12 rounded-lg flex items-center justify-center bg-yellow-50 text-yellow-700">
                            <i class="fa-solid fa-hourglass-half text-lg"></i>
                        </div>
                        <div class="text-left">
                            <div class="text-xs text-gray-500">Menunggu Persetujuan</div>
                            <div class="text-2xl font-bold text-gray-800">{{ $pendingLoans }}</div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 w-44 flex items-center gap-3">
                        <div class="w-12 h-12 rounded-lg flex items-center justify-center bg-red-50 text-red-600">
                            <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                        </div>
                        <div class="text-left">
                            <div class="text-xs text-gray-500">Jatuh Tempo (3 hari)</div>
                            <div class="text-2xl font-bold text-red-600">{{ $dueSoonLoans }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <h3 class="font-semibold text-gray-800 mb-4">Pinjaman Terbaru</h3>

            @if($currentLoans->isEmpty())
                <div class="text-sm text-gray-500">Belum ada pinjaman. Coba cari barang dan ajukan peminjaman.</div>
            @else
                <ul class="space-y-4">
                    @foreach($currentLoans as $trx)
                        <li class="border border-gray-100 rounded-xl p-4 flex items-start justify-between gap-4">
                            <div>
                                <div class="text-sm text-gray-500">#{{ $trx->invoice_number }} &middot; {{ $trx->created_at->format('d M Y') }}</div>
                                <div class="mt-2 font-medium text-gray-800">{{ $trx->products->pluck('name')->join(', ') }}</div>
                                <div class="text-xs text-gray-500 mt-1">Durasi: {{ $trx->duration ?? '-' }} hari &middot; Kembali: {{ $trx->return_date ? $trx->return_date->format('d M Y') : '-' }}</div>
                            </div>
                            <div class="text-right">
                                @if($trx->status == 'pending')
                                    <span class="px-3 py-1 rounded-full text-xs bg-yellow-100 text-yellow-800">Menunggu</span>
                                @elseif($trx->status == 'borrowed')
                                    <span class="px-3 py-1 rounded-full text-xs bg-indigo-100 text-indigo-800">Sedang Dipinjam</span>
                                @elseif($trx->status == 'returning')
                                    <span class="px-3 py-1 rounded-full text-xs bg-orange-100 text-orange-800">Menunggu Pengembalian</span>
                                @elseif($trx->status == 'returned')
                                    <span class="px-3 py-1 rounded-full text-xs bg-green-100 text-green-800">Selesai</span>
                                @else
                                    <span class="px-3 py-1 rounded-full text-xs bg-gray-100 text-gray-700">{{ ucfirst($trx->status) }}</span>
                                @endif
                                <div class="mt-2">
                                    <a href="{{ route('history.show', $trx->id) }}" class="inline-flex items-center gap-2 text-sm text-indigo-600 hover:underline">Lihat Detail</a>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif

        </div>

        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <h3 class="font-semibold text-gray-800 mb-4">Rekomendasi & Top Barang</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($topProductLabels as $index => $label)
                    <div class="p-4 border border-gray-100 rounded-xl flex items-center justify-between gap-4">
                        <div>
                            <div class="text-sm text-gray-500">{{ $label }}</div>
                            <div class="mt-1 text-xs text-gray-400">Dipinjam {{ $topProductData[$index] ?? 0 }} kali</div>
                        </div>
                        <div>
                            <a href="{{ route('loan.borrow') }}" class="inline-flex items-center gap-2 px-3 py-1 rounded-xl bg-indigo-600 text-white text-sm">Pinjam</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection