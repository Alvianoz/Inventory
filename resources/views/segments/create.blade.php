@extends('layout')

@section('title', 'Tambah Segmen')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
        <h1 class="text-2xl font-bold mb-4">Tambah Segmen Lokasi</h1>

        <form action="{{ route('segments.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama</label>
                <input type="text" name="name" required class="input-field" placeholder="Contoh: Gudang A">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                <textarea name="description" rows="4" class="input-field" placeholder="Opsional"></textarea>
            </div>

            <button class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-3 rounded-xl">Simpan Segmen</button>
        </form>
    </div>
</div>
@endsection