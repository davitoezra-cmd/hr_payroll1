<?php

namespace App\Notifications;

use App\Models\CompanyPost;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewCompanyPostNotification extends Notification
{
    use Queueable;

    public function __construct(
        public CompanyPost $post
    ) {
    }

    /**
     * Channel notifikasi
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Data yang disimpan ke tabel notifications
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => $this->post->type,
            'title' => $this->post->title,
            'message' => 'Ada informasi baru dari perusahaan.',
            'post_id' => $this->post->id,
        ];
    }
}