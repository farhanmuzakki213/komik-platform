<?php

namespace App\Livewire\Components;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class NotificationBell extends Component
{
    public $unreadCount = 0;

    public function mount()
    {
        $this->updateNotificationData();
    }

    public function getListeners()
    {
        if (!Auth::check()) return [];

        return [
            "echo-private:App.Models.User." . Auth::id() . ",.Illuminate\\Notifications\\Events\\BroadcastNotificationCreated" => 'handleNewNotification',
        ];
    }

    public function handleNewNotification($payload)
    {
        $this->updateNotificationData();
        $this->dispatch('play-notification-sound');
    }

    public function updateNotificationData()
    {
        $this->unreadCount = Auth::check() ? Auth::user()->unreadNotifications()->count() : 0;
    }

    public function markAsRead($notificationId, $url)
    {
        $notification = Auth::user()->notifications()->findOrFail($notificationId);
        $notification->markAsRead();

        return redirect()->to($url);
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        $this->updateNotificationData();
    }

    public function render()
    {
        return view('livewire.components.notification-bell', [
            'unreadNotifications' => Auth::check() ? Auth::user()->unreadNotifications()->latest()->take(5)->get() : []
        ]);
    }
}
