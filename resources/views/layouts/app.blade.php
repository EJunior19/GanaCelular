<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <!-- 📱 MOBILE -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Gana tu Celular Py')</title>

    <!-- 🎨 ESTILO -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- 📲 PWA -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#39FF14">
    <link rel="apple-touch-icon" href="/logo.png">

    <style>
        :root {
            --neon: #39FF14;
            --gold: #FFD700;
            --bg: #000000;
            --bg2: #0a0a0a;
            --bg3: #111111;
        }

        body { background: var(--bg); }

        .neon-border {
            border: 1px solid var(--neon);
            box-shadow: 0 0 8px var(--neon), inset 0 0 4px rgba(57,255,20,0.05);
        }

        .neon-glow {
            box-shadow: 0 0 12px var(--neon), 0 0 24px rgba(57,255,20,0.3);
        }

        .neon-text {
            color: var(--neon);
            text-shadow: 0 0 8px var(--neon), 0 0 16px rgba(57,255,20,0.5);
        }

        .gold-text {
            color: var(--gold);
            text-shadow: 0 0 8px var(--gold), 0 0 16px rgba(255,215,0,0.4);
        }

        .btn-neon {
            background: var(--neon);
            color: #000;
            font-weight: 900;
            transition: all 0.2s;
        }

        .btn-neon:hover {
            box-shadow: 0 0 16px var(--neon), 0 0 32px rgba(57,255,20,0.4);
            transform: scale(1.02);
        }

        .card-dark {
            background: var(--bg3);
            border: 1px solid rgba(57,255,20,0.25);
            box-shadow: 0 0 8px rgba(57,255,20,0.08);
        }
    </style>
</head>

<body class="bg-black text-white min-h-screen">

    <!-- 🔥 CONTENEDOR APP -->
    <div class="max-w-md mx-auto relative min-h-screen">

        <!-- 🔥 HEADER -->
        <div class="bg-black border-b border-[#39FF14]/30 p-4 flex justify-between items-center sticky top-0 z-50"
             style="box-shadow: 0 2px 12px rgba(57,255,20,0.15);">

            <a href="/" class="text-lg font-black neon-text">
                📱 GanaCelularPY
            </a>

            <div class="flex gap-3 text-sm">
                <a href="/" class="text-[#39FF14] hover:text-white transition font-semibold">Inicio</a>
                <a href="/admin" class="text-[#39FF14] hover:text-white transition font-semibold">Admin</a>
            </div>

        </div>

        <!-- 📦 CONTENIDO -->
        <div class="p-4 pb-24">
            @yield('content')
        </div>

    </div>

    <!-- 📲 BOTÓN INSTALAR APP -->
    <button id="installBtn"
        class="fixed bottom-4 right-4 btn-neon px-4 py-3 rounded-full shadow-lg hidden hover:scale-105 transition z-50 neon-glow">
        📲 Instalar
    </button>

    <!-- ⚙️ SCRIPTS -->
    <script>

        // 🔥 SERVICE WORKER
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js')
                .then(() => console.log('Service Worker registrado'))
                .catch(err => console.log('SW error', err));
        }

        // 📲 INSTALAR APP
        let deferredPrompt;

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            document.getElementById('installBtn').classList.remove('hidden');
        });

        document.getElementById('installBtn').addEventListener('click', async () => {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt = null;
            }
        });

    </script>

</body>

</html>
