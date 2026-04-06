<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MustUpdateProfile
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user && $user->profile_updated_at === null) {
            // Coba ambil tenant aktif, atau pertama dari instansi user jika belum ada tenant yang aktif di panel
            $tenant = \Filament\Facades\Filament::getTenant() ?? $user->instansi()->first();
            
            if ($tenant) {
                try {
                    $profileUrl = \App\Filament\Resources\UserResource::getUrl('edit', [
                        'record' => $user,
                        'tenant' => $tenant
                    ]);

                    $currentUrl = $request->fullUrl();
                    $isLogout = $request->routeIs('filament.admin.auth.logout');
                    // Abaikan Livewire requests baik lewat header maupun path
                    $isLivewire = $request->hasHeader('X-Livewire') || str_contains($currentUrl, '/livewire/update');
                    $isProfilePage = str_contains($currentUrl, $profileUrl);

                    // PENTING: Jangan redirect jika sedang di halaman login atau logout
                    if (!$isProfilePage && !$isLogout && !$isLivewire && !str_contains($currentUrl, 'login')) {
                        return redirect()->to($profileUrl);
                    }
                } catch (\Exception $e) {
                    // Fail silently
                }
            }
        }

        return $next($request);
    }
}
