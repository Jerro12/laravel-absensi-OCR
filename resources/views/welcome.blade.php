<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Absensi OCR') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-slate-900 text-white h-screen flex flex-col overflow-hidden">

    <!-- Header -->
    <header class="p-6 flex justify-between items-center bg-slate-800/50 backdrop-blur-md border-b border-white/10">
        <div class="flex items-center gap-3">
            <div class="h-10 w-10 bg-blue-600 rounded-lg flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
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

        <!-- Camera Feed Section -->
        <div class="flex-1 relative bg-black rounded-2xl overflow-hidden shadow-2xl border border-white/10">
            <video id="videoElement" class="w-full h-full object-cover" autoplay playsinline></video>
            <canvas id="canvas" class="hidden"></canvas>

            <!-- Instruction Overlay -->
            <div id="instructionOverlay"
                class="absolute inset-0 bg-gradient-to-b from-blue-900/80 to-black/80 backdrop-blur-sm flex items-center justify-center hidden">
                <div class="text-center p-8 max-w-md">
                    <div class="mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-32 w-32 mx-auto text-blue-400 animate-bounce"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                        </svg>
                    </div>
                    <h3 class="text-3xl font-bold mb-3 text-white" id="modeTitle">Absen Masuk</h3>
                    <p class="text-lg text-blue-200 mb-2 font-semibold">Pegang ID Card di depan wajah Anda</p>
                    <p class="text-sm text-slate-300 mb-8">Pastikan wajah dan ID card terlihat jelas dalam satu frame
                    </p>
                    <button id="captureBtn"
                        class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-400 px-10 py-4 rounded-full font-bold text-lg transition-all transform hover:scale-105 shadow-lg">
                        📸 Ambil Foto
                    </button>
                </div>
            </div>

            <!-- Status Badge -->
            <div class="absolute top-4 left-4">
                <div id="cameraStatus"
                    class="flex items-center gap-2 bg-black/60 backdrop-blur rounded-full px-3 py-1 border border-white/10">
                    <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
                    <span class="text-xs font-medium text-slate-300">Camera Inactive</span>
                </div>
            </div>
        </div>

        <!-- Action Section -->
        <div class="w-full md:w-[450px] flex flex-col gap-6 justify-center">

            <!-- Status Card -->
            <div class="bg-slate-800/50 rounded-2xl p-6 border border-white/5 backdrop-blur-sm">
                <h3 class="text-sm font-semibold text-slate-400 mb-4 uppercase tracking-wider">Status</h3>
                <div id="statusMessage" class="text-sm text-slate-300">
                    Klik tombol di bawah untuk memulai absensi
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="grid grid-cols-1 gap-4">
                <button id="checkInBtn"
                    class="relative group overflow-hidden bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 p-6 rounded-2xl shadow-lg transition-all duration-300 transform hover:scale-[1.02] active:scale-[0.98]">
                    <div
                        class="absolute right-0 top-0 h-full w-24 bg-white/10 -skew-x-12 transform translate-x-12 group-hover:translate-x-0 transition-transform duration-500">
                    </div>
                    <div class="flex items-center justify-between relative z-10">
                        <div class="text-left">
                            <h2 class="text-2xl font-bold text-white">ABSEN MASUK</h2>
                            <p class="text-emerald-100/80 text-sm mt-1">Pegang ID Card + Selfie</p>
                        </div>
                        <div class="h-12 w-12 bg-white/20 rounded-xl flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                        </div>
                    </div>
                </button>

                <button id="checkOutBtn"
                    class="relative group overflow-hidden bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-500 hover:to-blue-400 p-6 rounded-2xl shadow-lg transition-all duration-300 transform hover:scale-[1.02] active:scale-[0.98]">
                    <div
                        class="absolute right-0 top-0 h-full w-24 bg-white/10 -skew-x-12 transform translate-x-12 group-hover:translate-x-0 transition-transform duration-500">
                    </div>
                    <div class="flex items-center justify-between relative z-10">
                        <div class="text-left">
                            <h2 class="text-2xl font-bold text-white">ABSEN PULANG</h2>
                            <p class="text-blue-100/80 text-sm mt-1">Foto Wajah Saja</p>
                        </div>
                        <div class="h-12 w-12 bg-white/20 rounded-xl flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </div>
                    </div>
                </button>
            </div>

            <div class="text-center">
                <a href="{{ url('/admin/login') }}"
                    class="text-xs text-slate-500 hover:text-slate-300 transition-colors">Admin Login</a>
            </div>

        </div>
    </main>

    <script>
        // Clock
        function updateClock() {
            const now = new Date();
            document.getElementById('clock').textContent = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
            document.getElementById('date').textContent = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Elements
        const video = document.getElementById('videoElement');
        const canvas = document.getElementById('canvas');
        const checkInBtn = document.getElementById('checkInBtn');
        const checkOutBtn = document.getElementById('checkOutBtn');
        const statusMessage = document.getElementById('statusMessage');
        const cameraStatus = document.getElementById('cameraStatus');
        const instructionOverlay = document.getElementById('instructionOverlay');
        const modeTitle = document.getElementById('modeTitle');
        const captureBtn = document.getElementById('captureBtn');

        let stream = null;
        let currentMode = null;

        async function startCamera() {
            try {
                stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user', width: 1280, height: 720 } });
                video.srcObject = stream;
                cameraStatus.innerHTML = '<div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div><span class="text-xs font-medium text-slate-300">Camera Active</span>';
            } catch (error) {
                alert('Gagal mengakses kamera: ' + error.message);
            }
        }

        function stopCamera() {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                video.srcObject = null;
                cameraStatus.innerHTML = '<div class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></div><span class="text-xs font-medium text-slate-300">Camera Inactive</span>';
            }
        }

        function capturePhoto() {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            return new Promise((resolve) => {
                canvas.toBlob((blob) => resolve(blob), 'image/jpeg', 0.95);
            });
        }

        // Check-in: Show instruction to hold ID card
        checkInBtn.addEventListener('click', async () => {
            currentMode = 'check-in';
            await startCamera();
            modeTitle.textContent = 'Absen Masuk';
            instructionOverlay.classList.remove('hidden');
            statusMessage.textContent = 'Mode: Absen Masuk - Pegang ID card di depan wajah Anda';
        });

        // Check-out: Show instruction for selfie only
        checkOutBtn.addEventListener('click', async () => {
            currentMode = 'check-out';
            await startCamera();
            modeTitle.textContent = 'Absen Pulang';
            instructionOverlay.classList.remove('hidden');
            statusMessage.textContent = 'Mode: Absen Pulang - Ambil foto wajah Anda';
        });

        // Capture and submit
        captureBtn.addEventListener('click', async () => {
            statusMessage.textContent = 'Memproses absensi...';
            captureBtn.disabled = true;
            instructionOverlay.classList.add('hidden');

            try {
                const photoBlob = await capturePhoto();
                const formData = new FormData();
                formData.append('photo', photoBlob, 'photo.jpg');

                const endpoint = currentMode === 'check-in' ? '/attendance/check-in' : '/attendance/check-out';

                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    statusMessage.innerHTML = `
                        <div class="text-green-400 text-lg font-bold mb-2">✓ ${result.message}</div>
                        <div class="text-sm text-slate-400">
                            <strong>${result.data.name}</strong> - ${result.data.division}<br>
                            ${currentMode === 'check-in' ? 'Masuk: ' + result.data.check_in : 'Pulang: ' + result.data.check_out}<br>
                            <small>Score: ${result.data.score || 'N/A'}</small>
                        </div>
                    `;
                    stopCamera();
                } else {
                    statusMessage.innerHTML = `<span class="text-red-400 font-semibold">✗ ${result.message}</span>`;
                    instructionOverlay.classList.remove('hidden');
                }
            } catch (error) {
                statusMessage.innerHTML = `<span class="text-red-400">✗ Error: ${error.message}</span>`;
                instructionOverlay.classList.remove('hidden');
            } finally {
                captureBtn.disabled = false;
            }
        });
    </script>
</body>

</html>