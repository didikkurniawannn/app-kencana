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
        $primaryRgb = implode(',', sscanf($primaryColor, "#%02x%02x%02x"));
    @endphp
    <title>{{ $appName }} — Kendali Anggaran Cermat Aman Akuntabel</title>
    <meta name="description" content="KENCANA - Sistem kendali anggaran berbasis AI untuk pemerintah daerah. Monitoring real-time, arsip digital, dan analisis keuangan cerdas.">
    @if($faviconUrl)<link rel="icon" type="image/x-icon" href="{{ $faviconUrl }}">@endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{--primary:{{ $primaryColor }};--primary-rgb:{{ $primaryRgb }};--bg:#f8f9fb;--bg-dark:#0a0a14;--text:#1a1a2e;--text-muted:#64748b;--glass:rgba(255,255,255,0.7);--glass-border:rgba(0,0,0,0.06);}
*{margin:0;padding:0;box-sizing:border-box;}
html{scroll-behavior:smooth;}
body{font-family:'Plus Jakarta Sans',system-ui,sans-serif;background:var(--bg);color:var(--text);overflow-x:hidden;}
h1,h2,h3,h4{font-family:'Outfit',sans-serif;}

/* === NAVBAR === */
.nav{position:fixed;top:0;width:100%;z-index:100;padding:1rem 6%;display:flex;justify-content:space-between;align-items:center;transition:all .4s ease;background:transparent;}
.nav.scrolled{background:rgba(248,249,251,0.85);backdrop-filter:blur(20px);border-bottom:1px solid var(--glass-border);box-shadow:0 1px 20px rgba(0,0,0,0.04);}
.nav-logo{display:flex;align-items:center;gap:10px;text-decoration:none;color:var(--text);font-family:'Outfit',sans-serif;font-weight:700;font-size:1.25rem;}
.nav-logo img{height:36px;width:auto;}
.nav-cta{padding:0.6rem 1.6rem;border-radius:100px;background:var(--text);color:#fff;text-decoration:none;font-weight:600;font-size:0.9rem;transition:all .3s ease;border:none;cursor:pointer;}
.nav-cta:hover{background:var(--primary);transform:translateY(-2px);box-shadow:0 8px 24px rgba(var(--primary-rgb),0.3);}

/* === HERO === */
.hero{position:relative;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:8rem 6% 6rem;overflow:hidden;}
#particleCanvas{position:absolute;top:0;left:0;width:100%;height:100%;z-index:0;pointer-events:none;}
.hero-content{position:relative;z-index:1;max-width:900px;margin:0 auto;}
.hero-badge{display:inline-flex;align-items:center;gap:8px;padding:8px 20px;border-radius:100px;font-size:0.8rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--primary);background:rgba(var(--primary-rgb),0.08);border:1px solid rgba(var(--primary-rgb),0.15);margin-bottom:2rem;animation:fadeInDown .8s ease;}
.hero-badge svg{width:16px;height:16px;}
.hero h1{font-size:clamp(2.8rem,7vw,5.5rem);line-height:1.05;font-weight:800;letter-spacing:-2px;margin-bottom:1.5rem;color:var(--text);animation:fadeInUp 1s ease .15s both;}
.hero-sub{font-size:clamp(1rem,2.5vw,1.35rem);color:var(--text-muted);line-height:1.7;max-width:650px;margin:0 auto 3rem;animation:fadeInUp 1s ease .3s both;}
.hero-actions{display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;animation:fadeInUp 1s ease .5s both;}
.btn-dark{padding:1rem 2.5rem;border-radius:100px;background:var(--text);color:#fff;text-decoration:none;font-weight:700;font-size:1.05rem;display:inline-flex;align-items:center;gap:10px;transition:all .3s ease;border:none;cursor:pointer;}
.btn-dark:hover{background:var(--primary);box-shadow:0 16px 40px rgba(var(--primary-rgb),0.35);transform:translateY(-3px);}
.btn-outline{padding:1rem 2.5rem;border-radius:100px;background:transparent;color:var(--text);text-decoration:none;font-weight:600;font-size:1.05rem;border:1.5px solid rgba(0,0,0,0.12);transition:all .3s ease;cursor:pointer;}
.btn-outline:hover{border-color:var(--primary);color:var(--primary);transform:translateY(-3px);}

/* === AI SHOWCASE (DARK) === */
.ai-section{position:relative;background:var(--bg-dark);padding:8rem 6%;overflow:hidden;}
.ai-glow{position:absolute;width:600px;height:600px;border-radius:50%;filter:blur(120px);opacity:0.25;pointer-events:none;}
.ai-glow-1{top:-200px;left:-100px;background:linear-gradient(135deg,#4285f4,#8b5cf6);}
.ai-glow-2{bottom:-200px;right:-100px;background:linear-gradient(135deg,#d96570,#e8710a);}
.ai-inner{position:relative;z-index:1;max-width:1200px;margin:0 auto;display:grid;grid-template-columns:1fr 1fr;gap:4rem;align-items:center;}
.ai-text{color:#fff;}
.ai-badge{display:inline-flex;align-items:center;gap:8px;padding:6px 16px;border-radius:100px;font-size:0.75rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;background:linear-gradient(135deg,#4285f4,#8b5cf6,#d96570);color:#fff;margin-bottom:2rem;}
.ai-text h2{font-size:clamp(2rem,5vw,3.5rem);line-height:1.1;font-weight:800;letter-spacing:-1px;margin-bottom:1.5rem;background:linear-gradient(135deg,#fff 0%,rgba(255,255,255,0.7) 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
.ai-text p{color:#94a3b8;font-size:1.1rem;line-height:1.7;margin-bottom:2rem;}
.ai-features{display:flex;flex-direction:column;gap:1rem;}
.ai-feat{display:flex;align-items:center;gap:12px;color:#cbd5e1;font-size:0.95rem;font-weight:500;}
.ai-feat-dot{width:8px;height:8px;border-radius:50%;background:linear-gradient(135deg,#4285f4,#8b5cf6);flex-shrink:0;}

/* AI Mockup Card */
.ai-mockup{background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);border-radius:24px;padding:2rem;backdrop-filter:blur(10px);box-shadow:0 0 80px rgba(139,92,246,0.1),0 0 160px rgba(66,133,244,0.05);}
.ai-mock-header{display:flex;align-items:center;gap:10px;margin-bottom:1.5rem;padding-bottom:1rem;border-bottom:1px solid rgba(255,255,255,0.06);}
.ai-mock-avatar{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#4285f4,#8b5cf6);display:flex;align-items:center;justify-content:center;}
.ai-mock-avatar svg{width:20px;height:20px;color:#fff;}
.ai-mock-name{color:#fff;font-weight:700;font-size:0.95rem;}
.ai-mock-tag{color:#8b5cf6;font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:1px;}
.ai-chat-msg{margin-bottom:1.2rem;}
.ai-chat-q{background:rgba(255,255,255,0.06);border-radius:16px 16px 4px 16px;padding:1rem 1.2rem;color:#e2e8f0;font-size:0.9rem;display:inline-block;max-width:85%;margin-bottom:1rem;}
.ai-chat-a{background:linear-gradient(135deg,rgba(66,133,244,0.1),rgba(139,92,246,0.1));border:1px solid rgba(139,92,246,0.15);border-radius:4px 16px 16px 16px;padding:1.2rem;color:#e2e8f0;font-size:0.9rem;line-height:1.6;}
.ai-typing{display:flex;gap:4px;padding:0.5rem 0;}
.ai-typing span{width:6px;height:6px;border-radius:50%;background:#8b5cf6;animation:typingDot 1.4s infinite;}
.ai-typing span:nth-child(2){animation-delay:.2s;}
.ai-typing span:nth-child(3){animation-delay:.4s;}
@keyframes typingDot{0%,60%,100%{opacity:.3;transform:translateY(0)}30%{opacity:1;transform:translateY(-4px)}}

/* === ARC ICONS === */
.arc-section{padding:8rem 6%;text-align:center;}
.arc-icons{display:flex;justify-content:center;gap:0.8rem;flex-wrap:wrap;margin-bottom:4rem;padding:0 2rem;}
.arc-icon{width:60px;height:60px;border-radius:50%;background:rgba(var(--primary-rgb),0.06);border:1px solid rgba(var(--primary-rgb),0.1);display:flex;align-items:center;justify-content:center;transition:all .3s ease;cursor:default;position:relative;}
.arc-icon:hover{background:rgba(var(--primary-rgb),0.15);border-color:rgba(var(--primary-rgb),0.3);transform:translateY(-4px);box-shadow:0 8px 24px rgba(var(--primary-rgb),0.15);}
.arc-icon svg{width:24px;height:24px;color:var(--text);opacity:0.6;}
.arc-icon:hover svg{opacity:1;color:var(--primary);}
.arc-icon .arc-tip{position:absolute;bottom:calc(100% + 10px);left:50%;transform:translateX(-50%);background:var(--text);color:#fff;padding:6px 12px;border-radius:8px;font-size:0.72rem;font-weight:600;white-space:nowrap;opacity:0;pointer-events:none;transition:opacity .2s;}
.arc-icon:hover .arc-tip{opacity:1;}
.arc-statement{max-width:900px;margin:0 auto;}
.arc-statement h2{font-size:clamp(1.8rem,4.5vw,3.2rem);line-height:1.2;font-weight:700;letter-spacing:-1px;color:var(--text);}

/* === FEATURE CATEGORIES === */
.feat-section{padding:4rem 6% 8rem;max-width:1200px;margin:0 auto;}
.feat-row{display:grid;grid-template-columns:1fr 1fr;gap:6rem;align-items:center;margin-bottom:6rem;}
.feat-row.reverse{direction:rtl;}
.feat-row.reverse>*{direction:ltr;}
.feat-card-visual{background:rgba(var(--primary-rgb),0.04);border:1px solid rgba(var(--primary-rgb),0.08);border-radius:24px;padding:2.5rem;position:relative;overflow:hidden;min-height:300px;display:flex;flex-direction:column;justify-content:center;}
.feat-card-visual::before{content:'';position:absolute;top:-50%;right:-50%;width:200%;height:200%;background:radial-gradient(circle,rgba(var(--primary-rgb),0.05) 0%,transparent 60%);pointer-events:none;}
.feat-text h3{font-size:1.8rem;font-weight:700;letter-spacing:-0.5px;margin-bottom:1rem;}
.feat-text p{color:var(--text-muted);line-height:1.7;font-size:1.05rem;margin-bottom:1.5rem;}
.feat-list{list-style:none;display:flex;flex-direction:column;gap:0.6rem;}
.feat-list li{display:flex;align-items:flex-start;gap:10px;color:var(--text-muted);font-size:0.92rem;line-height:1.5;}
.feat-list li svg{width:18px;height:18px;color:var(--primary);flex-shrink:0;margin-top:2px;}
.feat-mini-chart{display:flex;align-items:flex-end;gap:6px;height:80px;padding-top:1rem;}
.feat-mini-bar{width:28px;border-radius:6px 6px 0 0;background:linear-gradient(to top,rgba(var(--primary-rgb),0.3),rgba(var(--primary-rgb),0.7));transition:height .8s ease;}
.feat-icon-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;}
.feat-icon-item{background:rgba(var(--primary-rgb),0.06);border-radius:16px;padding:1.2rem;text-align:center;transition:all .3s ease;}
.feat-icon-item:hover{background:rgba(var(--primary-rgb),0.12);transform:translateY(-3px);}
.feat-icon-item svg{width:28px;height:28px;color:var(--primary);margin-bottom:0.5rem;}
.feat-icon-item span{font-size:0.75rem;font-weight:600;color:var(--text-muted);display:block;}

/* === STATS === */
.stats-section{background:linear-gradient(135deg,rgba(var(--primary-rgb),0.03),rgba(var(--primary-rgb),0.08));padding:6rem 6%;text-align:center;}
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:2rem;max-width:1000px;margin:0 auto;}
.stat-item h3{font-size:clamp(2rem,4vw,3.5rem);font-weight:800;color:var(--primary);letter-spacing:-1px;}
.stat-item p{color:var(--text-muted);font-size:0.9rem;font-weight:500;margin-top:0.3rem;}

/* === FOOTER === */
footer{padding:3rem 6%;text-align:center;border-top:1px solid var(--glass-border);color:var(--text-muted);font-size:0.85rem;}

/* === ANIMATIONS === */
@keyframes fadeInUp{from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)}}
@keyframes fadeInDown{from{opacity:0;transform:translateY(-20px)}to{opacity:1;transform:translateY(0)}}
.reveal{opacity:0;transform:translateY(40px);transition:all .8s cubic-bezier(.16,1,.3,1);}
.reveal.visible{opacity:1;transform:translateY(0);}

/* === RESPONSIVE === */
@media(max-width:900px){
  .ai-inner{grid-template-columns:1fr;gap:3rem;}
  .feat-row,.feat-row.reverse{grid-template-columns:1fr;gap:2rem;direction:ltr;}
  .stats-grid{grid-template-columns:repeat(2,1fr);}
  .arc-icon{width:48px;height:48px;}
  .arc-icon svg{width:20px;height:20px;}
}
@media(max-width:480px){
  .hero h1{letter-spacing:-1px;}
  .hero-actions{flex-direction:column;align-items:center;}
  .stats-grid{grid-template-columns:1fr 1fr;}
}
</style>
</head>
<body>

<!-- NAVBAR -->
<nav class="nav" id="navbar">
    <a href="/" class="nav-logo">
        @if($appLogoUrl)<img src="{{ $appLogoUrl }}" alt="{{ $appName }}">@else<div style="width:32px;height:32px;background:var(--primary);border-radius:10px;"></div>@endif
        <span>{{ $appName }}</span>
    </a>
    <a href="/admin/login" class="nav-cta">Masuk Dashboard</a>
</nav>

<!-- HERO -->
<section class="hero">
    <canvas id="particleCanvas"></canvas>
    <div class="hero-content">
        <div class="hero-badge">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            E-Governance AI Platform
        </div>
        <h1>Kendali Anggaran Cermat, Aman & Akuntabel</h1>
        <p class="hero-sub">Platform cerdas berbasis AI untuk monitoring realisasi anggaran, arsip digital, dan analisis keuangan pemerintah daerah secara real-time.</p>
        <div class="hero-actions">
            <a href="/admin/login" class="btn-dark">
                Dashboard Monitoring
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14m-7-7 7 7-7 7"/></svg>
            </a>
            <a href="#features" class="btn-outline">Jelajahi Fitur</a>
        </div>
    </div>
</section>

<!-- AI SHOWCASE -->
<section class="ai-section" id="ai">
    <div class="ai-glow ai-glow-1"></div>
    <div class="ai-glow ai-glow-2"></div>
    <div class="ai-inner">
        <div class="ai-text reveal">
            <div class="ai-badge">✨ Powered by Gemini AI</div>
            <h2>Analisis Keuangan Berbasis AI</h2>
            <p>Kencana AI membantu Anda memahami kondisi keuangan instansi secara instan. Tanyakan apapun — dari realisasi anggaran hingga prediksi penyerapan.</p>
            <div class="ai-features">
                <div class="ai-feat"><div class="ai-feat-dot"></div>Analisis tren penyerapan anggaran otomatis</div>
                <div class="ai-feat"><div class="ai-feat-dot"></div>Identifikasi kegiatan over/under budget</div>
                <div class="ai-feat"><div class="ai-feat-dot"></div>Rekomendasi optimasi alokasi dana</div>
                <div class="ai-feat"><div class="ai-feat-dot"></div>Jawaban natural language, bukan angka mentah</div>
            </div>
        </div>
        <div class="ai-mockup reveal">
            <div class="ai-mock-header">
                <div class="ai-mock-avatar"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2 L15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26Z"/></svg></div>
                <div><div class="ai-mock-name">Kencana AI</div><div class="ai-mock-tag">Financial Assistant</div></div>
            </div>
            <div class="ai-chat-msg">
                <div class="ai-chat-q">Berapa total realisasi belanja bulan ini dibanding bulan lalu?</div>
                <div class="ai-chat-a">
                    📊 <strong>Realisasi Maret 2026:</strong> Rp 847.500.000 (naik 12.3% dari Februari).<br><br>
                    Penyerapan tertinggi ada di <strong>kegiatan infrastruktur</strong> (68%), sementara belanja operasional masih di 42%. Saya rekomendasikan percepatan realisasi di Triwulan II.
                </div>
            </div>
            <div class="ai-typing"><span></span><span></span><span></span></div>
        </div>
    </div>
</section>

<!-- ARC ICONS + STATEMENT -->
<section class="arc-section" id="features">
    <div class="reveal">
        <div class="arc-icons">
            <div class="arc-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg><span class="arc-tip">Autentikasi</span></div>
            <div class="arc-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg><span class="arc-tip">Hak Akses</span></div>
            <div class="arc-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg><span class="arc-tip">Anggaran</span></div>
            <div class="arc-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="m9 15 2 2 4-4"/></svg><span class="arc-tip">Verifikasi</span></div>
            <div class="arc-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg><span class="arc-tip">Upload Eviden</span></div>
            <div class="arc-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg><span class="arc-tip">Persetujuan</span></div>
            <div class="arc-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg><span class="arc-tip">Pembukuan</span></div>
            <div class="arc-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg><span class="arc-tip">Arsip Digital</span></div>
            <div class="arc-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg><span class="arc-tip">Pencarian</span></div>
            <div class="arc-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg><span class="arc-tip">Monitoring</span></div>
            <div class="arc-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg><span class="arc-tip">Import/Export</span></div>
            <div class="arc-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg><span class="arc-tip">Notifikasi</span></div>
            <div class="arc-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg><span class="arc-tip">Audit Trail</span></div>
        </div>
    </div>
    <div class="arc-statement reveal">
        <h2><strong>KENCANA</strong> — satu platform cerdas untuk seluruh kendali anggaran daerah.</h2>
    </div>
</section>

<!-- FEATURE CATEGORIES -->
<section class="feat-section">
    <!-- 1. Manajemen Anggaran -->
    <div class="feat-row reveal">
        <div class="feat-text">
            <h3>Manajemen Anggaran Terpadu</h3>
            <p>Kelola seluruh siklus anggaran dari DPA hingga realisasi dengan validasi otomatis dan dukungan multi-sumber dana.</p>
            <ul class="feat-list">
                <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>Input realisasi anggaran sistematis</li>
                <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>Pencatatan sumber dana (GU, LS, dll.)</li>
                <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>Validasi kesesuaian sumber dana</li>
                <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>DPA perubahan tanpa ganggu realisasi</li>
            </ul>
        </div>
        <div class="feat-card-visual">
            <div class="feat-mini-chart" id="chartAnim">
                <div class="feat-mini-bar" style="height:35%"></div>
                <div class="feat-mini-bar" style="height:55%"></div>
                <div class="feat-mini-bar" style="height:42%"></div>
                <div class="feat-mini-bar" style="height:78%"></div>
                <div class="feat-mini-bar" style="height:65%"></div>
                <div class="feat-mini-bar" style="height:90%"></div>
                <div class="feat-mini-bar" style="height:72%"></div>
                <div class="feat-mini-bar" style="height:85%"></div>
            </div>
            <p style="text-align:center;margin-top:1.5rem;color:var(--text-muted);font-size:0.85rem;font-weight:600;">Visualisasi Penyerapan Anggaran Real-time</p>
        </div>
    </div>

    <!-- 2. Verifikasi & Persetujuan -->
    <div class="feat-row reverse reveal">
        <div class="feat-text">
            <h3>Verifikasi & Persetujuan Berjenjang</h3>
            <p>Proses approval yang transparan sesuai SOP, dari upload eviden hingga persetujuan akhir pejabat berwenang.</p>
            <ul class="feat-list">
                <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>Unggah dokumen bukti kegiatan digital</li>
                <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>Verifikasi bertahap sesuai SOP</li>
                <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>Persetujuan akhir pejabat berwenang</li>
                <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>Tracking status dokumen real-time</li>
            </ul>
        </div>
        <div class="feat-card-visual" style="align-items:center;">
            <div style="display:flex;flex-direction:column;gap:1rem;width:100%;max-width:280px;">
                <div style="display:flex;align-items:center;gap:12px;background:rgba(var(--primary-rgb),0.08);padding:1rem;border-radius:14px;">
                    <div style="width:36px;height:36px;border-radius:50%;background:rgba(16,185,129,0.15);display:flex;align-items:center;justify-content:center;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg></div>
                    <div><div style="font-weight:700;font-size:0.85rem;">Upload Eviden</div><div style="font-size:0.75rem;color:var(--text-muted);">Selesai</div></div>
                </div>
                <div style="display:flex;align-items:center;gap:12px;background:rgba(var(--primary-rgb),0.08);padding:1rem;border-radius:14px;">
                    <div style="width:36px;height:36px;border-radius:50%;background:rgba(16,185,129,0.15);display:flex;align-items:center;justify-content:center;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg></div>
                    <div><div style="font-weight:700;font-size:0.85rem;">Verifikasi PPTK</div><div style="font-size:0.75rem;color:var(--text-muted);">Selesai</div></div>
                </div>
                <div style="display:flex;align-items:center;gap:12px;background:rgba(59,130,246,0.08);padding:1rem;border-radius:14px;border:2px solid rgba(59,130,246,0.2);">
                    <div style="width:36px;height:36px;border-radius:50%;background:rgba(59,130,246,0.15);display:flex;align-items:center;justify-content:center;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg></div>
                    <div><div style="font-weight:700;font-size:0.85rem;">Persetujuan Camat</div><div style="font-size:0.75rem;color:#3b82f6;font-weight:600;">Menunggu...</div></div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Arsip & Import/Export -->
    <div class="feat-row reveal">
        <div class="feat-text">
            <h3>Arsip Digital & Manajemen Data</h3>
            <p>Pengarsipan terstruktur sesuai standar ANRI dengan kemampuan import/export untuk kebutuhan pelaporan dan pemeriksaan BPK.</p>
            <ul class="feat-list">
                <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>Penyimpanan dokumen aman & terstruktur</li>
                <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>Nomor register & indeks arsip otomatis</li>
                <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>Import DPA & realisasi dari file eksternal</li>
                <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>Export laporan siap audit BPK</li>
            </ul>
        </div>
        <div class="feat-card-visual">
            <div class="feat-icon-grid">
                <div class="feat-icon-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg><span>Arsip</span></div>
                <div class="feat-icon-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg><span>Import</span></div>
                <div class="feat-icon-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg><span>Export</span></div>
                <div class="feat-icon-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg><span>Cari</span></div>
                <div class="feat-icon-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg><span>Laporan</span></div>
                <div class="feat-icon-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg><span>Audit</span></div>
            </div>
        </div>
    </div>
</section>

<!-- STATS -->
<section class="stats-section reveal">
    <div class="stats-grid">
        <div class="stat-item"><h3>23</h3><p>Kebutuhan Fungsional</p></div>
        <div class="stat-item"><h3>6</h3><p>Modul Terintegrasi</p></div>
        <div class="stat-item"><h3>AI</h3><p>Analisis Cerdas</p></div>
        <div class="stat-item"><h3>100%</h3><p>Siap Audit BPK</p></div>
    </div>
</section>

<!-- FOOTER -->
<footer>
    <p>&copy; {{ date('Y') }} {{ $appName }}. PEMERINTAH KABUPATEN BANDUNG.</p>
    <p style="margin-top:0.5rem;font-size:0.78rem;color:#94a3b8;">Developed by <strong style="color:var(--text-muted);">DIDIK KURNIAWAN</strong> — KEC. CANGKUANG KAB. BANDUNG</p>
</footer>

<script>
// Particle Canvas Interactive
(function(){
    const c = document.getElementById('particleCanvas');
    const ctx = c.getContext('2d');
    let w, h, particles = [];
    let mouse = { x: null, y: null, radius: 150 };

    function resize() {
        w = c.width = window.innerWidth;
        h = c.height = window.innerHeight;
    }

    window.addEventListener('resize', resize);
    window.addEventListener('mousemove', function(e) {
        mouse.x = e.x;
        mouse.y = e.y;
    });

    window.addEventListener('mouseout', function() {
        mouse.x = null;
        mouse.y = null;
    });

    resize();

    const colors = [
        'rgba({{ $primaryRgb }}, 0.4)',
        'rgba(59, 130, 246, 0.3)',
        'rgba(139, 92, 246, 0.3)',
        'rgba(217, 101, 112, 0.25)'
    ];

    class Particle {
        constructor() {
            this.init();
        }

        init() {
            this.x = Math.random() * w;
            this.y = Math.random() * h;
            this.r = Math.random() * 6 + 2; 
            this.dx = (Math.random() - 0.5) * 2; // Gerakan lebih lambat agar elegan
            this.dy = (Math.random() - 0.5) * 2;
            this.color = colors[Math.floor(Math.random() * colors.length)];
            this.density = (Math.random() * 60) + 20; 
            // Jarak target diperlebar maksimal (100px - 1000px)
            this.targetRadius = Math.random() * 900 + 100; 
            this.jitter = Math.random() * 0.4;
        }

        update() {
            // Mouse interaction (Ultra-Wide Scattered Cloud)
            if (mouse.x != null && mouse.y != null) {
                let dx = mouse.x - this.x;
                let dy = mouse.y - this.y;
                let distance = Math.sqrt(dx * dx + dy * dy);
                
                // Logika penyebaran luas
                if (distance > this.targetRadius + 50) {
                    let force = (distance / 5000); 
                    this.dx += (dx / distance) * force * (1.1 + this.jitter); 
                    this.dy += (dy / distance) * force * (1.1 + this.jitter);
                } else if (distance < this.targetRadius - 50) {
                    // Gaya tolak lebih kuat agar tetap renggang
                    let force = (this.targetRadius - distance) / 100;
                    this.dx -= (dx / distance) * force * 2.5; 
                    this.dy -= (dy / distance) * force * 2.5;
                }
                
                // Drifting alami
                this.dx += (Math.random() - 0.5) * 0.1;
                this.dy += (Math.random() - 0.5) * 0.1;

                this.dx *= 0.98; // Sangat smooth
                this.dy *= 0.98;
            } else {
                this.dx *= 0.998;
                this.dy *= 0.998;
            }

            // Apply velocity
            this.x += this.dx;
            this.y += this.dy;

            // Bounce off walls
            if (this.x < 0 || this.x > w) this.dx *= -0.9;
            if (this.y < 0 || this.y > h) this.dy *= -0.9;
        }

        draw() {
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.r, 0, Math.PI * 2);
            ctx.fillStyle = this.color;
            ctx.fill();
        }
    }

    function init() {
        particles = [];
        let numberOfParticles = (w * h) / 15000; // Density based on screen
        if (numberOfParticles > 100) numberOfParticles = 100;
        for (let i = 0; i < numberOfParticles; i++) {
            particles.push(new Particle());
        }
    }

    function animate() {
        ctx.clearRect(0, 0, w, h);
        for (let i = 0; i < particles.length; i++) {
            particles[i].update();
            particles[i].draw();
        }
        requestAnimationFrame(animate);
    }

    init();
    animate();
})();

// Navbar scroll
window.addEventListener('scroll', () => {
    document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 50);
});

// Scroll reveal
const obs = new IntersectionObserver((entries) => {
    entries.forEach(e => {
        if (e.isIntersecting) {
            e.target.classList.add('visible');
            obs.unobserve(e.target);
        }
    });
}, { threshold: 0.15 });
document.querySelectorAll('.reveal').forEach(el => obs.observe(el));
</script>
</body>
</html>