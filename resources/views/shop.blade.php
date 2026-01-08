@extends('layout')

@section('title', 'Shop')

@section('content')
    {{-- Success/Error Messages --}}
    @if (session('success'))
        <div class="mb-6 p-4 bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 rounded-xl"
            role="alert">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-green-500 flex items-center justify-center">
                    <i class="fa-solid fa-check text-white"></i>
                </div>
                <div>
                    <p class="font-bold text-green-800">Sukses!</p>
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
                    <p class="font-bold text-red-800">Error!</p>
                    <p class="text-red-700">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif


    {{-- Category Filter --}}
    @if (isset($categories))
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 mb-6">
            <div class="flex flex-wrap gap-2">
                <a href="{{ request()->fullUrlWithQuery(['category' => null, 'page' => null]) }}"
                    class="px-4 py-2 rounded-xl text-sm font-medium transition-all {{ !request('category') ? 'bg-gradient-to-r from-indigo-500 to-purple-500 text-white shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    <i class="fa-solid fa-th-large mr-2"></i>Semua
                </a>
                @foreach ($categories as $category)
                    <a href="{{ request()->fullUrlWithQuery(['category' => $category['slug'], 'page' => null]) }}"
                        class="px-4 py-2 rounded-xl text-sm font-medium transition-all {{ request('category') === $category['slug'] ? 'bg-gradient-to-r from-indigo-500 to-purple-500 text-white shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        <i class="{{ $category['icon'] ?? 'fa-solid fa-tag' }} mr-2"></i>{{ $category['name'] }}
                    </a>
                @endforeach
            </div>
        </div>
    @endif



    {{-- Mobile Cart Toggle --}}
    <div class="lg:hidden mb-4">
        <button id="mobile-cart-toggle"
            class="w-full bg-gradient-to-r from-indigo-500 to-purple-500 text-white font-semibold py-3 px-4 rounded-xl hover:shadow-lg transition-all flex items-center justify-between">
            <span>Lihat Keranjang</span>
            <div class="flex items-center space-x-2">
                <span id="mobile-cart-count"
                    class="bg-white text-indigo-600 px-3 py-1 rounded-full text-sm font-bold">0</span>
                <i class="fa-solid fa-shopping-cart"></i>
            </div>
        </button>
    </div>

    <div class="flex flex-col lg:flex-row lg:space-x-6">
        {{-- Products Grid --}}
        <div class="w-full lg:w-2/3 order-2 lg:order-1">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                <div>
                    <h1 class="text-2xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">
                        @if (request('category'))
                            {{ request('category') }}
                        @elseif(request('search'))
                            Pencarian: "{{ request('search') }}"
                        @else
                            Semua Produk
                        @endif
                    </h1>
                    @if (request('search') || request('category'))
                        <p class="text-sm text-gray-500 mt-1">{{ $products->total() }} produk ditemukan</p>
                    @endif
                </div>

                <select id="sort" onchange="applySorting(this.value)"
                    class="text-sm border border-gray-200 rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Terbaru</option>
                    <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Terlama</option>
                    <option value="price_low" {{ request('sort') === 'price_low' ? 'selected' : '' }}>Harga Terendah
                    </option>
                    <option value="price_high" {{ request('sort') === 'price_high' ? 'selected' : '' }}>Harga Tertinggi
                    </option>
                    <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Nama A-Z</option>
                    <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}>Nama Z-A</option>
                    <option value="stock" {{ request('sort') === 'stock' ? 'selected' : '' }}>Stok Tersedia</option>
                </select>
            </div>

            <div id="product-list" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                @forelse ($products as $product)
                    <div class="product-card bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100 group"
                        data-id="{{ $product->id }}" data-name="{{ $product->name }}" data-price="{{ $product->price }}"
                        data-stock="{{ $product->stock }}">
                        <div
                            class="w-full aspect-square bg-gradient-to-br from-indigo-50 to-purple-50 flex items-center justify-center relative overflow-hidden">
                            @if ($product->image_path)
                                <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}"
                                    class="w-full h-full object-cover">
                            @else
                                <i class="fa-solid fa-image text-6xl text-gray-300"></i>
                            @endif

                            <div
                                class="absolute inset-0 bg-gradient-to-br from-indigo-400/20 to-purple-400/20 opacity-0 group-hover:opacity-100 transition-opacity">
                            </div>

                            @if ($product->stock <= 5)
                                <div
                                    class="absolute top-3 left-3 bg-red-500 text-white text-xs px-3 py-1 rounded-full font-semibold shadow-lg">
                                    @if ($product->stock == 0)
                                        Habis
                                    @else
                                        Sisa {{ $product->stock }}
                                    @endif
                                </div>
                            @endif

                            @if ($product->category)
                                <div
                                    class="absolute top-3 right-3 bg-indigo-500 text-white text-xs px-3 py-1 rounded-full font-semibold shadow-lg">
                                    {{ $product->category }}
                                </div>
                            @endif
                        </div>
                        <div class="p-4">
                            <h3 class="text-lg font-bold text-gray-800 line-clamp-2 mb-2">{{ $product->name }}</h3>
                            <div class="flex justify-between items-center mb-4">
                                <span
                                    class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                                    Rp {{ number_format($product->price, 0, ',', '.') }}
                                </span>
                                <span class="text-sm text-gray-500">Stok: {{ $product->stock }}</span>
                            </div>
                            <button
                                class="add-to-cart-btn w-full py-3 rounded-xl font-semibold transition-all {{ $product->stock <= 0 ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : 'bg-gradient-to-r from-indigo-500 to-purple-500 text-white hover:shadow-lg hover:scale-105' }}"
                                {{ $product->stock <= 0 ? 'disabled' : '' }}>
                                @if ($product->stock <= 0)
                                    <i class="fa-solid fa-ban mr-2"></i>Stok Habis
                                @else
                                    <i class="fa-solid fa-cart-plus mr-2"></i>Tambah ke Keranjang
                                @endif
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="sm:col-span-2 lg:col-span-2 xl:col-span-3 text-center py-20">
                        <i class="fa-solid fa-search text-6xl text-gray-300 mb-4"></i>
                        <h3 class="text-xl font-bold text-gray-800 mb-2">Tidak ada produk ditemukan</h3>
                        <p class="text-gray-600 mb-6">
                            @if (request('search') || request('category'))
                                Coba ubah filter atau kata kunci pencarian Anda.
                            @else
                                Belum ada produk yang tersedia.
                            @endif
                        </p>
                        @if (request('search') || request('category'))
                            <a href="{{ route('shop.index') }}"
                                class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-500 text-white rounded-xl hover:shadow-lg transition-all">
                                <i class="fa-solid fa-arrow-left mr-2"></i>
                                Lihat Semua Produk
                            </a>
                        @endif
                    </div>
                @endforelse
            </div>

            @if ($products->hasPages())
                <div class="mt-8">
                    {{ $products->links() }}
                </div>
            @endif
        </div>

        {{-- Cart Sidebar (Desktop) --}}
        <div class="w-full lg:w-1/3 order-1 lg:order-2">
            <div class="hidden lg:block bg-white p-6 rounded-2xl shadow-sm border border-gray-100 sticky top-24">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-800">Keranjang</h2>
                    <div id="desktop-cart-count"
                        class="bg-gradient-to-r from-indigo-500 to-purple-500 text-white px-3 py-1 rounded-full text-sm font-bold shadow-md">
                        0</div>
                </div>

                {{-- QR Scanner Button --}}
                <div class="mb-6">
                    <button id="qr-scanner-btn"
                        class="w-full sm:w-auto bg-gradient-to-r from-blue-500 to-cyan-500 text-white font-semibold py-3 px-6 rounded-xl hover:shadow-lg transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid fa-qrcode text-xl"></i>
                        <span>Scan QR Produk</span>
                    </button>
                </div>

                <form action="{{ route('shop.checkout') }}" method="POST" id="checkout-form">
                    @csrf
                    <input type="hidden" name="cart" id="cart-input">

                    <div id="cart-items" class="space-y-3 mb-6 pr-2 max-h-64 overflow-y-auto"></div>

                    <div class="border-t pt-4 space-y-4">
                        <div>
                            <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-2">Metode
                                Pembayaran</label>
                            <select name="payment_method" id="payment_method"
                                class="block w-full border border-gray-200 rounded-xl shadow-sm py-2.5 px-4 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="cash">💵 Cash</option>
                                <option value="qris">📱 QRIS</option>
                                <option value="debit">💳 Debit Card</option>
                            </select>
                        </div>
                    </div>

                    <div class="border-t my-6"></div>

                    <div class="flex justify-between items-center mb-6">
                        <span class="text-lg font-bold text-gray-800">Total:</span>
                        <span id="total-price"
                            class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Rp
                            0</span>
                    </div>

                    <button type="submit"
                        class="w-full bg-gradient-to-r from-indigo-500 to-purple-500 text-white font-bold py-3 rounded-xl hover:shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                        id="checkout-btn" disabled>
                        <i class="fa-solid fa-check mr-2"></i>Checkout
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Mobile Cart Modal --}}
    <div id="mobile-cart-modal" class="lg:hidden fixed inset-0 bg-black/50 z-50 hidden">
        <div class="fixed bottom-0 left-0 right-0 bg-white rounded-t-2xl p-6 max-h-[80vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-800">Keranjang Belanja</h2>
                <button id="close-mobile-cart" class="text-gray-500 hover:text-gray-700">
                    <i class="fa-solid fa-times text-2xl"></i>
                </button>
            </div>

            {{-- QR Scanner Button --}}
            <div class="mb-6">
                <button id="qr-scanner-btn"
                    class="w-full sm:w-auto bg-gradient-to-r from-blue-500 to-cyan-500 text-white font-semibold py-3 px-6 rounded-xl hover:shadow-lg transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-qrcode text-xl"></i>
                    <span>Scan QR Produk</span>
                </button>
            </div>

            <form action="{{ route('shop.checkout') }}" method="POST" id="mobile-checkout-form">
                @csrf
                <input type="hidden" name="cart" id="mobile-cart-input">

                <div id="mobile-cart-items" class="space-y-3 mb-4 max-h-60 overflow-y-auto"></div>

                <div class="border-t pt-4 space-y-4">
                    <div>
                        <label for="mobile_payment_method" class="block text-sm font-medium text-gray-700 mb-2">Metode
                            Pembayaran</label>
                        <select name="payment_method" id="mobile_payment_method"
                            class="block w-full border border-gray-200 rounded-xl shadow-sm py-2.5 px-4 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="cash">💵 Cash</option>
                            <option value="qris">📱 QRIS</option>
                            <option value="debit">💳 Debit Card</option>
                        </select>
                    </div>
                </div>

                <div class="border-t my-4"></div>

                <div class="flex justify-between items-center mb-4">
                    <span class="text-lg font-bold text-gray-800">Total:</span>
                    <span id="mobile-total-price"
                        class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Rp
                        0</span>
                </div>

                <button type="submit"
                    class="w-full bg-gradient-to-r from-indigo-500 to-purple-500 text-white font-bold py-3 rounded-xl hover:shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                    id="mobile-checkout-btn" disabled>
                    <i class="fa-solid fa-check mr-2"></i>Checkout
                </button>
            </form>
        </div>
    </div>

    {{-- QR Scanner Modal --}}
    <div id="qr-scanner-modal" class="fixed inset-0 bg-black/70 z-50 hidden">
        <div
            class="fixed inset-4 md:inset-auto md:top-1/2 md:left-1/2 md:transform md:-translate-x-1/2 md:-translate-y-1/2 bg-white rounded-2xl p-6 max-w-lg w-full">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-800">Scan QR Code Produk</h2>
                <button id="close-qr-scanner" class="text-gray-500 hover:text-gray-700">
                    <i class="fa-solid fa-times text-2xl"></i>
                </button>
            </div>

            <div id="qr-reader" class="w-full"></div>

            <div class="mt-4 text-center text-sm text-gray-600">
                <p>Arahkan kamera ke QR code produk</p>
            </div>
        </div>
    </div>

    {{-- QR Code Scanner Library --}}
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    <script>
        function applySorting(sortValue) {
            const url = new URL(window.location);
            url.searchParams.set('sort', sortValue);
            url.searchParams.delete('page');
            window.location.href = url.toString();
        }

        document.addEventListener('DOMContentLoaded', function() {
            const productList = document.getElementById('product-list');
            const cartItemsContainer = document.getElementById('cart-items');
            const mobileCartItemsContainer = document.getElementById('mobile-cart-items');
            const totalPriceEl = document.getElementById('total-price');
            const mobileTotalPriceEl = document.getElementById('mobile-total-price');
            const checkoutForm = document.getElementById('checkout-form');
            const mobileCheckoutForm = document.getElementById('mobile-checkout-form');
            const cartInput = document.getElementById('cart-input');
            const mobileCartInput = document.getElementById('mobile-cart-input');
            const checkoutBtn = document.getElementById('checkout-btn');
            const mobileCheckoutBtn = document.getElementById('mobile-checkout-btn');
            const mobileCartToggle = document.getElementById('mobile-cart-toggle');
            const mobileCartModal = document.getElementById('mobile-cart-modal');
            const closeMobileCart = document.getElementById('close-mobile-cart');
            const mobileCartCount = document.getElementById('mobile-cart-count');
            const desktopCartCount = document.getElementById('desktop-cart-count');

            // QR Scanner elements
            const qrScannerBtn = document.getElementById('qr-scanner-btn');
            const qrScannerModal = document.getElementById('qr-scanner-modal');
            const closeQrScanner = document.getElementById('close-qr-scanner');
            let html5QrCode = null;

            let cart = [];

            // Check if QR product parameter exists in URL
            const urlParams = new URLSearchParams(window.location.search);
            const qrProductId = urlParams.get('qr_product');
            if (qrProductId) {
                fetchAndAddProductFromQR(qrProductId);
                // Remove the parameter from URL
                urlParams.delete('qr_product');
                const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
                window.history.replaceState({}, '', newUrl);
            }

            // QR Scanner Functions
            if (qrScannerBtn && qrScannerModal) {
                qrScannerBtn.addEventListener('click', function() {
                    qrScannerModal.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                    startQrScanner();
                });
            }

            if (closeQrScanner && qrScannerModal) {
                closeQrScanner.addEventListener('click', function() {
                    stopQrScanner();
                    qrScannerModal.classList.add('hidden');
                    document.body.style.overflow = 'auto';
                });
            }

            if (qrScannerModal) {
                qrScannerModal.addEventListener('click', function(e) {
                    if (e.target === qrScannerModal) {
                        stopQrScanner();
                        qrScannerModal.classList.add('hidden');
                        document.body.style.overflow = 'auto';
                    }
                });
            }

            function startQrScanner() {
                html5QrCode = new Html5Qrcode("qr-reader");

                html5QrCode.start({
                        facingMode: "environment"
                    }, {
                        fps: 10,
                        qrbox: {
                            width: 250,
                            height: 250
                        }
                    },
                    (decodedText, decodedResult) => {
                        // Extract product ID from URL
                        try {
                            // Support absolute and relative URLs (debug logging added)
                            const url = new URL(decodedText, window.location.origin);
                            console.debug('QR decoded:', decodedText, '=>', url.href);
                            const productId = url.searchParams.get('qr_product');

                            if (productId) {
                                fetchAndAddProductFromQR(productId);
                                stopQrScanner();
                                qrScannerModal.classList.add('hidden');
                                document.body.style.overflow = 'auto';
                            } else {
                                showNotification('QR Code tidak valid!', 'error');
                            }
                        } catch (e) {
                            showNotification('Format QR Code tidak valid!', 'error');
                        }
                    },
                    (errorMessage) => {
                        // Scanning error, ignore
                    }
                ).catch((err) => {
                    console.error('Error starting QR scanner:', err);
                    showNotification('Gagal mengakses kamera!', 'error');
                });
            }

            function stopQrScanner() {
                if (html5QrCode) {
                    html5QrCode.stop().then(() => {
                        html5QrCode.clear();
                        html5QrCode = null;
                    }).catch((err) => {
                        console.error('Error stopping scanner:', err);
                    });
                }
            }

            function fetchAndAddProductFromQR(productId) {
                fetch(`/api/product/${productId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const product = data.product;

                            if (product.stock <= 0) {
                                showNotification('Produk ini stoknya habis!', 'error');
                                return;
                            }

                            const existingItem = cart.find(item => item.id === product.id);
                            const currentQuantityInCart = existingItem ? existingItem.quantity : 0;

                            if (currentQuantityInCart >= product.stock) {
                                showNotification('Stok tidak mencukupi!', 'error');
                                return;
                            }

                            if (existingItem) {
                                existingItem.quantity++;
                            } else {
                                cart.push({
                                    id: product.id,
                                    name: product.name,
                                    price: product.price,
                                    quantity: 1,
                                    maxStock: product.stock
                                });
                            }

                            showNotification(`${product.name} ditambahkan ke keranjang dari QR!`);
                            renderCart();
                        } else {
                            showNotification('Produk tidak ditemukan!', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('Gagal memuat produk!', 'error');
                    });
            }

            // Mobile cart toggle
            if (mobileCartToggle && mobileCartModal) {
                mobileCartToggle.addEventListener('click', function() {
                    mobileCartModal.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                });
            }

            if (closeMobileCart && mobileCartModal) {
                closeMobileCart.addEventListener('click', function() {
                    mobileCartModal.classList.add('hidden');
                    document.body.style.overflow = 'auto';
                });
            }

            if (mobileCartModal) {
                mobileCartModal.addEventListener('click', function(e) {
                    if (e.target === mobileCartModal) {
                        mobileCartModal.classList.add('hidden');
                        document.body.style.overflow = 'auto';
                    }
                });
            }

            function renderCart() {
                renderCartItems(cartItemsContainer);
                renderCartItems(mobileCartItemsContainer);
                updateTotalPrice();
                updateCartCount();
                updateCheckoutButton();
            }

            function renderCartItems(container) {
                if (!container) return;

                container.innerHTML = '';

                if (cart.length === 0) {
                    container.innerHTML = `
                        <div class="text-center text-gray-500 py-8">
                            <i class="fa-solid fa-shopping-cart text-4xl text-gray-300 mb-2"></i>
                            <p>Keranjang masih kosong</p>
                        </div>
                    `;
                } else {
                    cart.forEach(item => {
                        const itemHtml = `
                            <div class="cart-item flex justify-between items-center bg-gradient-to-r from-gray-50 to-indigo-50 p-3 rounded-xl border border-gray-100">
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-gray-800 text-sm truncate">${item.name}</p>
                                    <p class="text-xs text-gray-500">Rp ${formatRupiah(item.price)}</p>
                                </div>
                                <div class="flex items-center space-x-2 ml-2">
                                    <button type="button" class="cart-quantity-btn w-8 h-8 flex items-center justify-center text-indigo-600 hover:text-indigo-800 bg-white rounded-lg border border-indigo-200 hover:border-indigo-400 transition-all" data-id="${item.id}" data-action="decrease">
                                        <i class="fa-solid fa-minus text-xs"></i>
                                    </button>
                                    <span class="font-bold text-sm min-w-[2rem] text-center text-indigo-600">${item.quantity}</span>
                                    <button type="button" class="cart-quantity-btn w-8 h-8 flex items-center justify-center text-indigo-600 hover:text-indigo-800 bg-white rounded-lg border border-indigo-200 hover:border-indigo-400 transition-all" data-id="${item.id}" data-action="increase">
                                        <i class="fa-solid fa-plus text-xs"></i>
                                    </button>
                                    <button type="button" class="cart-remove-btn text-red-500 hover:text-red-700 ml-1 w-8 h-8 flex items-center justify-center bg-red-50 rounded-lg hover:bg-red-100 transition-all" data-id="${item.id}">
                                        <i class="fa-solid fa-trash text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        `;
                        container.insertAdjacentHTML('beforeend', itemHtml);
                    });
                }
            }

            function updateTotalPrice() {
                const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                const formattedTotal = `Rp ${formatRupiah(total)}`;
                if (totalPriceEl) totalPriceEl.textContent = formattedTotal;
                if (mobileTotalPriceEl) mobileTotalPriceEl.textContent = formattedTotal;
            }

            function updateCartCount() {
                const count = cart.reduce((sum, item) => sum + item.quantity, 0);
                if (mobileCartCount) mobileCartCount.textContent = count;
                if (desktopCartCount) desktopCartCount.textContent = count;
            }

            function updateCheckoutButton() {
                const isEmpty = cart.length === 0;
                if (checkoutBtn) {
                    checkoutBtn.disabled = isEmpty;
                    checkoutBtn.innerHTML = isEmpty ? '<i class="fa-solid fa-ban mr-2"></i>Keranjang Kosong' :
                        '<i class="fa-solid fa-check mr-2"></i>Checkout';
                }
                if (mobileCheckoutBtn) {
                    mobileCheckoutBtn.disabled = isEmpty;
                    mobileCheckoutBtn.innerHTML = isEmpty ? '<i class="fa-solid fa-ban mr-2"></i>Keranjang Kosong' :
                        '<i class="fa-solid fa-check mr-2"></i>Checkout';
                }
            }

            function formatRupiah(number) {
                return new Intl.NumberFormat('id-ID').format(number);
            }

            function showNotification(message, type = 'success') {
                const notification = document.createElement('div');
                notification.className =
                    `fixed top-4 right-4 z-50 p-4 rounded-xl shadow-lg ${type === 'success' ? 'bg-gradient-to-r from-green-500 to-emerald-500' : 'bg-gradient-to-r from-red-500 to-rose-500'} text-white transform translate-x-full transition-transform duration-300`;
                notification.innerHTML = `
                    <div class="flex items-center space-x-3">
                        <i class="fa-solid ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'} text-xl"></i>
                        <span class="font-medium">${message}</span>
                    </div>
                `;

                document.body.appendChild(notification);

                setTimeout(() => notification.classList.remove('translate-x-full'), 100);
                setTimeout(() => {
                    notification.classList.add('translate-x-full');
                    setTimeout(() => document.body.removeChild(notification), 300);
                }, 3000);
            }

            if (productList) {
                productList.addEventListener('click', function(e) {
                    if (e.target.closest('.add-to-cart-btn') && !e.target.closest('.add-to-cart-btn')
                        .disabled) {
                        const card = e.target.closest('.product-card');
                        const productId = parseInt(card.dataset.id);
                        const productName = card.dataset.name;
                        const productPrice = parseFloat(card.dataset.price);
                        const productStock = parseInt(card.dataset.stock);

                        const existingItem = cart.find(item => item.id === productId);
                        const currentQuantityInCart = existingItem ? existingItem.quantity : 0;

                        if (currentQuantityInCart >= productStock) {
                            showNotification('Stok tidak mencukupi!', 'error');
                            return;
                        }

                        if (existingItem) {
                            existingItem.quantity++;
                        } else {
                            cart.push({
                                id: productId,
                                name: productName,
                                price: productPrice,
                                quantity: 1,
                                maxStock: productStock
                            });
                        }

                        showNotification(`${productName} ditambahkan ke keranjang`);
                        renderCart();
                    }
                });
            }

            function handleCartActions(e) {
                const target = e.target.closest('.cart-quantity-btn, .cart-remove-btn');
                if (!target) return;

                const productId = parseInt(target.dataset.id);
                const itemInCart = cart.find(item => item.id === productId);

                if (!itemInCart) return;

                if (target.classList.contains('cart-remove-btn')) {
                    cart = cart.filter(item => item.id !== productId);
                    showNotification('Item dihapus dari keranjang');
                }

                if (target.classList.contains('cart-quantity-btn')) {
                    const action = target.dataset.action;
                    if (action === 'increase') {
                        if (itemInCart.quantity < itemInCart.maxStock) {
                            itemInCart.quantity++;
                        } else {
                            showNotification('Stok tidak mencukupi!', 'error');
                            return;
                        }
                    } else if (action === 'decrease') {
                        if (itemInCart.quantity > 1) {
                            itemInCart.quantity--;
                        } else {
                            cart = cart.filter(item => item.id !== productId);
                            showNotification('Item dihapus dari keranjang');
                        }
                    }
                }
                renderCart();
            }

            if (cartItemsContainer) cartItemsContainer.addEventListener('click', handleCartActions);
            if (mobileCartItemsContainer) mobileCartItemsContainer.addEventListener('click', handleCartActions);

            function handleCheckout(e, isMobile = false) {
                if (cart.length === 0) {
                    e.preventDefault();
                    showNotification('Keranjang Anda kosong!', 'error');
                    return;
                }

                const cartData = JSON.stringify(cart);
                if (isMobile) {
                    if (mobileCartInput) mobileCartInput.value = cartData;
                } else {
                    if (cartInput) cartInput.value = cartData;
                }
            }

            if (checkoutForm) checkoutForm.addEventListener('submit', (e) => handleCheckout(e, false));
            if (mobileCheckoutForm) mobileCheckoutForm.addEventListener('submit', (e) => handleCheckout(e, true));

            renderCart();

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
