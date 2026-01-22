<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Absensi OCR') }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .camera-placeholder {
            background-image: url("data:image/svg+xml,%3Csvg width='64' height='64' viewBox='0 0 24 24' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M12 16C14.2091 16 16 14.2091 16 12C16 9.79086 14.2091 8 12 8C9.79086 8 8 9.79086 8 12C8 14.2091 9.79086 16 12 16Z' stroke='%239CA3AF' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3Cpath d='M3 7V5C3 3.89543 3.89543 3 5 3H7' stroke='%239CA3AF' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3Cpath d='M17 3H19C20.1046 3 21 3.89543 21 5V7' stroke='%239CA3AF' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3Cpath d='M21 17V19C21 20.1046 20.1046 21 19 21H17' stroke='%239CA3AF' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3Cpath d='M7 21H5C3.89543 21 3 20.1046 3 19V17' stroke='%239CA3AF' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: center;
        }
    </style>
</head>
<body class="bg-slate-900 text-white h-screen flex flex-col overflow-hidden">

    <!-- Header -->
    <header class="p-6 flex justify-between items-center bg-slate-800/50 backdrop-blur-md border-b border-white/10">
        <div class="flex items-center gap-3">
            <div class="h-10 w-10 bg-blue-600 rounded-lg flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-bold tracking-tight">ABSENSI OCR</h1>
                <p class="text-xs text-slate-400">Sistem Presensi Wajah & ID Card</p>
            </div>
        </div>
        <div class="text-right">
            <div id="clock" class="text-3xl font-mono font-bold text-blue-400">00:00:00</div>
            <div id="date" class="text-sm text-slate-400">Loading...</div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col md:flex-row p-6 gap-6 relative">
        
        <!-- Camera Feed Section (Left/Top) -->
        <div class="flex-1 relative bg-black rounded-2xl overflow-hidden shadow-2xl border border-white/10 group">
            <div class="absolute inset-0 camera-placeholder opacity-20"></div>
            
            <!-- Face Scanning Animation Overlay -->
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <div class="relative w-64 h-64 border-2 border-blue-500/30 rounded-full animate-pulse">
                    <div class="absolute inset-0 border-t-2 border-blue-400 rounded-full w-full h-full animate-spin"></div>
                    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                         <p class="text-blue-400/80 text-xs font-mono tracking-widest uppercase">Scanning...</p>
                    </div>
                </div>
            </div>

            <!-- Status Badge -->
            <div class="absolute top-4 left-4">
                <div class="flex items-center gap-2 bg-black/60 backdrop-blur rounded-full px-3 py-1 border border-white/10">
                    <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
                    <span class="text-xs font-medium text-slate-300">Camera Active</span>
                </div>
            </div>

            <video id="videoElement" class="w-full h-full object-cover hidden"></video>
        </div>

        <!-- Action Section (Right/Bottom) -->
        <div class="w-full md:w-[450px] flex flex-col gap-6 justify-center">
            
            <!-- User Info Card (Placeholder for detected user) -->
            <div class="bg-slate-800/50 rounded-2xl p-6 border border-white/5 backdrop-blur-sm">
                <h3 class="text-sm font-semibold text-slate-400 mb-4 uppercase tracking-wider">Deteksi Karyawan</h3>
                <div class="flex items-center gap-4">
                    <div class="h-16 w-16 rounded-full bg-slate-700 flex items-center justify-center border-2 border-slate-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div>
                        <div class="h-5 w-32 bg-slate-700 rounded mb-2 animate-pulse"></div>
                        <div class="h-3 w-24 bg-slate-700/50 rounded animate-pulse"></div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="grid grid-cols-1 gap-4">
                <button class="relative group overflow-hidden bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 p-6 rounded-2xl shadow-lg transition-all duration-300 transform hover:scale-[1.02] active:scale-[0.98]">
                    <div class="absolute right-0 top-0 h-full w-24 bg-white/10 -skew-x-12 transform translate-x-12 group-hover:translate-x-0 transition-transform duration-500"></div>
                    <div class="flex items-center justify-between relative z-10">
                        <div class="text-left">
                            <h2 class="text-2xl font-bold text-white">ABSEN MASUK</h2>
                            <p class="text-emerald-100/80 text-sm mt-1">Check-in Kehadiran</p>
                        </div>
                        <div class="h-12 w-12 bg-white/20 rounded-xl flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                        </div>
                    </div>
                </button>

                <button class="relative group overflow-hidden bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-400 p-6 rounded-2xl shadow-lg transition-all duration-300 transform hover:scale-[1.02] active:scale-[0.98]">
                     <div class="absolute right-0 top-0 h-full w-24 bg-white/10 -skew-x-12 transform translate-x-12 group-hover:translate-x-0 transition-transform duration-500"></div>
                     <div class="flex items-center justify-between relative z-10">
                        <div class="text-left">
                            <h2 class="text-2xl font-bold text-white">ABSEN PULANG</h2>
                            <p class="text-blue-100/80 text-sm mt-1">Check-out Kehadiran</p>
                        </div>
                        <div class="h-12 w-12 bg-white/20 rounded-xl flex items-center justify-center">
                             <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </div>
                    </div>
                </button>
            </div>
            
            <div class="text-center">
                <a href="{{ url('/admin/login') }}" class="text-xs text-slate-500 hover:text-slate-300 transition-colors">Admin Login</a>
            </div>

        </div>
    </main>

    <script>
        // Clock functionality
        function updateClock() {
            const now = new Date();
            
            // Time
            const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
            document.getElementById('clock').textContent = now.toLocaleTimeString('id-ID', timeOptions);
            
            // Date
            const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            document.getElementById('date').textContent = now.toLocaleDateString('id-ID', dateOptions);
        }
        
        setInterval(updateClock, 1000);
        updateClock();

        // Placeholder for camera access request (future implementation)
        /*
        if (navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(function (stream) {
                    var video = document.getElementById("videoElement");
                    video.srcObject = stream;
                    video.classList.remove('hidden');
                })
                .catch(function (error) {
                    console.log("Something went wrong with camera access");
                });
        }
        */
    </script>
</body>
</html>
