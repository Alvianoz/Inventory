@extends('layout')

@section('title', 'Kelola Segmen')

@section('content')
    @php
        use Illuminate\Support\Facades\Storage;
    @endphp
    <div class="mb-6">
        <h1
            class="text-2xl md:text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
            <i class="fa-solid fa-clipboard-list mr-3"></i>Kelola Segmen
        </h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Total Segmen</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $segments->count() }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-orange-100 flex items-center justify-center">
                    <i class="fa-solid fa-map-location-dot text-indigo-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Produk di Segmen</p>
                    <p class="text-2xl font-bold text-orange-600">
                        {{ $segments->reduce(function ($carry, $s) {return $carry + $s->products->count();}, 0) }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-orange-100 flex items-center justify-center">
                    <i class="fa-solid fa-boxes text-indigo-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600">Menunggu Konfirmasi</p>
                    <p class="text-2xl font-bold text-orange-600">
                        {{ \App\Models\Transaction::where('status', 'returning')->whereHas('products', function ($q) {$q->whereNotNull('segment_id');})->count() }}
                    </p>
                </div>
                <div class="w-12 h-12 rounded-full bg-orange-100 flex items-center justify-center">
                    <i class="fa-solid fa-clock text-indigo-600"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 mb-6">

        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('segments.create') }}" id="openCreateSegment"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-sm">Tambah
                Segmen</a>
        </div>

        @if (session('success'))
            <div class="p-4 bg-green-50 border-l-4 border-green-500 rounded-xl">{{ session('success') }}</div>
        @endif

        <div id="segments-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($segments as $segment)
                <div class="segment-card bg-white rounded-2xl shadow-sm border border-gray-100 p-4 flex flex-col justify-between min-h-[14rem]"
                    data-name="{{ strtolower($segment->name) }}" data-code="{{ strtolower($segment->code) }}">
                    <div>
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="text-lg font-bold text-gray-800">{{ $segment->name }}</h3>
                                <p class="text-xs text-gray-500">Kode: <span class="font-mono">{{ $segment->code }}</span>
                                </p>
                            </div>
                            <div class="ml-4 flex-shrink-0 text-right">
                                @if ($segment->image_path)
                                    <img src="{{ Storage::url($segment->image_path) }}" alt="{{ $segment->name }}"
                                        class="w-20 h-20 object-cover border rounded-lg inline-block" />
                                @else
                                    <a href="{{ route('segments.qr.show', $segment->id) }}" title="Lihat QR Segmen">
                                        <img src="https://chart.googleapis.com/chart?chs=120x120&cht=qr&chl={{ urlencode(url('/return/segment/' . $segment->id)) }}"
                                            alt="QR" class="w-20 h-20 border rounded-lg inline-block" />
                                    </a>
                                @endif
                            </div>
                        </div>

                        <p class="text-sm text-gray-600 mt-3">
                            {{ \Illuminate\Support\Str::limit($segment->description ?? '-', 160) }}</p>

                        <div class="mt-4 text-sm text-gray-500">Produk: <span
                                class="font-semibold text-gray-700">{{ $segment->products->count() }}</span></div>
                    </div>

                    <div class="mt-4 flex items-center justify-between gap-3">
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('segments.qr.show', $segment->id) }}"
                                class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-purple-50 text-purple-700 text-sm">Lihat
                                QR</a>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button" class="edit-segment-btn px-3 py-2 rounded-xl bg-yellow-50 text-yellow-700 text-sm"
                                data-id="{{ $segment->id }}" data-name="{{ e($segment->name) }}" data-description="{{ e($segment->description ?? '') }}" data-image="{{ $segment->image_path ? Storage::url($segment->image_path) : '' }}">Edit</button>
                            <form action="{{ route('segments.destroy', $segment->id) }}" method="POST"
                                class="inline-block" onsubmit="return confirm('Hapus segmen ini?');">
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
                    <a href="{{ route('segments.create') }}" class="text-indigo-600 hover:text-indigo-700 font-medium"><i
                            class="fa-solid fa-plus-circle mr-2"></i>Tambah segmen pertama</a>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Create Modal -->
    <div id="createSegmentModal" class="fixed inset-0 bg-black/60 hidden z-50">
        <div class="relative top-10 mx-auto p-6 border max-w-2xl shadow-2xl rounded-2xl bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold">Tambah Segmen</h3>
                <button id="closeCreateModal" class="text-gray-400 hover:text-gray-600 p-2 rounded-lg hover:bg-gray-100">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>

            <form action="{{ route('segments.store') }}" method="POST" class="space-y-4" enctype="multipart/form-data">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama</label>
                    <input type="text" name="name" required class="block w-full border border-gray-200 rounded-xl shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Contoh: Gudang A">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                    <textarea name="description" rows="4" class="block w-full border border-gray-200 rounded-xl shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Opsional"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Gambar</label>
                    <input type="file" name="image_path" accept="image/*" data-preview-target="create-image-preview"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-gradient-to-r file:from-indigo-50 file:to-purple-50 file:text-indigo-700" />
                    <p class="mt-1 text-xs text-gray-500">Format: JPG, PNG, GIF. Maksimal 2MB</p>
                    <img id="create-image-preview" class="mt-3 w-32 h-32 object-cover rounded-lg border border-gray-200 hidden" alt="Preview"/>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="w-full md:w-auto bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-2 px-4 rounded-xl">Simpan Segmen</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editSegmentModal" class="fixed inset-0 bg-black/60 hidden z-50">
        <div class="relative top-10 mx-auto p-6 border max-w-2xl shadow-2xl rounded-2xl bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold">Edit Segmen</h3>
                <button id="closeEditModal" class="text-gray-400 hover:text-gray-600 p-2 rounded-lg hover:bg-gray-100">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>

            <form id="editSegmentForm" action="#" method="POST" class="space-y-4" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama</label>
                    <input id="edit-name" type="text" name="name" required class="block w-full border border-gray-200 rounded-xl shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Nama segmen">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                    <textarea id="edit-description" name="description" rows="4" class="block w-full border border-gray-200 rounded-xl shadow-sm py-3 px-4 focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Gambar</label>
                    <img id="edit-image-preview" class="mb-3 w-32 h-32 object-cover rounded-lg border border-gray-200 hidden" alt="Preview"/>
                    <input type="file" name="image_path" accept="image/*" data-preview-target="edit-image-preview"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-gradient-to-r file:from-indigo-50 file:to-purple-50 file:text-indigo-700" />
                    <p class="mt-1 text-xs text-gray-500">Format: JPG, PNG, GIF. Maksimal 2MB. Kosongkan jika tidak ingin mengubah gambar.</p>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="w-full md:w-auto bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-2 px-4 rounded-xl">Update Segmen</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function copySegmentLink(url) {
            navigator.clipboard.writeText(url).then(() => {
                const el = document.createElement('div');
                el.className =
                    'fixed top-4 right-4 z-50 p-3 rounded-lg bg-gradient-to-r from-green-500 to-emerald-500 text-white shadow-lg';
                el.innerText = 'Tautan segmen disalin ke clipboard';
                document.body.appendChild(el);
                setTimeout(() => el.remove(), 2500);
            }).catch(() => alert('Gagal menyalin tautan'));
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Search/filter
            const search = document.querySelector('input[name="search"]');
            const cards = Array.from(document.querySelectorAll('.segment-card'));

            function applyFilter() {
                if (!search) return;
                const q = search.value.trim().toLowerCase();
                cards.forEach(c => {
                    const name = c.dataset.name || '';
                    const code = c.dataset.code || '';
                    if (name.includes(q) || code.includes(q)) {
                        c.style.display = '';
                    } else {
                        c.style.display = 'none';
                    }
                });
            }

            if (search) {
                search.addEventListener('input', applyFilter);
                applyFilter();
            }

            // Create modal
            const openCreate = document.getElementById('openCreateSegment');
            const createModal = document.getElementById('createSegmentModal');
            const closeCreate = document.getElementById('closeCreateModal');
            if (openCreate && createModal) {
                openCreate.addEventListener('click', function(e) {
                    e.preventDefault();
                    createModal.classList.remove('hidden');
                });

                closeCreate.addEventListener('click', function() {
                    createModal.classList.add('hidden');
                });

                createModal.addEventListener('click', function(e) {
                    if (e.target === createModal) createModal.classList.add('hidden');
                });
            }

            // Edit modal
            const editModal = document.getElementById('editSegmentModal');
            const closeEdit = document.getElementById('closeEditModal');
            const editForm = document.getElementById('editSegmentForm');

            document.querySelectorAll('.edit-segment-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const name = this.dataset.name || '';
                    const description = this.dataset.description || '';
                    const image = this.dataset.image || '';

                    if (editForm) {
                        editForm.action = '{{ url('/segments') }}' + '/' + id;
                        document.getElementById('edit-name').value = name;
                        document.getElementById('edit-description').value = description;

                        const preview = document.getElementById('edit-image-preview');
                        if (image) {
                            preview.src = image;
                            preview.classList.remove('hidden');
                        } else {
                            preview.classList.add('hidden');
                            preview.removeAttribute('src');
                        }

                        editModal.classList.remove('hidden');
                    }
                });
            });

            if (closeEdit) {
                closeEdit.addEventListener('click', function() { editModal.classList.add('hidden'); });
            }

            if (editModal) {
                editModal.addEventListener('click', function(e) { if (e.target === editModal) editModal.classList.add('hidden'); });
            }

            // Image preview for file inputs inside modals
            document.querySelectorAll('input[type="file"][name="image_path"]').forEach(input => {
                input.addEventListener('change', function() {
                    const file = this.files[0];
                    const previewId = this.dataset.previewTarget;
                    if (file && previewId) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const img = document.getElementById(previewId);
                            img.src = e.target.result;
                            img.classList.remove('hidden');
                        };
                        reader.readAsDataURL(file);
                    }
                });
            });
        });
    </script>
@endsection
