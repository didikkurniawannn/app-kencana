<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Lazy;
use Filament\Facades\Filament;

class RealtimeNotificationAlert extends Component
{
    public int $unreadCount = 0;
    public int $previousCount = 0;
    public bool $hasNewNotification = false;
    public ?string $latestTitle = null;
    public ?string $latestBody = null;

    public function mount(): void
    {
        $this->unreadCount = $this->getUnreadCount();
        $this->previousCount = $this->unreadCount;
    }

    public function checkForNewNotifications(): void
    {
        $newCount = $this->getUnreadCount();

        if ($newCount > $this->previousCount) {
            $this->hasNewNotification = true;

            // Get the latest unread notification
            $latest = auth()->user()
                ?->unreadNotifications()
                ->latest()
                ->first();

            if ($latest) {
                $data = $latest->data;
                $this->latestTitle = $data['title'] ?? 'Notifikasi Baru';
                $this->latestBody = $data['body'] ?? null;
            }

            $this->dispatch('new-notification-received', [
                'title' => $this->latestTitle,
                'body' => $this->latestBody,
                'count' => $newCount,
            ]);
        }

        $this->previousCount = $newCount;
        $this->unreadCount = $newCount;
    }

    private function getUnreadCount(): int
    {
        return auth()->user()?->unreadNotifications()->count() ?? 0;
    }

    public function dismissAlert(): void
    {
        $this->hasNewNotification = false;
    }

    public function render()
    {
        return view('livewire.realtime-notification-alert');
    }
}
