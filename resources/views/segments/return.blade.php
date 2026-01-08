@extends('layout')

@section('title', 'Pengembalian - ' . $segment->name)

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold">Pengembalian - {{ $segment->name }}</h2>
                <p class="text-sm text-gray-500">Kode: {{ $segment->code }}</p>
            </div>
            <div class="text-right">
                <img src="https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl={{ urlencode(url('/return/segment/' . $segment->id)) }}"
                    alt="QR" class="w-28 h-28 border rounded-lg inline-block" />
                <div class="mt-2">
                    <a href="{{ url('/return/segment/' . $segment->id) }}" class="text-xs text-indigo-600">Buka</a>
                </div>
            </div>
        </div>

        @if(isset($activeLoans) && $activeLoans->count() > 0)
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 mb-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Barang yang Sedang Dipinjam di {{ $segment->name }}</h2>
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
                                </div>
                            </div>
                            <form action="{{ route('loan.return.submit') }}" method="POST" class="mt-3">
                                @csrf
                                <input type="hidden" name="transaction_id" value="{{ $loan->id }}">
                                <button type="submit" class="w-full bg-gradient-to-r from-orange-500 to-red-500 text-white font-semibold py-2 px-4 rounded-lg hover:shadow-lg transition-all flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-rotate-left"></i>
                                    <span>Ajukan Pengembalian</span>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>

    {{-- Modal / Scanner --}}
    <div id="qr-scanner-modal" class="fixed inset-0 bg-black/70 z-50 hidden">
        <div
            class="fixed inset-4 md:inset-auto md:top-1/2 md:left-1/2 md:transform md:-translate-x-1/2 md:-translate-y-1/2 bg-white rounded-2xl p-6 max-w-lg w-full">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-800">Scan QR Produk</h2>
                <button id="close-qr-scanner" class="text-gray-500 hover:text-gray-700"><i
                        class="fa-solid fa-times text-2xl"></i></button>
            </div>
            <div id="qr-reader" class="w-full"></div>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const qrScannerBtn = document.getElementById('qr-scanner-btn');
            const qrScannerModal = document.getElementById('qr-scanner-modal');
            const closeQrScanner = document.getElementById('close-qr-scanner');
            const qrReader = document.getElementById('qr-reader');
            let html5QrCode = null;

            if (qrScannerBtn) {
                qrScannerBtn.addEventListener('click', function() {
                    qrScannerModal.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                    startQrScanner();
                });
            }

            if (closeQrScanner) {
                closeQrScanner.addEventListener('click', function() {
                    stopQrScanner();
                    qrScannerModal.classList.add('hidden');
                    document.body.style.overflow = 'auto';
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

                            if (productId) {
                                // Panggil API untuk mencari transaksi returning pada segmen ini
                                fetch(`/api/segment/{{ $segment->id }}/product/${productId}`)
                                    .then(res => res.json())
                                    .then(data => {
                                        if (data.success) {
                                            stopQrScanner();
                                            qrScannerModal.classList.add('hidden');
                                            document.body.style.overflow = 'auto';

                                            const trx = data.transaction;
                                            showConfirmModal(trx);
                                        } else {
                                            alert(data.error ||
                                                'Tidak ditemukan transaksi pengembalian untuk produk ini.'
                                                );
                                        }
                                    }).catch(err => {
                                        console.error(err);
                                        alert('Gagal mencari transaksi.');
                                    });
                            } else {
                                alert('QR produk tidak valid.');
                            }
                        } catch (e) {
                            alert('Format QR tidak valid.');
                        }
                    },
                    (errorMessage) => {
                        // ignore
                    }
                ).catch(err => {
                    console.error(err);
                    alert('Gagal mengakses kamera.');
                });
            }

            function stopQrScanner() {
                if (html5QrCode) {
                    html5QrCode.stop().then(() => {
                        html5QrCode.clear();
                        html5QrCode = null;
                    }).catch(() => {});
                }
            }

            // Konfirmasi pengembalian via tombol list
            document.querySelectorAll('.confirm-return-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const trxId = btn.dataset.id;
                    openConfirmForm(trxId);
                });
            });

            function showConfirmModal(trx) {
                if (!confirm(`Konfirmasi pengembalian transaksi #${trx.invoice_number} oleh ${trx.user.name}?`))
                    return;

                // Buka form verifikasi (simple prompt for now)
                const condition = prompt('Kondisi saat diterima (good/damaged/lost):', 'good');
                if (!condition) return;

                const notes = prompt('Catatan pengembalian (opsional):', '');
                submitConfirmReturn(trx.id, condition, notes);
            }

            function openConfirmForm(trxId) {
                const condition = prompt('Kondisi saat diterima (good/damaged/lost):', 'good');
                if (!condition) return;
                const notes = prompt('Catatan pengembalian (opsional):', '');
                submitConfirmReturn(trxId, condition, notes);
            }

            function submitConfirmReturn(trxId, condition, notes) {
                fetch(`/admin/loans/${trxId}/confirm-return`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            condition_on_return: condition,
                            return_notes: notes
                        })
                    }).then(res => res.json().catch(() => res))
                    .then(res => {
                        // Jika server redirect, fallback ke reload
                        if (res && (res.redirect || res.success === undefined)) {
                            window.location.reload();
                        } else if (res && res.success === false) {
                            alert(res.error || 'Gagal mengonfirmasi pengembalian.');
                        } else {
                            window.location.reload();
                        }
                    }).catch(err => {
                        console.error(err);
                        window.location.reload();
                    });
            }
        });
    </script>
@endsection
