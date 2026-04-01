<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        // Try to fetch settings, fallback if DB not ready
        $settings = [];
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                $settings = \App\Models\Setting::all()->pluck('value', 'key')->toArray();
            }
        } catch (\Exception $e) {
            // Table might not exist yet during migration
        }

        $appName = $settings['app_name'] ?? config('app.name');
        $primaryColor = $settings['primary_color'] ?? '#14797b';
        $logo = isset($settings['app_logo']) ? \Illuminate\Support\Facades\Storage::disk('public')->url($settings['app_logo']) : null;
        $favicon = isset($settings['app_favicon']) ? \Illuminate\Support\Facades\Storage::disk('public')->url($settings['app_favicon']) : null;

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->spa()
            ->databaseNotifications()
            ->login(\App\Filament\Pages\Auth\CustomLogin::class)
            ->userMenuItems([
                'profile' => \Filament\Navigation\MenuItem::make()
                    ->label('Profile')
                    ->icon('heroicon-m-user-circle')
                    ->url(fn(): string => filament()->getTenant() ? \App\Filament\Resources\UserResource::getUrl('edit', ['record' => auth()->user(), 'tenant' => filament()->getTenant()]) : '#'),
            ])
            ->passwordReset()
            ->tenant(\App\Models\Instansi::class, slugAttribute: 'slug', ownershipRelationship: 'instansi')
            ->brandName(fn() => \App\Models\Setting::get('app_name', 'Kencana'))
            ->brandLogo(fn() => request()->routeIs('filament.admin.auth.*') || !filament()->auth()->check() ? asset('images/logo-kencana.png') : $logo)
            ->brandLogoHeight(fn() => request()->routeIs('filament.admin.auth.*') || !filament()->auth()->check() ? '7rem' : '2.5rem')
            ->favicon(fn() => ($favicon = \App\Models\Setting::get('app_favicon')) ? \Illuminate\Support\Facades\Storage::disk('public')->url($favicon) : null)
            ->colors([
                'primary' => $primaryColor,
            ])
            ->font('Inter')
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth('full')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                \App\Filament\Pages\CustomDashboard::class,
            ])
            ->navigationGroups([
                \Filament\Navigation\NavigationGroup::make('Transaksi'),
                \Filament\Navigation\NavigationGroup::make('Laporan'),
                \Filament\Navigation\NavigationGroup::make('Master Data'),
                \Filament\Navigation\NavigationGroup::make('Pengaturan'),
            ])
            ->navigationItems([
                \Filament\Navigation\NavigationItem::make('Master RKA')
                    ->visible(fn (): bool => auth()->user()->can('view_any_program'))
                    ->url(fn (): string => \App\Filament\Resources\ProgramResource::getUrl())
                    ->isActiveWhen(fn (): bool => request()->routeIs(
                        'filament.admin.resources.programs.*',
                        'filament.admin.resources.kegiatans.*',
                        'filament.admin.resources.sub-kegiatans.*',
                        'filament.admin.resources.detail-belanjas.*'
                    ))
                    ->icon('heroicon-o-folder-open')
                    ->group('Master Data')
                    ->sort(1),
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
            ])
            ->plugins([
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make()
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->renderHook(
                \Filament\View\PanelsRenderHook::GLOBAL_SEARCH_BEFORE,
                fn(): string => \Illuminate\Support\Facades\Blade::render('
                    <div class="flex items-center gap-x-3">
                        @livewire(\'pending-task-navbar\')
                        @livewire(\'year-switcher\')
                    </div>
                ')
            )
            ->renderHook(
                \Filament\View\PanelsRenderHook::STYLES_AFTER,
                fn(): string => '
                    <style>
                        /* ============================================
                           YEAR SWITCHER STYLES
                           ============================================ */
                        .year-switcher-wrapper {
                            position: relative;
                            display: inline-flex;
                            align-items: center;
                            margin-right: 0.5rem;
                        }
                        .year-switcher-btn {
                            display: inline-flex;
                            align-items: center;
                            gap: 0.4rem;
                            padding: 0.45rem 0.85rem;
                            border-radius: 10px;
                            font-size: 0.82rem;
                            font-weight: 600;
                            color: #1e293b;
                            background: linear-gradient(135deg, #f0fdfa 0%, #e0f2fe 100%);
                            border: 1.5px solid #99f6e4;
                            cursor: pointer;
                            transition: all 0.2s ease;
                            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
                            white-space: nowrap;
                        }
                        .year-switcher-btn:hover {
                            background: linear-gradient(135deg, #ccfbf1 0%, #bae6fd 100%);
                            border-color: #5eead4;
                            box-shadow: 0 2px 8px rgba(20, 184, 166, 0.15);
                        }
                        .year-switcher-icon { color: #14b8a6; flex-shrink: 0; }
                        .year-switcher-label { line-height: 1; }
                        .year-switcher-chevron {
                            color: #94a3b8;
                            transition: transform 0.2s ease;
                            flex-shrink: 0;
                        }
                        .year-switcher-chevron.rotated { transform: rotate(180deg); }
                        .year-switcher-dropdown {
                            position: absolute;
                            top: calc(100% + 6px);
                            right: 0;
                            min-width: 160px;
                            background: #ffffff;
                            border: 1px solid #e2e8f0;
                            border-radius: 12px;
                            box-shadow: 0 10px 40px rgba(0,0,0,0.1), 0 2px 8px rgba(0,0,0,0.04);
                            overflow: hidden;
                            z-index: 50;
                        }
                        .year-switcher-dropdown-header {
                            padding: 0.55rem 0.85rem;
                            font-size: 0.7rem;
                            font-weight: 600;
                            text-transform: uppercase;
                            letter-spacing: 0.05em;
                            color: #94a3b8;
                            border-bottom: 1px solid #f1f5f9;
                        }
                        .year-switcher-option {
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            width: 100%;
                            padding: 0.55rem 0.85rem;
                            font-size: 0.82rem;
                            font-weight: 500;
                            color: #334155;
                            background: none;
                            border: none;
                            cursor: pointer;
                            transition: all 0.15s ease;
                            text-align: left;
                        }
                        .year-switcher-option:hover {
                            background: #f0fdfa;
                            color: #0f766e;
                        }
                        .year-switcher-option.active {
                            background: #f0fdfa;
                            color: #0f766e;
                            font-weight: 600;
                        }
                        .year-check-icon { color: #14b8a6; }

                        /* ============================================
                           KHUSUS HALAMAN LOGIN (fi-simple-layout)
                           Background dan overlay HANYA di halaman login
                           ============================================ */
                        .fi-simple-layout {
                            background-image: url("/images/login-bg.png") !important;
                            background-size: cover !important;
                            background-position: center bottom !important;
                            background-attachment: fixed !important;
                            position: relative;
                        }
                        
                        /* Overlay terang elegan hanya di login */
                        .fi-simple-layout::before {
                            content: "";
                            position: fixed;
                            top: 0; right: 0; bottom: 0; left: 0;
                            background: rgba(255, 255, 255, 0.35);
                            backdrop-filter: blur(6px);
                            z-index: 0;
                        }
                        
                        /* Pastikan konten login di atas overlay */
                        .fi-simple-layout > * {
                            position: relative;
                            z-index: 1;
                        }

                        /* Container Form Login: Glassmorphism putih elegan */
                        .fi-simple-main {
                            background: rgba(255, 255, 255, 0.92) !important;
                            backdrop-filter: blur(20px) !important;
                            border: 1px solid rgba(255, 255, 255, 0.7) !important;
                            border-radius: 24px !important;
                            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08), 
                                        0 0 20px rgba(' . implode(',', sscanf($primaryColor, "#%02x%02x%02x")) . ', 0.12) !important;
                            animation: gentle-glow 4s infinite alternate;
                            padding: 2rem !important;
                        }

                        /* Teks gelap untuk container login */
                        .fi-simple-main, .fi-simple-main h1, .fi-simple-main h2, .fi-simple-main h3, 
                        .fi-simple-main p, .fi-simple-main span, .fi-simple-main a, .fi-simple-main label {
                            color: #1e293b !important;
                        }
                        
                        /* ============================================
                           FORM INPUT ELEGAN & USER-FRIENDLY
                           ============================================ */
                        
                        /* Container Input: Border hijau agar selalu terlihat jelas */
                        /* Target class asli Filament: fi-input-wrp */
                        .fi-simple-main .fi-input-wrp {
                            background-color: #f8fafc !important;
                            border: 2px solid #059669 !important; /* Hijau Emerald 600 - SELALU terlihat */
                            border-radius: 12px !important;
                            box-shadow: 0 1px 4px rgba(5, 150, 105, 0.08) !important;
                            ring: none !important;
                            --tw-ring-shadow: none !important;
                            --tw-shadow: none !important;
                            overflow: hidden !important;
                            transition: all 0.25s ease !important;
                        }

                        /* Hover: hijau sedikit lebih gelap */
                        .fi-simple-main .fi-input-wrp:hover {
                            border-color: #047857 !important; /* Emerald 700 */
                            box-shadow: 0 2px 8px rgba(5, 150, 105, 0.15) !important;
                        }

                        /* Fokus: ring hijau terang menyala */
                        .fi-simple-main .fi-input-wrp:focus-within {
                            border-color: #047857 !important;
                            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.25), 0 2px 8px rgba(5, 150, 105, 0.15) !important;
                            background-color: #ffffff !important;
                        }

                        /* Input Field */
                        .fi-simple-main input[type="email"],
                        .fi-simple-main input[type="password"],
                        .fi-simple-main input[type="text"],
                        .fi-simple-main .fi-input {
                            background-color: transparent !important;
                            color: #111827 !important;
                            border: none !important;
                            padding: 0.75rem 1rem !important;
                            width: 100% !important;
                            height: auto !important;
                            min-height: 44px !important;
                            font-size: 0.95rem !important;
                        }

                        /* Placeholder */
                        .fi-simple-main input::placeholder,
                        .fi-simple-main .fi-input::placeholder {
                            color: #9ca3af !important; /* Gray 400 */
                            opacity: 1 !important;
                        }

                        /* Checkbox "Remember Me" */
                        .fi-simple-main input[type="checkbox"] {
                            width: 1.25rem !important;
                            height: 1.25rem !important;
                            background-color: #f8fafc !important;
                            border: 2px solid #059669 !important;
                            border-radius: 6px !important;
                            appearance: auto !important;
                            cursor: pointer !important;
                        }

                        /* Tombol Masuk - lebih premium */
                        .fi-simple-main .fi-btn-primary,
                        .fi-simple-main button[type="submit"] {
                            border-radius: 12px !important;
                            padding: 0.75rem 1.5rem !important;
                            font-weight: 600 !important;
                            letter-spacing: 0.025em !important;
                            transition: all 0.3s ease !important;
                        }

                        /* ============================================ */

                        @keyframes gentle-glow {
                            0% { box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05), 0 0 10px rgba(' . implode(',', sscanf($primaryColor, "#%02x%02x%02x")) . ', 0.08); }
                            100% { box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1), 0 0 30px rgba(' . implode(',', sscanf($primaryColor, "#%02x%02x%02x")) . ', 0.2); }
                        }
                        
                        /* ============================================
                           LOGO KHUSUS HALAMAN LOGIN
                           ============================================ */
                        .fi-simple-layout .fi-logo {
                            display: flex;
                            justify-content: center;
                            align-items: center;
                            width: 100%;
                            padding: 10px 0;
                        }
                        
                        .fi-simple-layout .fi-simple-header .fi-logo {
                            font-size: 7rem !important;
                            line-height: 7rem !important;
                        }
                        
                        .fi-simple-layout .fi-simple-header .fi-logo img,
                        .fi-simple-layout .fi-logo img {
                            height: 7rem !important;
                            max-height: 7rem !important;
                            width: auto !important;
                            max-width: 100% !important;
                            object-fit: contain;
                        }

                        /* ============================================
                           LAPORAN BPK - Card-Style Tabs
                           ============================================ */

                        /* Tab container - card style layout */
                        .fi-resource-laporan-bpk .fi-tabs {
                            background: transparent !important;
                            box-shadow: none !important;
                            ring: none !important;
                            --tw-ring-shadow: none !important;
                            border: none !important;
                            padding: 0.5rem 0 !important;
                            gap: 0.75rem !important;
                            flex-wrap: nowrap !important;
                            overflow-x: auto !important;
                        }

                        /* Each tab item as a card */
                        .fi-resource-laporan-bpk .fi-tabs-item {
                            display: flex !important;
                            flex-direction: column !important;
                            align-items: center !important;
                            justify-content: center !important;
                            padding: 0.85rem 1rem !important;
                            min-width: auto !important;
                            max-width: 140px !important;
                            border: 2px dashed #e2e8f0 !important;
                            border-radius: 14px !important;
                            background: #ffffff !important;
                            transition: all 0.25s ease !important;
                            gap: 0.4rem !important;
                            cursor: pointer !important;
                            position: relative !important;
                            flex-shrink: 0 !important;
                        }

                        /* Icon inside card - on top, centered */
                        .fi-resource-laporan-bpk .fi-tabs-item .fi-tabs-item-icon {
                            order: -1 !important;
                            width: 28px !important;
                            height: 28px !important;
                            color: #94a3b8 !important;
                            transition: color 0.25s ease !important;
                        }

                        /* Label below icon */
                        .fi-resource-laporan-bpk .fi-tabs-item .fi-tabs-item-label {
                            font-size: 0.75rem !important;
                            font-weight: 500 !important;
                            color: #64748b !important;
                            text-align: center !important;
                            line-height: 1.3 !important;
                            white-space: normal !important;
                            word-break: break-word !important;
                            transition: color 0.25s ease !important;
                        }

                        /* Badge inside card - positioned top-right */
                        .fi-resource-laporan-bpk .fi-tabs-item .fi-badge {
                            position: absolute !important;
                            top: 6px !important;
                            right: 6px !important;
                            font-size: 0.65rem !important;
                            min-width: 20px !important;
                            height: 20px !important;
                            padding: 0 5px !important;
                        }

                        /* Hover state */
                        .fi-resource-laporan-bpk .fi-tabs-item:hover {
                            border-color: #cbd5e1 !important;
                            background: #f8fafc !important;
                            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06) !important;
                        }

                        .fi-resource-laporan-bpk .fi-tabs-item:hover .fi-tabs-item-icon {
                            color: #475569 !important;
                        }

                        .fi-resource-laporan-bpk .fi-tabs-item:hover .fi-tabs-item-label {
                            color: #334155 !important;
                        }

                        /* Active tab - solid border + blue accent line */
                        .fi-resource-laporan-bpk .fi-tabs-item-active,
                        .fi-resource-laporan-bpk .fi-tabs-item.fi-active {
                            border: 2px solid #e2e8f0 !important;
                            border-style: solid !important;
                            background: #ffffff !important;
                            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1) !important;
                        }

                        .fi-resource-laporan-bpk .fi-tabs-item-active::after,
                        .fi-resource-laporan-bpk .fi-tabs-item.fi-active::after {
                            content: "" !important;
                            position: absolute !important;
                            bottom: -2px !important;
                            left: 20% !important;
                            right: 20% !important;
                            height: 3px !important;
                            background: #3b82f6 !important;
                            border-radius: 3px 3px 0 0 !important;
                        }

                        .fi-resource-laporan-bpk .fi-tabs-item-active .fi-tabs-item-icon,
                        .fi-resource-laporan-bpk .fi-tabs-item.fi-active .fi-tabs-item-icon {
                            color: #3b82f6 !important;
                        }

                        .fi-resource-laporan-bpk .fi-tabs-item-active .fi-tabs-item-label,
                        .fi-resource-laporan-bpk .fi-tabs-item.fi-active .fi-tabs-item-label {
                            color: #1e293b !important;
                            font-weight: 600 !important;
                        }

                        /* ===== Dark mode support ===== */
                        .dark .fi-resource-laporan-bpk .fi-tabs-item {
                            background: #1e293b !important;
                            border-color: #334155 !important;
                        }

                        .dark .fi-resource-laporan-bpk .fi-tabs-item:hover {
                            background: #334155 !important;
                            border-color: #475569 !important;
                        }

                        .dark .fi-resource-laporan-bpk .fi-tabs-item-active,
                        .dark .fi-resource-laporan-bpk .fi-tabs-item.fi-active {
                            background: #1e293b !important;
                            border-color: #475569 !important;
                            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2) !important;
                        }

                        .dark .fi-resource-laporan-bpk .fi-tabs-item .fi-tabs-item-icon {
                            color: #64748b !important;
                        }

                        .dark .fi-resource-laporan-bpk .fi-tabs-item .fi-tabs-item-label {
                            color: #94a3b8 !important;
                        }

                        .dark .fi-resource-laporan-bpk .fi-tabs-item-active .fi-tabs-item-icon,
                        .dark .fi-resource-laporan-bpk .fi-tabs-item.fi-active .fi-tabs-item-icon {
                            color: #60a5fa !important;
                        }

                        .dark .fi-resource-laporan-bpk .fi-tabs-item-active .fi-tabs-item-label,
                        .dark .fi-resource-laporan-bpk .fi-tabs-item.fi-active .fi-tabs-item-label {
                            color: #f1f5f9 !important;
                        }

                        /* ============================================
                           PREMIUM AI SIDEBAR MENU (Gemini Pro Style)
                           ============================================ */
                        .fi-sidebar-item:has(a[href*="analisis-ai"]) {
                            position: relative;
                            border-radius: 12px;
                            padding: 2px;
                            background: linear-gradient(135deg, #4285f4, #9b72cb, #d96570, #e8710a);
                            background-size: 300% 300%;
                            animation: ai-gradient-flow 4s ease infinite;
                            margin: 4px 0;
                        }
                        .fi-sidebar-item:has(a[href*="analisis-ai"]) > a.fi-sidebar-item-button {
                            background: #fff !important;
                            border-radius: 10px !important;
                            padding: 10px 12px !important;
                            transition: all 0.3s ease;
                        }
                        .dark .fi-sidebar-item:has(a[href*="analisis-ai"]) > a.fi-sidebar-item-button {
                            background: #1a1a2e !important;
                        }
                        .fi-sidebar-item:has(a[href*="analisis-ai"]) > a:hover {
                            background: #f8f4ff !important;
                        }
                        .dark .fi-sidebar-item:has(a[href*="analisis-ai"]) > a:hover {
                            background: #1e1e3a !important;
                        }
                        .fi-sidebar-item.fi-active:has(a[href*="analisis-ai"]) {
                            box-shadow: 0 0 16px rgba(139, 92, 246, 0.35), 0 0 32px rgba(66, 133, 244, 0.15);
                        }
                        .fi-sidebar-item.fi-active:has(a[href*="analisis-ai"]) > a.fi-sidebar-item-button {
                            background: #f0ecff !important;
                        }
                        .dark .fi-sidebar-item.fi-active:has(a[href*="analisis-ai"]) > a.fi-sidebar-item-button {
                            background: #1e1a3a !important;
                        }
                        .fi-sidebar-item:has(a[href*="analisis-ai"]) .fi-sidebar-item-icon {
                            color: #8b5cf6 !important;
                            filter: drop-shadow(0 0 3px rgba(139, 92, 246, 0.4));
                        }
                        .fi-sidebar-item:has(a[href*="analisis-ai"]) .fi-sidebar-item-label {
                            background: linear-gradient(135deg, #4285f4, #8b5cf6, #d96570);
                            -webkit-background-clip: text;
                            -webkit-text-fill-color: transparent;
                            background-clip: text;
                            font-weight: 700 !important;
                        }
                        .fi-sidebar-item:has(a[href*="analisis-ai"]) .fi-badge {
                            background: linear-gradient(135deg, #8b5cf6, #4285f4) !important;
                            color: white !important;
                            border: none !important;
                            font-weight: 700 !important;
                            font-size: 9px !important;
                            letter-spacing: 0.5px;
                            box-shadow: 0 0 8px rgba(139, 92, 246, 0.4);
                            animation: ai-badge-pulse 2s ease-in-out infinite;
                        }
                        @keyframes ai-gradient-flow {
                            0% { background-position: 0% 50%; }
                            50% { background-position: 100% 50%; }
                            100% { background-position: 0% 50%; }
                        }
                        @keyframes ai-badge-pulse {
                            0%, 100% { box-shadow: 0 0 8px rgba(139, 92, 246, 0.4); }
                            50% { box-shadow: 0 0 14px rgba(139, 92, 246, 0.7), 0 0 24px rgba(66, 133, 244, 0.3); }
                        }
                    </style>
                '
            )
            ->renderHook(
                \Filament\View\PanelsRenderHook::FOOTER,
                fn(): string => '
                    <div style="text-align:center;padding:1rem;font-size:0.75rem;color:#94a3b8;">
                        Developed by <strong style="color:#64748b;">DIDIK KURNIAWAN</strong> — KEC. CANGKUANG KAB. BANDUNG
                    </div>
                '
            );
    }
}
