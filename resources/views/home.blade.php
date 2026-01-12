@extends('layout')

@section('title', 'Home - Dashboard')

@section('content')
<div class="space-y-6">
    {{-- Dashboard Title --}}
    <div class="flex items-center gap-3">
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-600 to-purple-600 flex items-center justify-center">
            <i class="fa-solid fa-house text-white text-xl"></i>
        </div>
        <h1 class="text-2xl md:text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
            Dashboard
        </h1>
    </div>

    {{-- Hero / Quick Actions for role user --}}
    @if(Auth::user()->role === 'user')
    <div class="mt-6 bg-transparent">
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <div class="grid grid-cols-1 gap-4">
                <div>
                    <p class="text-xl font-semibold">Halo, {{ explode(' ', trim(Auth::user()->name))[0] }} <span class="wave">👋</span></p>
                    <p class="text-sm text-gray-500 mt-1">Selamat datang di dashboard anggota. Di sini kamu bisa melihat status pinjaman dan melakukan aksi cepat.</p>
                </div>

                <div class="flex flex-col lg:flex-row justify-between items-center gap-4">
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('loan.borrow') }}" class="flex items-center gap-3 px-6 py-3 rounded-full text-white font-semibold tracking-wide shadow-md bg-gradient-to-r from-indigo-600 to-purple-600">
                            <span class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center">
                                <i class="fa-solid fa-box text-white text-lg"></i>
                            </span>
                            <span class="text-base">Pinjam Barang</span>
                        </a>

                        <a href="{{ route('loan.return') }}" class="flex items-center gap-3 px-5 py-3 rounded-xl text-white font-semibold tracking-wide shadow-sm bg-gradient-to-r from-emerald-500 to-teal-400">
                            <span class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center text-white">
                                <i class="fa-solid fa-rotate-left text-lg"></i>
                            </span>
                            <span class="font-medium text-sm">Kembalikan</span>
                        </a>

                        <a href="/history" class="flex items-center gap-3 px-5 py-3 rounded-xl text-white font-semibold tracking-wide shadow-sm bg-gradient-to-r from-amber-400 to-orange-400">
                            <span class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center text-white">
                                <i class="fa-solid fa-clock-rotate-left text-lg"></i>
                            </span>
                            <span class="font-medium text-sm">Riwayat</span>
                        </a>

                        <a href="/profile" class="flex items-center gap-3 px-5 py-3 rounded-xl text-white font-semibold tracking-wide shadow-sm bg-gradient-to-r from-sky-500 to-cyan-500">
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
                                <div class="text-2xl font-bold text-indigo-800">{{ $sedangDipinjam ?? 0 }}</div>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 w-44 flex items-center gap-3">
                            <div class="w-12 h-12 rounded-lg flex items-center justify-center bg-yellow-50 text-yellow-700">
                                <i class="fa-solid fa-hourglass-half text-lg"></i>
                            </div>
                            <div class="text-left">
                                <div class="text-xs text-gray-500">Menunggu Persetujuan</div>
                                <div class="text-2xl font-bold text-gray-800">{{ $menungguPersetujuan ?? 0 }}</div>
                            </div>
                        </div>

                        <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 w-44 flex items-center gap-3">
                            <div class="w-12 h-12 rounded-lg flex items-center justify-center bg-red-50 text-red-600">
                                <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                            </div>
                            <div class="text-left">
                                <div class="text-xs text-gray-500">Jatuh Tempo (3 hari)</div>
                                <div class="text-2xl font-bold text-red-500">{{ $jatuhTempo ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <a class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-300 group" href="/manage">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center mb-3 shadow-lg group-hover:scale-110 transition-transform">
                <i class="fa-solid fa-boxes text-xl text-white"></i>
            </div>
            <div class="text-xs text-gray-600 mb-1">Total Barang</div>
            <div class="text-2xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">
                {{ $totalBarang }}
            </div>
        </a>

        <a class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-300 group" href="/manage">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-orange-500 to-red-500 flex items-center justify-center mb-3 shadow-lg group-hover:scale-110 transition-transform">
                <i class="fa-solid fa-box-open text-xl text-white"></i>
            </div>
            <div class="text-xs text-gray-600 mb-1">Barang Kosong</div>
            <div class="text-2xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">
                {{ $stokHabis }}
            </div>
        </a>

        <a class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-300 group" href="/manage">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center mb-3 shadow-lg group-hover:scale-110 transition-transform">
                <i class="fa-solid fa-boxes-stacked text-xl text-white"></i>
            </div>
            <div class="text-xs text-gray-600 mb-1">Barang Tersedia</div>
            <div class="text-2xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">
                {{ $stokTersedia }}
            </div>
        </a>

        <a class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-300 group" href="/history">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center mb-3 shadow-lg group-hover:scale-110 transition-transform">
                <i class="fa-solid fa-box text-xl text-white"></i>
            </div>
            <div class="text-xs text-gray-600 mb-1">Sedang Dipinjam</div>
            <div class="text-2xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">
                {{ $sedangDipinjam }}
            </div>
        </a>

        <a class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-300 group" href="/admin/loans">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-yellow-500 to-orange-500 flex items-center justify-center mb-3 shadow-lg group-hover:scale-110 transition-transform">
                <i class="fa-solid fa-clock text-xl text-white"></i>
            </div>
            <div class="text-xs text-gray-600 mb-1">Menunggu Persetujuan</div>
            <div class="text-2xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">
                {{ $menungguPersetujuan }}
            </div>
        </a>

        <a class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-300 group" href="/admin/loans">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-cyan-500 to-blue-500 flex items-center justify-center mb-3 shadow-lg group-hover:scale-110 transition-transform">
                <i class="fa-solid fa-rotate-left text-xl text-white"></i>
            </div>
            <div class="text-xs text-gray-600 mb-1">Menunggu Pengembalian</div>
            <div class="text-2xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">
                {{ $menungguPengembalian }}
            </div>
        </a>
    </div>

    <!-- Charts Container -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <!-- Daily Sales Chart -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center">
                        <i class="fa-solid fa-chart-line text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-800">Pinjaman Harian</h2>
                        <p class="text-xs text-gray-500">7 Hari Terakhir</p>
                    </div>
                </div>
            </div>
            <div class="h-80">
                <canvas id="dailySalesChart"></canvas>
            </div>
        </div>

        <!-- Monthly Sales Chart -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <div class="flex justify-between items-center mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-green-500 to-emerald-500 flex items-center justify-center">
                        <i class="fa-solid fa-chart-bar text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-800">Pinjaman Bulanan</h2>
                        <p class="text-xs text-gray-500">12 Bulan Terakhir</p>
                    </div>
                </div>
            </div>
            <div class="h-80">
                <canvas id="monthlySalesChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Additional Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Payment Method Chart -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-pink-500 to-rose-500 flex items-center justify-center">
                    <i class="fa-solid fa-hands-holding text-white"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-800">Status Pinjaman</h2>
                    <p class="text-xs text-gray-500">Distribusi Status</p>
                </div>
            </div>
            <div class="h-80">
                <canvas id="paymentMethodChart"></canvas>
            </div>
        </div>

        <!-- Top Products Chart -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-orange-500 to-amber-500 flex items-center justify-center">
                    <i class="fa-solid fa-trophy text-white"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-800">Barang Paling Sering Dipinjam</h2>
                    <p class="text-xs text-gray-500">Top 5 Barang</p>
                </div>
            </div>
            <div class="h-80">
                <canvas id="topProductsChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        // Daily Sales Chart
        const dailyCtx = document.getElementById('dailySalesChart').getContext('2d');
        const dailySalesChart = new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($dailyLabels) !!},
                datasets: [{
                    label: 'Pinjaman Harian',
                    data: {!! json_encode($dailyData) !!},
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    borderColor: 'rgba(99, 102, 241, 1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: 'rgba(99, 102, 241, 1)',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value, index, values) {
                                return value;
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += context.parsed.y + ' pinjaman';
                                }
                                return label;
                            }
                        },
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        cornerRadius: 8
                    },
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Monthly Sales Chart
        const monthlyCtx = document.getElementById('monthlySalesChart').getContext('2d');
        const monthlySalesChart = new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($monthlyLabels) !!},
                datasets: [{
                    label: 'Pinjaman Bulanan',
                    data: {!! json_encode($monthlyData) !!},
                    backgroundColor: function(context) {
                        const chart = context.chart;
                        const {ctx, chartArea} = chart;
                        if (!chartArea) return null;
                        
                        const gradient = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
                        gradient.addColorStop(0, 'rgba(34, 197, 94, 0.2)');
                        gradient.addColorStop(1, 'rgba(34, 197, 94, 0.8)');
                        return gradient;
                    },
                    borderColor: 'rgba(34, 197, 94, 1)',
                    borderWidth: 2,
                    borderRadius: 10,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value, index, values) {
                                return value;
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += context.parsed.y + ' pinjaman';
                                }
                                return label;
                            }
                        },
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        cornerRadius: 8
                    },
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Payment Method Chart
        const paymentCtx = document.getElementById('paymentMethodChart').getContext('2d');
        const paymentMethodChart = new Chart(paymentCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($statusLabels) !!},
                datasets: [{
                    data: {!! json_encode($statusData) !!},
                    backgroundColor: [
                        'rgba(99, 102, 241, 0.8)',
                        'rgba(168, 85, 247, 0.8)',
                        'rgba(236, 72, 153, 0.8)'
                    ],
                    borderColor: [
                        'rgba(99, 102, 241, 1)',
                        'rgba(168, 85, 247, 1)',
                        'rgba(236, 72, 153, 1)'
                    ],
                    borderWidth: 2,
                    hoverBackgroundColor: [
                        'rgba(99, 102, 241, 1)',
                        'rgba(168, 85, 247, 1)',
                        'rgba(236, 72, 153, 1)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} transaksi (${percentage}%)`;
                            }
                        },
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        cornerRadius: 8
                    },
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    }
                }
            }
        });

        // Top Products Chart
        const productsCtx = document.getElementById('topProductsChart').getContext('2d');
        const topProductsChart = new Chart(productsCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($topProductLabels) !!},
                datasets: [{
                    label: 'Jumlah Pinjaman',
                    data: {!! json_encode($topProductData) !!},
                    backgroundColor: function(context) {
                        const chart = context.chart;
                        const {ctx, chartArea} = chart;
                        if (!chartArea) return null;
                        
                        const gradient = ctx.createLinearGradient(chartArea.left, 0, chartArea.right, 0);
                        gradient.addColorStop(0, 'rgba(249, 115, 22, 0.8)');
                        gradient.addColorStop(1, 'rgba(249, 115, 22, 0.2)');
                        return gradient;
                    },
                    borderColor: 'rgba(249, 115, 22, 1)',
                    borderWidth: 2,
                    borderRadius: 10,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    y: {
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label}: ${context.parsed.x} kali`;
                            }
                        },
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        cornerRadius: 8
                    },
                    legend: {
                        display: false
                    }
                }
            }
        });
    });
</script>
@endsection