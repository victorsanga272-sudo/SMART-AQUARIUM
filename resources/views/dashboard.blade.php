<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Smart Aquarium Dashboard</title>
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.documentElement.dataset.theme = localStorage.getItem('dashboard_theme') === 'light' ? 'light' : 'dark';
    </script>
    <style>
        html[data-theme="light"] body {
            background: #f1f5f9;
            color: #0f172a;
        }

        html[data-theme="light"] .dashboard-header {
            background: #ffffff;
            border-color: #cbd5e1;
        }

        html[data-theme="light"] .dashboard-surface {
            background: #ffffff;
            border-color: #cbd5e1;
            color: #0f172a;
        }

        html[data-theme="light"] .dashboard-surface .text-white,
        html[data-theme="light"] .dashboard-surface .text-slate-200,
        html[data-theme="light"] .dashboard-surface .text-slate-300,
        html[data-theme="light"] .dashboard-surface .text-slate-400,
        html[data-theme="light"] .dashboard-header .text-slate-200,
        html[data-theme="light"] .dashboard-header .text-slate-300,
        html[data-theme="light"] .dashboard-header .text-slate-400 {
            color: #334155;
        }

        html[data-theme="light"] .dashboard-settings-panel .text-slate-200,
        html[data-theme="light"] .dashboard-settings-panel .text-slate-300,
        html[data-theme="light"] .dashboard-settings-panel .text-slate-400 {
            color: #334155;
        }

        html[data-theme="light"] .dashboard-surface .bg-slate-700 {
            background: #e2e8f0;
            color: #1e293b;
        }

        html[data-theme="light"] .dashboard-logout {
            background: #e11d48;
            color: #ffffff;
        }

        html[data-theme="light"] .dashboard-logout:hover {
            background: #be123c;
        }

        html[data-theme="light"] .dashboard-settings-panel {
            border-color: #cbd5e1;
            background: #ffffff;
            color: #0f172a;
        }

        html[data-theme="light"] .dashboard-header details > summary {
            background: #e2e8f0;
            color: #1e293b;
        }

        html[data-theme="light"] .dashboard-settings-panel .bg-slate-900 {
            background: #f1f5f9;
        }

        html[data-theme="light"] .dashboard-settings-panel input {
            border-color: #94a3b8;
            background: #ffffff;
            color: #0f172a;
        }

        html[data-theme="light"] .dashboard-settings-panel input::placeholder {
            color: #64748b;
            opacity: 1;
        }

        html[data-theme="light"] .dashboard-settings-panel input:focus {
            border-color: #2563eb;
            outline: 2px solid #93c5fd;
            outline-offset: 1px;
        }

        html[data-theme="light"] .dashboard-theme-button[aria-pressed="true"] {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .dashboard-theme-button[aria-pressed="true"] {
            background: #334155;
            color: #ffffff;
        }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen">

    <!-- Header -->
    <header class="dashboard-header bg-slate-800 border-b border-slate-700">
        <div class="mx-auto flex w-full max-w-7xl flex-col gap-4 px-4 py-4 sm:px-6 md:flex-row md:items-center md:justify-between">
            <div class="flex min-w-0 items-center justify-between gap-4">
                <div class="flex min-w-0 items-center gap-3">
                    <a href="{{ route('terms') }}" aria-label="SACP terms and conditions" title="SACP terms and conditions" class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-blue-600 text-xs font-bold text-white transition-colors hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-400">SACP</a>
                    <h1 class="truncate text-lg font-bold text-blue-400">SACP Dashboard</h1>
                </div>
                <div class="flex shrink-0 items-center gap-2">
                    <span id="statusPing" class="h-2.5 w-2.5 rounded-full bg-slate-500"></span>
                    <span id="statusText" class="text-xs text-slate-300 sm:text-sm">Connecting...</span>
                </div>
            </div>

            <div class="flex min-w-0 items-center justify-between gap-3 sm:justify-end">
                <div class="flex min-w-0 items-center gap-3">
                    <label for="profilePhotoInput" aria-label="Upload profile photo" title="Upload profile photo" class="relative grid h-11 w-11 shrink-0 cursor-pointer place-items-center overflow-hidden rounded-full bg-sky-700 text-sm font-semibold text-white ring-2 ring-slate-600">
                        <img id="profilePhoto" alt="Profile photo" class="hidden h-full w-full object-cover">
                        <span id="avatarInitials">US</span>
                        <span aria-hidden="true" class="absolute bottom-0 right-0 grid h-4 w-4 place-items-center rounded-full bg-blue-500 text-xs leading-none text-white">+</span>
                    </label>
                    <input id="profilePhotoInput" type="file" accept="image/*" class="sr-only">
                    <div class="min-w-0">
                        <p class="max-w-[45vw] truncate text-sm font-semibold text-slate-200">Hello, <span id="userName">User</span></p>
                        <p class="max-w-[45vw] truncate text-xs text-slate-400">SAIN <span id="aquariumId">Loading...</span></p>
                    </div>
                </div>

                <div class="flex shrink-0 items-center gap-2">
                    <details class="relative">
                        <summary aria-label="Settings" title="Settings" class="grid h-10 w-10 cursor-pointer list-none place-items-center rounded-lg bg-slate-700 text-slate-200 transition-colors hover:bg-slate-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-400">
                            <i aria-hidden="true" class="fa-solid fa-gear text-lg"></i>
                        </summary>
                        <div class="dashboard-settings-panel absolute right-0 top-10 z-10 w-64 max-w-[calc(100vw-2rem)] rounded-lg border border-slate-700 bg-slate-800 p-4 shadow-lg">
                            <p class="mb-3 text-sm font-medium">Appearance</p>
                            <div class="flex rounded-md bg-slate-900 p-1" role="group" aria-label="Theme">
                                <button id="lightThemeButton" type="button" aria-pressed="false" class="dashboard-theme-button flex-1 rounded px-3 py-1.5 text-sm">Light</button>
                                <button id="darkThemeButton" type="button" aria-pressed="true" class="dashboard-theme-button flex-1 rounded px-3 py-1.5 text-sm">Dark</button>
                            </div>
                            <section class="mt-4 border-t border-slate-700 pt-3">
                                <p class="text-sm font-medium">Aquarium device</p>
                                <p class="mt-1 text-xs text-slate-400">Start pairing on the Aquarium, then enter its one-time code here within five minutes.</p>
                                <p class="mt-2 break-all text-xs text-slate-400">SAIN: <code id="deviceAquariumId">Loading...</code></p>
                                <label for="devicePairingOtp" class="mt-3 block text-xs text-slate-400">8-digit pairing code</label>
                                <input id="devicePairingOtp" type="text" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{8}" maxlength="8" class="mt-1 w-full rounded border border-slate-600 bg-slate-900 px-2 py-2 font-mono text-xs tracking-widest text-white" placeholder="00000000">
                                <button id="pairDeviceButton" type="button" class="mt-3 w-full rounded bg-blue-600 px-3 py-2 text-xs font-semibold text-white hover:bg-blue-500">Pair The Aquarium</button>
                                <p id="deviceTokenStatus" role="status" aria-live="polite" class="mt-2 text-xs text-slate-400"></p>
                            </section>
                            <form method="POST" action="{{ route('vivo_users.logout_all') }}" class="mt-4 border-t border-slate-700 pt-3" onsubmit="return confirm('Log out all devices, including this one? You will need to sign in again.') && clearLocalAuthDataForGlobalLogout()">
                                @csrf
                                <button type="submit" class="w-full text-left text-xs font-medium text-rose-400 hover:text-rose-300">Log out all devices</button>
                            </form>
                        </div>
                    </details>
                    <button onclick="logout()" class="dashboard-logout rounded-lg bg-rose-600 px-3 py-2 text-xs font-semibold text-white transition-colors hover:bg-rose-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-400">Log Out</button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Body -->
    <main class="max-w-7xl mx-auto px-6 py-8 space-y-8">

        <!-- Metric Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="dashboard-surface bg-slate-800 border border-slate-700 p-5 rounded-xl">
                <div class="text-sm text-slate-400">Water Temperature</div>
                <div id="cardTemp" class="text-3xl font-bold mt-2 text-white">-- °C</div>
                <span id="badgeTemp" class="inline-block mt-3 px-2.5 py-0.5 text-xs font-semibold text-slate-400 bg-slate-700 rounded-full">
                    Awaiting data
                </span>
            </div>

            <div class="dashboard-surface bg-slate-800 border border-slate-700 p-5 rounded-xl">
                <div class="text-sm text-slate-400">pH Level</div>
                <div id="cardPh" class="text-3xl font-bold mt-2 text-white">--</div>
                <span id="badgePh" class="inline-block mt-3 px-2.5 py-0.5 text-xs font-semibold text-slate-400 bg-slate-700 rounded-full">
                    Awaiting data
                </span>
            </div>

            <div class="dashboard-surface bg-slate-800 border border-slate-700 p-5 rounded-xl">
                <div class="text-sm text-slate-400">Turbidity (Clarity)</div>
                <div id="cardTurbidity" class="text-3xl font-bold mt-2 text-white">-- NTU</div>
                <span id="badgeTurbidity" class="inline-block mt-3 px-2.5 py-0.5 text-xs font-semibold text-slate-400 bg-slate-700 rounded-full">
                    Awaiting data
                </span>
            </div>

            <div class="dashboard-surface bg-slate-800 border border-slate-700 p-5 rounded-xl">
                <div class="text-sm text-slate-400">Water Level</div>
                <div id="cardLevel" class="text-3xl font-bold mt-2 text-white">-- %</div>
                <span id="badgeLevel" class="inline-block mt-3 px-2.5 py-0.5 text-xs font-semibold text-slate-400 bg-slate-700 rounded-full">
                    Awaiting data
                </span>
            </div>
        </div>

        <!-- Telemetry Graph & Manual Controls -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="dashboard-surface lg:col-span-2 bg-slate-800 border border-slate-700 p-6 rounded-xl">
                <h2 class="text-lg font-semibold mb-4 text-slate-200">Real-Time Telemetry History</h2>
                <canvas id="telemetryChart" class="w-full h-64"></canvas>
            </div>

            <div class="dashboard-surface bg-slate-800 border border-slate-700 p-6 rounded-xl space-y-4">
                <h2 class="text-lg font-semibold text-slate-200">Actuator Commands</h2>
                
                <button onclick="triggerActuator('feeder')" class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-500 font-semibold rounded-lg transition-colors">
                    Trigger Fish Feeder
                </button>
                
                <button onclick="triggerActuator('pump-refill')" class="w-full py-3 px-4 bg-emerald-600 hover:bg-emerald-500 font-semibold rounded-lg transition-colors">
                    Start Water Refill Pump
                </button>
                
                <button onclick="triggerActuator('pump-drain')" class="w-full py-3 px-4 bg-amber-600 hover:bg-amber-500 font-semibold rounded-lg transition-colors">
                    Start Water Drain Pump
                </button>
            </div>
        </div>

    </main>

    <script>
        const themeButtons = {
            light: document.getElementById('lightThemeButton'),
            dark: document.getElementById('darkThemeButton')
        };

        function setTheme(theme) {
            document.documentElement.dataset.theme = theme;
            localStorage.setItem('dashboard_theme', theme);
            themeButtons.light.setAttribute('aria-pressed', String(theme === 'light'));
            themeButtons.dark.setAttribute('aria-pressed', String(theme === 'dark'));
            telemetryChart.options.plugins.legend.labels.color = theme === 'light' ? '#475569' : '#94a3b8';
            telemetryChart.options.scales.x.ticks.color = theme === 'light' ? '#475569' : '#64748b';
            telemetryChart.options.scales.y.ticks.color = theme === 'light' ? '#475569' : '#64748b';
            telemetryChart.options.scales.x.grid.color = theme === 'light' ? '#e2e8f0' : '#334155';
            telemetryChart.options.scales.y.grid.color = theme === 'light' ? '#e2e8f0' : '#334155';
            telemetryChart.update();
        }

        function getAuthData() {
            try {
                return JSON.parse(localStorage.getItem('auth_data') || 'null');
            } catch {
                return null;
            }
        }

        function clearLocalAuthDataForGlobalLogout() {
            localStorage.removeItem('auth_data');
            return true;
        }

        const authData = getAuthData();
        if (!authData?.user) {
            window.location.replace('/login/create');
        }

        window.addEventListener('pageshow', () => {
            if (!getAuthData()?.user) {
                window.location.replace('/login/create');
            }
        });

        const displayName = authData?.user?.username || authData?.user?.email || 'User';
        const avatarStorageKey = `dashboard_avatar_${authData?.user?.id || displayName.toLowerCase()}`;
        document.getElementById('userName').textContent = displayName;

        function setAquariumSain(sain) {
            if (!sain) {
                return;
            }

            const formattedSain = sain.replace(/(\d{5})(?=\d)/g, '$1-');
            document.getElementById('aquariumId').textContent = formattedSain;
            document.getElementById('deviceAquariumId').textContent = formattedSain;
        }

        setAquariumSain(authData?.user?.aquarium?.sain);

        const nameParts = displayName.trim().split(/\s+/);
        document.getElementById('avatarInitials').textContent = (nameParts.length > 1
            ? `${nameParts[0][0]}${nameParts[nameParts.length - 1][0]}`
            : nameParts[0].slice(0, 2)).toUpperCase();

        const profilePhoto = document.getElementById('profilePhoto');
        const avatarInitials = document.getElementById('avatarInitials');
        const savedProfilePhoto = localStorage.getItem(avatarStorageKey);
        if (savedProfilePhoto) {
            profilePhoto.src = savedProfilePhoto;
            profilePhoto.classList.remove('hidden');
            avatarInitials.classList.add('hidden');
        }

        document.getElementById('profilePhotoInput').addEventListener('change', (event) => {
            const photoFile = event.target.files[0];
            if (!photoFile || !photoFile.type.startsWith('image/')) {
                return;
            }

            if (photoFile.size > 8 * 1024 * 1024) {
                alert('Choose an image smaller than 8 MB.');
                event.target.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = () => {
                const image = new Image();
                image.onload = () => {
                    const scale = Math.min(1, 256 / Math.max(image.width, image.height));
                    const canvas = document.createElement('canvas');
                    canvas.width = Math.max(1, Math.round(image.width * scale));
                    canvas.height = Math.max(1, Math.round(image.height * scale));
                    canvas.getContext('2d').drawImage(image, 0, 0, canvas.width, canvas.height);

                    const photoData = canvas.toDataURL('image/jpeg', 0.82);
                    try {
                        localStorage.setItem(avatarStorageKey, photoData);
                        profilePhoto.src = photoData;
                        profilePhoto.classList.remove('hidden');
                        avatarInitials.classList.add('hidden');
                    } catch {
                        alert('This photo could not be saved in this browser.');
                    }
                    event.target.value = '';
                };
                image.src = reader.result;
            };
            reader.readAsDataURL(photoFile);
        });

        // Chart setup
        const ctx = document.getElementById('telemetryChart').getContext('2d');
        const telemetryChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [
                    { label: 'Temperature (°C)', data: [], borderColor: '#38bdf8', tension: 0.3 },
                    { label: 'pH Level', data: [], borderColor: '#34d399', tension: 0.3 },
                    { label: 'Turbidity (NTU)', data: [], borderColor: '#f59e0b', tension: 0.3 }
                ]
            },
            options: {
                responsive: true,
                plugins: { legend: { labels: { color: '#94a3b8' } } },
                scales: {
                    x: { ticks: { color: '#64748b' }, grid: { color: '#334155' } },
                    y: { ticks: { color: '#64748b' }, grid: { color: '#334155' } }
                }
            }
        });

        setTheme(document.documentElement.dataset.theme);
        themeButtons.light.addEventListener('click', () => setTheme('light'));
        themeButtons.dark.addEventListener('click', () => setTheme('dark'));

        const pairDeviceButton = document.getElementById('pairDeviceButton');
        const devicePairingOtp = document.getElementById('devicePairingOtp');
        const deviceTokenStatus = document.getElementById('deviceTokenStatus');

        pairDeviceButton.addEventListener('click', async () => {
            const otp = devicePairingOtp.value.trim();
            if (!/^\d{8}$/.test(otp)) {
                deviceTokenStatus.textContent = 'Enter the 8-digit code shown by the ESP32.';
                return;
            }

            pairDeviceButton.disabled = true;
            deviceTokenStatus.textContent = 'Checking pairing code...';

            try {
                const response = await fetch('/api/aquarium/pair-device', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ otp })
                });

                if (response.status === 401) {
                    logout();
                    return;
                }

                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.errors?.otp?.[0] || data.message || 'Could not pair the ESP32.');
                }

                setAquariumSain(data.sain);
                devicePairingOtp.value = '';
                deviceTokenStatus.textContent = data.message || 'ESP32 paired successfully.';
            } catch (error) {
                deviceTokenStatus.textContent = error.message || 'Could not pair the ESP32.';
            } finally {
                pairDeviceButton.disabled = false;
            }
        });

        // Poll API for telemetry
        async function fetchTelemetry() {
            try {
                const response = await fetch('/api/telemetry/latest', {
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                if (response.status === 401) {
                    logout();
                    return;
                }

                const data = await response.json();
                setAquariumSain(data.sain);
                if (data.current) {
                    updateCards(data.current);
                    updateChart(data.history);
                    
                    document.getElementById('statusPing').className = "w-3 h-3 bg-emerald-500 rounded-full animate-pulse";
                    document.getElementById('statusText').textContent = "ESP32 Active";
                } else {
                    document.getElementById('statusText').textContent = "Waiting for aquarium data";
                }
            } catch (err) {
                document.getElementById('statusPing').className = "w-3 h-3 bg-red-500 rounded-full";
                document.getElementById('statusText').textContent = "Offline / Connection Error";
            }
        }

        function updateCards(current) {
            document.getElementById('cardTemp').textContent = `${current.temperature} °C`;
            document.getElementById('cardPh').textContent = current.ph;
            document.getElementById('cardTurbidity').textContent = `${current.turbidity} NTU`;
            document.getElementById('cardLevel').textContent = `${current.water_level ?? 100}%`;
        }

        function updateChart(history) {
            telemetryChart.data.labels = history.map(item => new Date(item.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }));
            telemetryChart.data.datasets[0].data = history.map(item => item.temperature);
            telemetryChart.data.datasets[1].data = history.map(item => item.ph);
            telemetryChart.data.datasets[2].data = history.map(item => item.turbidity);
            telemetryChart.update();
        }

        async function triggerActuator(endpoint) {
            const response = await fetch(`/api/actuator/${endpoint}`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.status === 401) {
                logout();
                return;
            }

            const data = await response.json();
            alert(data.status || 'Command sent');
        }

        function logout() {
            localStorage.removeItem('auth_data');
            window.location.replace('/login/create');
        }

        // Fetch initially and repeat every 5 seconds
        fetchTelemetry();
        setInterval(fetchTelemetry, 5000);
    </script>
</body>
</html>