<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $primaryColor = \App\Models\Setting::get('primary_color', '#14797b');
        $appName = \App\Models\Setting::get('app_name', 'Control Anggaran Kecamatan');
        $appLogo = \App\Models\Setting::get('app_logo');
        $appLogoUrl = $appLogo ? \Illuminate\Support\Facades\Storage::url($appLogo) : null;
        $favicon = \App\Models\Setting::get('app_favicon');
        $faviconUrl = $favicon ? \Illuminate\Support\Facades\Storage::url($favicon) : null;
    @endphp

    <title>{{ $appName }} | Kecamatan Cangkuang</title>

    @if($faviconUrl)
        <link rel="icon" type="image/x-icon" href="{{ $faviconUrl }}">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@400;600;800&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --primary:
                {{ $primaryColor }}
            ;
            --primary-rgb:
                {{ implode(',', sscanf($primaryColor, "#%02x%02x%02x")) }}
            ;
            --bg-dark: #0f172a;
            --glass: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background-color: var(--bg-dark);
            color: white;
            min-height: 100vh;
            overflow-x: hidden;
            background-image:
                radial-gradient(circle at 0% 0%, rgba(var(--primary-rgb), 0.15) 0%, transparent 50%),
                radial-gradient(circle at 100% 100%, rgba(var(--primary-rgb), 0.1) 0%, transparent 50%);
        }

        .navbar {
            padding: 1.5rem 8%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--glass-border);
            background: rgba(15, 23, 42, 0.8);
        }

        .logo {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 1.5rem;
            color: white;
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            letter-spacing: -0.5px;
        }

        .logo img {
            height: 40px;
            width: auto;
        }

        .hero {
            padding: 12rem 8% 6rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            position: relative;
        }

        .badge {
            background: rgba(var(--primary-rgb), 0.1);
            color: var(--primary);
            padding: 8px 20px;
            border-radius: 100px;
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 2rem;
            border: 1px solid rgba(var(--primary-rgb), 0.2);
            animation: fadeInDown 0.8s ease-out;
        }

        h1 {
            font-family: 'Outfit', sans-serif;
            font-size: clamp(2.5rem, 8vw, 5rem);
            line-height: 1.1;
            font-weight: 800;
            margin-bottom: 1.5rem;
            max-width: 1000px;
            background: linear-gradient(135deg, #fff 0%, rgba(255, 255, 255, 0.7) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: fadeInUp 1s ease-out 0.2s both;
        }

        .tagline {
            font-size: clamp(1.1rem, 3vw, 1.35rem);
            line-height: 1.6;
            color: #94a3b8;
            margin-bottom: 3rem;
            max-width: 700px;
            animation: fadeInUp 1s ease-out 0.4s both;
        }

        .cta-container {
            display: flex;
            gap: 1.5rem;
            animation: fadeInUp 1s ease-out 0.6s both;
        }

        .btn {
            padding: 1rem 2.5rem;
            border-radius: 12px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            font-size: 1.1rem;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            box-shadow: 0 20px 40px -10px rgba(var(--primary-rgb), 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-5px);
            box-shadow: 0 30px 60px -10px rgba(var(--primary-rgb), 0.6);
            filter: brightness(1.1);
        }

        .btn-secondary {
            background: var(--glass);
            color: white;
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(10px);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-5px);
        }

        .features {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
            padding: 4rem 8% 8rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        @media (max-width: 768px) {
            .features {
                grid-template-columns: 1fr;
            }
        }

        .feature-card {
            background: var(--glass);
            padding: 3rem 2rem;
            border-radius: 24px;
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(10px);
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(800px circle at var(--mouse-x) var(--mouse-y), rgba(255, 255, 255, 0.06), transparent 40%);
            opacity: 0;
            transition: opacity 0.5s;
        }

        .feature-card:hover::before {
            opacity: 1;
        }

        .feature-card:hover {
            border-color: rgba(var(--primary-rgb), 0.3);
            transform: scale(1.02);
        }

        .feature-icon {
            width: 56px;
            height: 56px;
            background: rgba(var(--primary-rgb), 0.1);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 2rem;
            color: var(--primary);
            border: 1px solid rgba(var(--primary-rgb), 0.2);
        }

        .feature-card h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            font-family: 'Outfit', sans-serif;
        }

        .feature-card p {
            color: #94a3b8;
            line-height: 1.6;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        footer {
            padding: 4rem 8%;
            text-align: center;
            color: #475569;
            font-size: 0.95rem;
            border-top: 1px solid var(--glass-border);
        }

        /* Ambient Orbs */
        .orb {
            position: absolute;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            filter: blur(100px);
            z-index: -1;
            opacity: 0.3;
            pointer-events: none;
        }

        .orb-1 {
            top: 10%;
            left: -100px;
            background: var(--primary);
            animation: float 20s infinite alternate;
        }

        .orb-2 {
            bottom: 10%;
            right: -100px;
            background: #3b82f6;
            animation: float 15s infinite alternate-reverse;
        }

        /* Floating Animation */
        @keyframes float-slow {

            0%,
            100% {
                transform: translateY(0) rotate(0deg);
            }

            50% {
                transform: translateY(-20px) rotate(2deg);
            }
        }

        @keyframes float-medium {

            0%,
            100% {
                transform: translateY(0) rotate(0deg);
            }

            50% {
                transform: translateY(-15px) rotate(-2deg);
            }
        }

        @keyframes pulse-glow {

            0%,
            100% {
                box-shadow: 0 0 20px rgba(var(--primary-rgb), 0.2);
            }

            50% {
                box-shadow: 0 0 40px rgba(var(--primary-rgb), 0.4);
            }
        }

        .hero-visual {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            pointer-events: none;
            z-index: -1;
            overflow: hidden;
        }

        .floating-card {
            position: absolute;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            animation: float-slow 6s ease-in-out infinite;
        }

        .card-1 {
            top: 20%;
            right: 15%;
            width: 220px;
            animation-delay: 0s;
        }

        .card-2 {
            top: 50%;
            right: 10%;
            width: 180px;
            animation-delay: 2s;
            animation-name: float-medium;
        }

        .card-3 {
            top: 30%;
            left: 10%;
            width: 200px;
            animation-delay: 1s;
        }

        .chart-bar {
            height: 8px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            margin-top: 8px;
            position: relative;
            overflow: hidden;
        }

        .chart-fill {
            height: 100%;
            background: var(--primary);
            border-radius: 4px;
            width: 0%;
            animation: fillBar 1.5s ease-out forwards;
        }

        @keyframes fillBar {
            to {
                width: var(--w);
            }
        }

        @media (max-width: 900px) {
            .hero-visual {
                display: none;
            }
        }

        /* Update existing hero styles */
        .hero {
            padding: 14rem 8% 8rem;
        }

        .badge {
            margin-bottom: 1.5rem;
            background: rgba(var(--primary-rgb), 0.15);
            border: 1px solid rgba(var(--primary-rgb), 0.3);
            color: #fff;
            text-shadow: 0 2px 10px rgba(var(--primary-rgb), 0.5);
        }
    </style>
</head>

<body>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>

    <nav class="navbar">
        <a href="/" class="logo">
            @if($appLogoUrl)
                <img src="{{ $appLogoUrl }}" alt="{{ $appName }}">
            @else
                <div style="width: 32px; height: 32px; background: var(--primary); border-radius: 8px;"></div>
            @endif
            <span>{{ $appName }}</span>
        </a>
        <a href="/admin/login" class="btn btn-secondary"
            style="padding: 0.6rem 1.5rem; font-size: 1rem; border-radius: 10px;">
            Masuk
        </a>
    </nav>

    <main class="hero">
        <div class="hero-visual">
            <!-- Floating Finance Elements -->
            <div class="floating-card card-1">
                <div style="background:rgba(var(--primary-rgb),0.2); padding:10px; border-radius:10px;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                    </svg>
                </div>
                <div>
                    <div style="height:6px; width:60px; background:rgba(255,255,255,0.2); border-radius:3px;"></div>
                    <div class="chart-bar" style="width:100px;">
                        <div class="chart-fill" style="--w: 75%"></div>
                    </div>
                </div>
            </div>

            <div class="floating-card card-2">
                <div style="background:rgba(16, 185, 129, 0.2); padding:10px; border-radius:10px;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#34d399" stroke-width="2">
                        <polyline points="22 7 13.5 15.5 8.5 10.5 2 17" />
                        <polyline points="16 7 22 7 22 13" />
                    </svg>
                </div>
                <div>
                    <div style="height:6px; width:40px; background:rgba(255,255,255,0.2); border-radius:3px;"></div>
                    <div style="font-size:0.8rem; color:#34d399; font-weight:bold; margin-top:4px;">+24%</div>
                </div>
            </div>

            <div class="floating-card card-3">
                <div style="background:rgba(59, 130, 246, 0.2); padding:10px; border-radius:10px;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#60a5fa" stroke-width="2">
                        <rect x="2" y="5" width="20" height="14" rx="2" />
                        <line x1="2" y1="10" x2="22" y2="10" />
                    </svg>
                </div>
                <div>
                    <div style="height:6px; width:80px; background:rgba(255,255,255,0.2); border-radius:3px;"></div>
                    <div class="chart-bar" style="width:120px;">
                        <div class="chart-fill" style="--w: 60%; background:#60a5fa;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="badge">E-Governance Standard</div>
        <h1>KENCANA</h1>
        <p
            style="font-size: 1.2rem; font-weight: 500; color: #cbd5e1; margin-top: -10px; margin-bottom: 2rem; letter-spacing: 0.5px;">
            Kendali Anggaran Cermat Aman Akuntabel
        </p>
        <p class="tagline">Pantau setiap rupiah untuk pembangunan yang berkelanjutan.</p>

        <div class="cta-container">
            <a href="/admin/login" class="btn btn-primary">
                Dashboard Monitoring
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14m-7-7 7 7-7 7" />
                </svg>
            </a>
            <a href="#features" class="btn btn-secondary">Pelajari Sistem</a>
        </div>
    </main>

    <section id="features" class="features">
        <div class="feature-card">
            <div class="feature-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 3v18h18" />
                    <path d="m19 9-5 5-4-4-3 3" />
                </svg>
            </div>
            <h3>Monitoring Real-time</h3>
            <p>Pantau realisasi setiap detail belanja secara instan dengan dashboard interaktif yang mendalam.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <rect width="18" height="18" x="3" y="3" rx="2" ry="2" />
                    <line x1="12" x2="12" y1="3" y2="21" />
                    <line x1="3" x2="21" y1="12" y2="12" />
                </svg>
            </div>
            <h3>Manajemen RKA</h3>
            <p>Kelola struktur anggaran tahunan melalui sistem import yang cerdas dan mendukung revisi parsial.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                </svg>
            </div>
            <h3>Arsip Digital AMRI</h3>
            <p>Penyimpanan bukti pengeluaran yang terorganisir sesuai standar kearsipan nasional (ANRI).</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                    <polyline points="14 2 14 8 20 8" />
                    <path d="m9 15 2 2 4-4" />
                </svg>
            </div>
            <h3>Bebas Cemas Audit BPK</h3>
            <p>Hadapi pemeriksaan BPK dengan tenang. Data terstruktur, transparan, dan dapat dipertanggungjawabkan
                sepenuhnya.</p>
        </div>
    </section>

    <footer>
        <p>&copy; {{ date('Y') }} {{ $appName }}. PEMERINTAH KABUPATEN BANDUNG.</p>
    </footer>

    <script>
        // Feature Card Mouse Follow Effect
        document.querySelectorAll('.feature-card').forEach(card => {
            card.addEventListener('mousemove', e => {
                const rect = card.getBoundingClientRect();
                card.style.setProperty('--mouse-x', `${e.clientX - rect.left}px`);
                card.style.setProperty('--mouse-y', `${e.clientY - rect.top}px`);
            });
        });
    </script>
</body>

</html>