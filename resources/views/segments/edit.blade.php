@extends('layout')

@section('title', 'Edit Segmen')

@section('content')
@php
use Illuminate\Support\Facades\Storage;
@endphp
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
        <h1 class="text-2xl font-bold mb-4">Edit Segmen Lokasi</h1>

        <form action="{{ route('segments.update', $segment->id) }}" method="POST" class="space-y-4" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama</label>
                <input type="text" name="name" required class="input-field" value="{{ $segment->name }}">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                <textarea name="description" rows="4" class="input-field">{{ $segment->description }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Gambar</label>
                    <div class="mb-3">
                        <img src="{{ $segment->image_url }}" alt="{{ $segment->name }}" 
                             class="w-32 h-32 object-cover rounded-lg border border-gray-200">
                        <p class="mt-1 text-xs text-gray-500">Gambar saat ini</p>
                    </div>
                <input type="file" name="image_path" accept="image/*"
                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-gradient-to-r file:from-indigo-50 file:to-purple-50 file:text-indigo-700" />
                <p class="mt-1 text-xs text-gray-500">Format: JPG, PNG, GIF. Maksimal 2MB. Kosongkan jika tidak ingin mengubah gambar.</p>
            </div>

            <button class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-3 rounded-xl">Update Segmen</button>
        </form>
    </div>
</div>
@endsection