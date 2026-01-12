<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Enuma Vault</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <style>
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .animate-gradient {
            background-size: 200% 200%;
            animation: gradient 6s ease infinite;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 relative overflow-auto">

    <style>
        .bg-anim { position: absolute; inset: 0; z-index: 0; background: linear-gradient(135deg, rgba(99,102,241,0.03) 0%, rgba(167,139,250,0.02) 50%); }
        #tsparticles { width: 100%; height: 100%; }
        .bg-anim::after { content: ''; position: absolute; inset: 0; background: linear-gradient(180deg, rgba(255,255,255,0.62), rgba(255,255,255,0.36)); pointer-events: none; }
    </style>

    <div class="bg-anim" aria-hidden="true">
        <div id="tsparticles"></div>
    </div>

    <div class="w-full max-w-md relative z-10">
        <div class="bg-white/40 backdrop-blur-md rounded-3xl p-8 border border-white/20" style="box-shadow: 0 12px 30px rgba(124,58,237,0.12);">
            {{-- Logo & Branding --}}
            <div class="text-center mb-8">
                <div class="w-20 h-20 mx-auto mb-4 rounded-2xl flex items-center justify-center overflow-hidden bg-gradient-to-br from-indigo-500 to-purple-500 p-1">
                    <div class="w-full h-full rounded-xl bg-white/10 flex items-center justify-center">
                        <img src="{{ asset('logo.png') }}" alt="Logo" class="w-4/5 h-4/5 object-contain">
                    </div>
                </div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent mb-2">
                    Enuma Vault
                </h1>
                <h2 class="text-xl font-semibold text-purple-900/90">Buat Akun Baru</h2>
                <p class="text-purple-800/70 mt-1">Isi data untuk membuat akun baru</p>
            </div>

            <form action="/register" method="POST" class="space-y-5">
                @csrf

                {{-- Name Input --}}
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Nama Lengkap</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fa-solid fa-user text-purple-700/70"></i>
                        </div>
                        <input type="text" name="name" id="name" required 
                               class="block w-full pl-12 pr-4 py-3 border border-transparent bg-white/60 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-400 transition-all text-purple-900 placeholder-purple-700/60"
                               placeholder="John Doe">
                    </div>
                </div>

                {{-- Email Input --}}
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fa-solid fa-envelope text-purple-700/70"></i>
                        </div>
                        <input type="email" name="email" id="email" required 
                               class="block w-full pl-12 pr-4 py-3 border border-transparent bg-white/60 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-400 transition-all text-purple-900 placeholder-purple-700/60"
                               placeholder="you@example.com">
                    </div>
                </div>

                {{-- Password Input --}}
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fa-solid fa-lock text-purple-700/70"></i>
                        </div>
                        <input type="password" name="password" id="password" required
                               class="block w-full pl-12 pr-4 py-3 border border-transparent bg-white/60 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-400 transition-all text-purple-900 placeholder-purple-700/60"
                               placeholder="Minimal 8 karakter">
                    </div>
                    <p class="mt-1 text-xs text-purple-800/60">
                        <i class="fa-solid fa-info-circle mr-1"></i>
                        Gunakan kombinasi huruf, angka dan simbol
                    </p>
                </div>

                {{-- Password Confirmation --}}
                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">Konfirmasi Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fa-solid fa-lock text-purple-700/70"></i>
                        </div>
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                               class="block w-full pl-12 pr-4 py-3 border border-transparent bg-white/60 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-400 transition-all text-purple-900 placeholder-purple-700/60"
                               placeholder="Ulangi password">
                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="pt-2">
                    <button type="submit" 
                            class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-xl shadow-md text-base font-semibold text-white bg-gradient-to-r from-indigo-500 to-purple-600 hover:shadow-lg transition-all">
                        <i class="fa-solid fa-user-plus mr-2"></i>
                        Daftar Sekarang
                    </button>
                </div>
            </form>

            {{-- Back to Login Link --}}
            <p class="mt-6 text-center text-sm text-gray-700">Sudah punya akun? <a href="/login" class="font-semibold text-indigo-600 hover:text-indigo-700">Masuk di sini</a></p>
        </div>

    </div>

    <!-- tsParticles scripts -->
    <script src="https://cdn.jsdelivr.net/npm/tsparticles@2/tsparticles.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            tsParticles.load('tsparticles', {
                fullScreen: { enable: false },
                detectRetina: true,
                fpsLimit: 60,
                background: { color: { value: 'transparent' } },
                particles: {
                    number: { value: 30, density: { enable: true, area: 800 } },
                    color: { value: ['#7c3aed', '#8b5cf6', '#a78bfa', '#fb7185'] },
                    shape: { type: 'circle' },
                    opacity: { value: 0.7, random: { enable: true, minimumValue: 0.3 } },
                    size: { value: { min: 20, max: 60 }, animation: { enable: true, speed: 6, minimumValue: 20, sync: false } },
                    move: { enable: true, speed: 1.5, direction: 'none', outMode: 'out' },
                    links: { enable: false }
                },
                interactivity: {
                    detectsOn: 'canvas',
                    events: { onHover: { enable: true, mode: 'repulse' }, onClick: { enable: false } },
                    modes: { repulse: { distance: 120, duration: 0.4 } }
                }
            });
        });
    </script>

</body>
</html>