@extends('layout')

@section('title', 'Kelola Barang')

@section('content')
    {{-- Alert Messages --}}
    @if (session('success'))
        <div class="mb-6 p-4 bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 rounded-xl"
            role="alert">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-green-500 flex items-center justify-center">
                    <i class="fa-solid fa-check text-white"></i>
                </div>
                <div>
                    <p class="font-bold text-green-800">Sukses</p>
                    <p class="text-green-700">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 p-4 bg-gradient-to-r from-red-50 to-rose-50 border-l-4 border-red-500 rounded-xl" role="alert">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-red-500 flex items-center justify-center">
                    <i class="fa-solid fa-exclamation-triangle text-white"></i>
                </div>
                <div>
                    <p class="font-bold text-red-800">Error</p>
                    <p class="text-red-700">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Mobile Action Button --}}
    <div class="lg:hidden mb-4">
        <button id="mobile-form-toggle"
            class="w-full bg-gradient-to-r from-indigo-500 to-purple-500 text-white font-semibold py-3 px-4 rounded-xl hover:shadow-lg transition-all flex items-center justify-center space-x-2">
            <i class="fa-solid fa-plus"></i>
            <span>Kelola Barang</span>
        </button>
    </div>

    <div class="flex flex-col lg:flex-row lg:space-x-6">
        {{-- Products Grid --}}
        <div class="w-full lg:w-2/3 order-2 lg:order-1">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                <div>
                    <h1 class="text-2xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">
                        Daftar Barang</h1>
                    <div class="flex gap-4 mt-2 text-sm text-gray-500">
                        <span class="flex items-center space-x-1">
                            <i class="fa-solid fa-box text-indigo-500"></i>
                            <span>{{ $products->count() }} barang</span>
                        </span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                @forelse ($products as $product)
                    <div
                        class="bg-white rounded-2xl shadow-sm overflow-hidden group hover:shadow-xl transition-all duration-300 border border-gray-100">
                        <div
                            class="w-full aspect-square bg-gradient-to-br from-indigo-50 to-purple-50 flex items-center justify-center relative">
                            @if ($product->image_path)
                                <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}"
                                    class="w-full h-full object-cover">
                            @else
                                <i class="fa-solid fa-image text-6xl text-gray-300"></i>
                            @endif

                            @if ($product->stock <= 1)
                                <div
                                    class="absolute top-3 left-3 bg-red-500 text-white text-xs px-3 py-1 rounded-full font-semibold shadow-lg">
                                    @if ($product->stock == 0)
                                        Kosong
                                    @else
                                        Sisa {{ $product->stock }}
                                    @endif
                                </div>
                            @endif

                            <form action="{{ route('products.destroy', $product->id) }}" method="POST"
                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus barang ini?');"
                                class="absolute top-3 right-3">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="bg-red-500 text-white h-10 w-10 rounded-xl flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all hover:bg-red-600 shadow-lg">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>

                        <div class="p-4">
                            <h3 class="text-lg font-bold text-gray-800 line-clamp-2 mb-2">{{ $product->name }}</h3>
                            <div class="text-sm text-gray-500 mb-1">Segmen: {{ $product->segment ? $product->segment->name : '-' }}</div>
                            <div class="flex justify-end items-center mb-4">
                                <p
                                    class="text-sm font-semibold {{ $product->stock > 5 ? 'text-gray-500' : ($product->stock > 0 ? 'text-orange-500' : 'text-red-500') }}">
                                    Stok: {{ $product->stock }}
                                </p>
                            </div>

                            {{-- Action Buttons dengan QR --}}
                            <div class="space-y-2">
                                <div class="flex gap-2">
                                    <button
                                        class="edit-btn flex-1 bg-gradient-to-r from-blue-50 to-cyan-50 text-blue-700 text-sm py-2.5 px-3 rounded-xl hover:shadow-md transition-all font-medium border border-blue-100"
                                        data-id="{{ $product->id }}" data-name="{{ $product->name }}"
                                        data-stock="{{ $product->stock }}"
                                        data-category="{{ $product->category }}"
                                        data-segment-id="{{ $product->segment_id }}">
                                        <i class="fa-solid fa-edit mr-1"></i>Edit
                                    </button>
                                    <button
                                        class="restock-btn flex-1 bg-gradient-to-r from-yellow-50 to-amber-50 text-yellow-700 text-sm py-2.5 px-3 rounded-xl hover:shadow-md transition-all font-medium border border-yellow-100"
                                        data-id="{{ $product->id }}" data-name="{{ $product->name }}"
                                        data-price="{{ $product->price }}" data-stock="{{ $product->stock }}">
                                        <i class="fa-solid fa-plus-circle mr-1"></i>Restock
                                    </button>
                                </div>

                                {{-- QR Code Button --}}
                                <div class="flex gap-2">
                                    <a href="{{ route('products.qr.show', $product->id) }}"
                                        class="flex-1 bg-gradient-to-r from-purple-50 to-pink-50 text-purple-700 text-sm py-2.5 px-3 rounded-xl hover:shadow-md transition-all font-medium border border-purple-100 text-center inline-block"
                                        title="Lihat QR Code">
                                        <i class="fa-solid fa-qrcode mr-1"></i>Lihat QR
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="sm:col-span-2 xl:col-span-3 text-center py-20">
                        <i class="fa-solid fa-box-open text-6xl text-gray-300 mb-4"></i>
                        <h3 class="text-xl font-bold text-gray-800 mb-2">
                            @if (request('search'))
                                Barang dengan nama "{{ request('search') }}" tidak ditemukan.
                            @else
                                Belum ada barang yang tersedia.
                            @endif
                        </h3>
                        <button class="mt-4 text-indigo-600 hover:text-indigo-700 font-medium"
                            onclick="document.querySelector('[data-tab=tambah]').click()">
                            <i class="fa-solid fa-plus-circle mr-2"></i>Tambah barang pertama
                        </button>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Form Sidebar (Desktop) --}}
        <div class="w-full lg:w-1/3 order-1 lg:order-2">
            <div class="hidden lg:block bg-white p-6 rounded-2xl shadow-sm border border-gray-100 sticky top-24">
                <div class="flex border-b mb-6">
                    <button data-tab="tambah"
                        class="tab-button active-tab flex-1 py-3 font-semibold text-center transition-all">
                        Tambah
                    </button>
                    <button data-tab="edit" class="tab-button flex-1 py-3 font-semibold text-center transition-all opacity-50 cursor-not-allowed" disabled>
                        Edit
                    </button>
                    <button data-tab="restock" class="tab-button flex-1 py-3 font-semibold text-center transition-all opacity-50 cursor-not-allowed" disabled>
                        Restock
                    </button>
                </div>

                <div>
                    {{-- Tambah Tab --}}
                    <div id="tambah" class="tab-content">
                        <h2 class="text-xl font-bold mb-4 text-gray-800">Tambah Barang</h2>
                        <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data"
                            class="space-y-4">
                            @csrf
                            <div>
                                <label for="name_tambah" class="block text-sm font-medium text-gray-700 mb-2">Nama
                                    Barang</label>
                                <input type="text" id="name_tambah" name="name" required class="input-field"
                                    placeholder="Masukkan nama barang">
                            </div>
                            <div>
                                <label for="stock_tambah" class="block text-sm font-medium text-gray-700 mb-2">Stok
                                    Awal</label>
                                <input type="number" id="stock_tambah" name="stock" required class="input-field"
                                    placeholder="10" min="0">
                            </div>
                            <div>
                                <label for="category_tambah"
                                    class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                                <input type="text" id="category_tambah" name="category" class="input-field"
                                    placeholder="Elektronik, Peralatan, dll">
                            </div>

                            <div>
                                <label for="segment_tambah" class="block text-sm font-medium text-gray-700 mb-2">Segmen Lokasi</label>
                                <select id="segment_tambah" name="segment_id" class="input-field">
                                    <option value="">-- Pilih Segmen --</option>
                                    @foreach($segments as $segment)
                                        <option value="{{ $segment->id }}">{{ $segment->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="image_path_tambah"
                                    class="block text-sm font-medium text-gray-700 mb-2">Gambar</label>
                                <input type="file" id="image_path_tambah" name="image_path" accept="image/*"
                                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-gradient-to-r file:from-indigo-50 file:to-purple-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-all" />
                            </div>
                            <button type="submit"
                                class="w-full bg-gradient-to-r from-indigo-500 to-purple-500 text-white font-bold py-3 rounded-xl hover:shadow-lg transition-all">
                                <i class="fa-solid fa-save mr-2"></i>Simpan Barang
                            </button>
                        </form>
                    </div>

                    {{-- Edit Tab --}}
                    <div id="edit" class="tab-content hidden">
                        <h2 class="text-xl font-bold mb-4 text-gray-800">Edit Barang</h2>
                        <p id="edit-instruction" class="text-sm text-gray-500 mb-4">
                            Pilih barang dari daftar untuk mengeditnya.
                        </p>
                        <form id="edit-form" action="" method="POST" class="space-y-4">
                            @csrf
                            @method('PUT')
                            <div>
                                <label for="name_edit" class="block text-sm font-medium text-gray-700 mb-2">Nama
                                    Barang</label>
                                <input type="text" id="name_edit" name="name" required class="input-field">
                            </div>
                            <div>
                                <label for="category_edit"
                                    class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                                <input type="text" id="category_edit" name="category" class="input-field">
                            </div>

                            <div>
                                <label for="segment_edit" class="block text-sm font-medium text-gray-700 mb-2">Segmen Lokasi</label>
                                <select id="segment_edit" name="segment_id" class="input-field">
                                    <option value="">-- Pilih Segmen --</option>
                                    @foreach($segments as $segment)
                                        <option value="{{ $segment->id }}">{{ $segment->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit"
                                class="w-full bg-gradient-to-r from-blue-500 to-cyan-500 text-white font-bold py-3 rounded-xl hover:shadow-lg transition-all">
                                <i class="fa-solid fa-save mr-2"></i>Update Barang
                            </button>
                        </form>
                    </div>

                    {{-- Restock Tab --}}
                    <div id="restock" class="tab-content hidden">
                        <h2 class="text-xl font-bold mb-4 text-gray-800">Restock Barang</h2>
                        <p id="restock-instruction" class="text-sm text-gray-500 mb-4">
                            Pilih barang untuk menambah stok.
                        </p>
                        <form id="restock-form" action="" method="POST" class="space-y-4">
                            @csrf
                            @method('PUT')
                            <div>
                                <label for="name_restock" class="block text-sm font-medium text-gray-700 mb-2">Nama
                                    Barang</label>
                                <input type="text" id="name_restock" readonly class="input-field bg-gray-50">
                            </div>
                            <div>
                                <label for="stock_sekarang" class="block text-sm font-medium text-gray-700 mb-2">Stok Saat
                                    Ini</label>
                                <input type="number" id="stock_sekarang" readonly class="input-field bg-gray-50">
                            </div>
                            <div>
                                <label for="stock_added" class="block text-sm font-medium text-gray-700 mb-2">Jumlah
                                    Tambahan</label>
                                <input type="number" id="stock_added" name="stock_added" required class="input-field"
                                    placeholder="Masukkan jumlah" min="1">
                            </div>
                            <button type="submit"
                                class="w-full bg-gradient-to-r from-yellow-500 to-amber-500 text-white font-bold py-3 rounded-xl hover:shadow-lg transition-all">
                                <i class="fa-solid fa-plus mr-2"></i>Tambah Stok
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Mobile Form Modal --}}
    <div id="mobile-form-modal" class="lg:hidden fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="fixed bottom-0 left-0 right-0 bg-white rounded-t-2xl max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-800">Kelola Barang</h2>
                    <button id="close-mobile-form" class="text-gray-500 hover:text-gray-700">
                        <i class="fa-solid fa-times text-2xl"></i>
                    </button>
                </div>

                <div class="flex border-b mb-4">
                    <button data-tab="mobile-tambah"
                        class="mobile-tab-button active-tab flex-1 py-2 font-semibold text-center">Tambah</button>
                    <button data-tab="mobile-edit"
                        class="mobile-tab-button flex-1 py-2 font-semibold text-center opacity-50 cursor-not-allowed" disabled>Edit</button>
                    <button data-tab="mobile-restock"
                        class="mobile-tab-button flex-1 py-2 font-semibold text-center opacity-50 cursor-not-allowed" disabled>Restock</button>
                </div>

                <div>
                    {{-- Mobile Forms (similar structure) --}}
                    <div id="mobile-tambah" class="mobile-tab-content">
                        <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data"
                            class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Barang</label>
                                <input type="text" name="name" required class="input-field"
                                    placeholder="Masukkan nama barang">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Stok Awal</label>
                                <input type="number" name="stock" required class="input-field" placeholder="10"
                                    min="0">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                                <input type="text" name="category" class="input-field" placeholder="Makanan, dll">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Segmen Lokasi</label>
                                <select name="segment_id" class="input-field">
                                    <option value="">-- Pilih Segmen --</option>
                                    @foreach($segments as $segment)
                                        <option value="{{ $segment->id }}">{{ $segment->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Gambar</label>
                                <input type="file" name="image_path" accept="image/*"
                                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-gradient-to-r file:from-indigo-50 file:to-purple-50 file:text-indigo-700" />
                            </div>
                            <button type="submit"
                                class="w-full bg-gradient-to-r from-indigo-500 to-purple-500 text-white font-bold py-3 rounded-xl">
                                <i class="fa-solid fa-save mr-2"></i>Simpan Barang
                            </button>
                        </form>
                    </div>

                    <div id="mobile-edit" class="mobile-tab-content hidden">
                        <p id="mobile-edit-instruction" class="text-sm text-gray-500 mb-4">Pilih barang dari daftar untuk
                            mengeditnya.</p>
                        <form id="mobile-edit-form" action="" method="POST" class="space-y-4">
                            @csrf
                            @method('PUT')
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Barang</label>
                                <input type="text" id="mobile_name_edit" name="name" required class="input-field">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                                <input type="text" id="mobile_category_edit" name="category" class="input-field">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Segmen Lokasi</label>
                                <select id="mobile_segment_edit" name="segment_id" class="input-field">
                                    <option value="">-- Pilih Segmen --</option>
                                    @foreach($segments as $segment)
                                        <option value="{{ $segment->id }}">{{ $segment->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit"
                                class="w-full bg-gradient-to-r from-blue-500 to-cyan-500 text-white font-bold py-3 rounded-xl">
                                <i class="fa-solid fa-save mr-2"></i>Update Barang
                            </button>
                        </form>
                    </div>

                    <div id="mobile-restock" class="mobile-tab-content hidden">
                        <p id="mobile-restock-instruction" class="text-sm text-gray-500 mb-4">Pilih barang untuk menambah
                            stok.</p>
                        <form id="mobile-restock-form" action="" method="POST" class="space-y-4">
                            @csrf
                            @method('PUT')
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Barang</label>
                                <input type="text" id="mobile_name_restock" readonly class="input-field bg-gray-50">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Stok Sekarang</label>
                                    <input type="number" id="mobile_stock_sekarang" readonly
                                        class="input-field bg-gray-50">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Tambah</label>
                                    <input type="number" id="mobile_stock_added" name="stock_added" required
                                        class="input-field" min="1">
                                </div>
                            </div>
                            <button type="submit"
                                class="w-full bg-gradient-to-r from-yellow-500 to-amber-500 text-white font-bold py-3 rounded-xl">
                                <i class="fa-solid fa-plus mr-2"></i>Tambah Stok
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .tab-button.active-tab,
        .mobile-tab-button.active-tab {
            color: #6366f1;
            border-bottom: 3px solid #6366f1;
        }

        .input-field {
            display: block;
            width: 100%;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 0.625rem 1rem;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .input-field:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.tab-button');
            const mobileTabs = document.querySelectorAll('.mobile-tab-button');
            const contents = document.querySelectorAll('.tab-content');
            const mobileContents = document.querySelectorAll('.mobile-tab-content');
            const editButtons = document.querySelectorAll('.edit-btn');
            const restockButtons = document.querySelectorAll('.restock-btn');
            const mobileFormToggle = document.getElementById('mobile-form-toggle');
            const mobileFormModal = document.getElementById('mobile-form-modal');
            const closeMobileForm = document.getElementById('close-mobile-form');
            const editForm = document.getElementById('edit-form');
            const restockForm = document.getElementById('restock-form');
            const mobileEditForm = document.getElementById('mobile-edit-form');
            const mobileRestockForm = document.getElementById('mobile-restock-form');

            mobileFormToggle.addEventListener('click', function() {
                mobileFormModal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            });

            closeMobileForm.addEventListener('click', function() {
                mobileFormModal.classList.add('hidden');
                document.body.style.overflow = 'auto';
            });

            mobileFormModal.addEventListener('click', function(e) {
                if (e.target === mobileFormModal) {
                    mobileFormModal.classList.add('hidden');
                    document.body.style.overflow = 'auto';
                }
            });

            function openTab(tabName) {
                tabs.forEach(t => t.classList.remove('active-tab'));
                contents.forEach(c => c.classList.add('hidden'));
                const targetBtn = document.querySelector(`[data-tab="${tabName}"]`);
                if (targetBtn) targetBtn.classList.add('active-tab');
                const targetContent = document.getElementById(tabName);
                if (targetContent) targetContent.classList.remove('hidden');
            }

            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    if (tab.disabled) return;
                    openTab(tab.dataset.tab);
                });
            });

            function openMobileTab(tabName) {
                mobileTabs.forEach(t => t.classList.remove('active-tab'));
                mobileContents.forEach(c => c.classList.add('hidden'));
                const targetBtn = document.querySelector(`[data-tab="${tabName}"]`);
                if (targetBtn) targetBtn.classList.add('active-tab');
                const targetContent = document.getElementById(tabName);
                if (targetContent) targetContent.classList.remove('hidden');
            }

            mobileTabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    if (tab.disabled) return;
                    openMobileTab(tab.dataset.tab);
                });
            });

            function populateEditForm(id, name, price, category, segmentId) {
                editForm.action = `/products/${id}`;
                editForm.querySelector('#name_edit').value = name;
                editForm.querySelector('#category_edit').value = category || '';
                document.getElementById('segment_edit').value = segmentId || '';
                document.getElementById('edit-instruction').classList.add('hidden');

                mobileEditForm.action = `/products/${id}`;
                document.getElementById('mobile_name_edit').value = name;
                document.getElementById('mobile_category_edit').value = category || '';
                document.getElementById('mobile_segment_edit').value = segmentId || '';
                document.getElementById('mobile-edit-instruction').classList.add('hidden');
            }

            function populateRestockForm(id, name, stock) {
                restockForm.action = `/products/${id}/restock`;
                restockForm.querySelector('#name_restock').value = name;
                restockForm.querySelector('#stock_sekarang').value = stock;
                restockForm.querySelector('#stock_added').value = '';
                document.getElementById('restock-instruction').classList.add('hidden');

                mobileRestockForm.action = `/products/${id}/restock`;
                document.getElementById('mobile_name_restock').value = name;
                document.getElementById('mobile_stock_sekarang').value = stock;
                document.getElementById('mobile_stock_added').value = '';
                document.getElementById('mobile-restock-instruction').classList.add('hidden');
            }

            editButtons.forEach(button => {
                button.addEventListener('click', () => {
                    populateEditForm(
                        button.dataset.id,
                        button.dataset.name,
                        0,
                        button.dataset.category,
                        button.dataset.segmentId
                    );

                    if (window.innerWidth < 1024) {
                        mobileFormModal.classList.remove('hidden');
                        document.body.style.overflow = 'hidden';
                        openMobileTab('mobile-edit');
                    } else {
                        openTab('edit');
                    }
                });
            });

            restockButtons.forEach(button => {
                button.addEventListener('click', () => {
                    populateRestockForm(
                        button.dataset.id,
                        button.dataset.name,
                        button.dataset.stock
                    );

                    if (window.innerWidth < 1024) {
                        mobileFormModal.classList.remove('hidden');
                        document.body.style.overflow = 'hidden';
                        openMobileTab('mobile-restock');
                    } else {
                        openTab('restock');
                    }
                });
            });

            setTimeout(function() {
                const alerts = document.querySelectorAll('.bg-gradient-to-r');
                alerts.forEach(alert => {
                    if (alert.classList.contains('from-green-50') || alert.classList.contains(
                            'from-red-50')) {
                        alert.style.transition = 'opacity 0.5s';
                        alert.style.opacity = '0';
                        setTimeout(() => alert.remove(), 500);
                    }
                });
            }, 5000);

        });
    </script>
@endsection
