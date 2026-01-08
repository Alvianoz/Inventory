@extends('layout')

@section('title', 'Kembalikan Barang')

@section('content')
    {{-- Success/Error Messages --}}
    @if (session('success'))
        <div class="mb-6 p-4 bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 rounded-xl" role="alert">
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

    <div class="max-w-6xl mx-auto">
        <h1 class="text-2xl md:text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent mb-6">
            <i class="fa-solid fa-rotate-left mr-3"></i>Kembalikan Barang
        </h1>

        {{-- QR Scanner Section --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Scan QR Code Barang</h2>
            <button id="qr-scanner-btn" class="w-full bg-gradient-to-r from-blue-500 to-cyan-500 text-white font-semibold py-4 px-6 rounded-xl hover:shadow-lg transition-all flex items-center justify-center gap-3">
                <i class="fa-solid fa-qrcode text-2xl"></i>
                <span class="text-lg">Scan QR Code untuk Kembalikan</span>
            </button>
            <p class="mt-3 text-sm text-gray-500">Tip: Anda juga dapat <strong>scan QR segmen</strong> (label lokasi) untuk membuka halaman pengembalian per segmen.</p>
        </div>

        {{-- Active Loans List --}}
        @if($activeLoans->count() > 0)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Barang yang Sedang Dipinjam</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($activeLoans as $loan)
                        <div class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition-all">
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex-1">
                                    <h3 class="font-bold text-gray-800 mb-1">
                                        {{ $loan->products->first()->name }}
                                    </h3>
                                    <p class="text-sm text-gray-600">
                                        <span class="font-semibold">No. Pinjaman:</span> {{ $loan->invoice_number }}
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        <span class="font-semibold">Tanggal Pinjam:</span> {{ $loan->borrow_date->format('d/m/Y') }}
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        <span class="font-semibold">Durasi:</span> {{ $loan->duration }} hari
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        <span class="font-semibold">Segmen:</span> {{ $loan->products->first()->segment ? $loan->products->first()->segment->name : '-' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center">
                <i class="fa-solid fa-inbox text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Tidak ada barang yang sedang dipinjam</h3>
                <p class="text-gray-600">Anda belum meminjam barang apapun saat ini.</p>
            </div>
        @endif
    </div>

    {{-- QR Scanner Modal --}}
    <div id="qr-scanner-modal" class="fixed inset-0 bg-black/70 z-50 hidden">
        <div class="fixed inset-4 md:inset-auto md:top-1/2 md:left-1/2 md:transform md:-translate-x-1/2 md:-translate-y-1/2 bg-white rounded-2xl p-6 max-w-lg w-full">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-800">Scan QR Code Barang</h2>
                <button id="close-qr-scanner" class="text-gray-500 hover:text-gray-700">
                    <i class="fa-solid fa-times text-2xl"></i>
                </button>
            </div>

            <div id="qr-reader" class="w-full"></div>

            <div class="mt-4 text-center text-sm text-gray-600">
                <p>Arahkan kamera ke QR code barang yang ingin dikembalikan</p>
            </div>
        </div>
    </div>

    {{-- QR Code Scanner Library --}}
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const qrScannerBtn = document.getElementById('qr-scanner-btn');
            const qrScannerModal = document.getElementById('qr-scanner-modal');
            const closeQrScanner = document.getElementById('close-qr-scanner');
            let html5QrCode = null;

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
                        try {
                            const url = new URL(decodedText);
                            const productId = url.searchParams.get('qr_product');

                            // Jika QR berisi ID produk (format QR lama)
                            if (productId) {
                                fetchLoanAndSubmit(productId);
                                stopQrScanner();
                                qrScannerModal.classList.add('hidden');
                                document.body.style.overflow = 'auto';
                                return;
                            }

                            // Jika QR adalah segmen yang mengarah ke halaman pengembalian segmen
                            // Pastikan origin sama atau path mengandung /return/segment untuk safety
                            if ((url.origin === window.location.origin && url.pathname.includes('/return/segment')) || url.pathname.includes('/return/segment')) {
                                stopQrScanner();
                                qrScannerModal.classList.add('hidden');
                                document.body.style.overflow = 'auto';
                                window.location.href = url.href;
                                return;
                            }

                            showNotification('QR Code tidak valid atau tidak terkait dengan pengembalian!', 'error');
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

            function fetchLoanAndSubmit(productId) {
                fetch(`/api/loan/${productId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const transaction = data.transaction;
                            
                            // Create form and submit
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = '{{ route("loan.return.submit") }}';
                            
                            const csrfInput = document.createElement('input');
                            csrfInput.type = 'hidden';
                            csrfInput.name = '_token';
                            csrfInput.value = '{{ csrf_token() }}';
                            
                            const transactionInput = document.createElement('input');
                            transactionInput.type = 'hidden';
                            transactionInput.name = 'transaction_id';
                            transactionInput.value = transaction.id;
                            
                            form.appendChild(csrfInput);
                            form.appendChild(transactionInput);
                            document.body.appendChild(form);
                            form.submit();
                        } else {
                            showNotification(data.error || 'Tidak ada pinjaman aktif untuk produk ini!', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('Gagal memuat data pinjaman!', 'error');
                    });
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

            // Auto-hide alerts
            setTimeout(function() {
                const alerts = document.querySelectorAll('.bg-gradient-to-r');
                alerts.forEach(alert => {
                    if (alert.classList.contains('from-green-50') || alert.classList.contains('from-red-50')) {
                        alert.style.transition = 'opacity 0.5s';
                        alert.style.opacity = '0';
                        setTimeout(() => alert.remove(), 500);
                    }
                });
            }, 5000);
        });
    </script>
@endsection