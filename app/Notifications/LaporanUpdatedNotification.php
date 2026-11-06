<?php

namespace App\Notifications;

use App\Models\Laporan;
use Illuminate\Notifications\Notification;

class LaporanUpdatedNotification extends Notification
{
    /**
     * @param  array<int, 'status'|'response'>  $events
     */
    public function __construct(
        public int $laporanId,
        public string $laporanTitle,
        public array $events,
        public ?string $status,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array{
     *     laporan_id: int,
     *     laporan_judul: string,
     *     events: array<int, 'status'|'response'>,
     *     status: ?string,
     *     message: string
     * }
     */
    public function toDatabase(object $notifiable): array
    {
        $statusLabel = $this->status === null
            ? null
            : (Laporan::STATUSES[$this->status] ?? ucfirst($this->status));

        $message = match ($this->events) {
            ['status', 'response'] => "Status laporan \"{$this->laporanTitle}\" diperbarui menjadi {$statusLabel} dan tanggapan admin diperbarui.",
            ['status'] => "Laporan \"{$this->laporanTitle}\" {$this->statusMessage($this->status)}.",
            default => "Admin memperbarui tanggapan pada laporan \"{$this->laporanTitle}\".",
        };

        return [
            'laporan_id' => $this->laporanId,
            'laporan_judul' => $this->laporanTitle,
            'events' => $this->events,
            'status' => $this->status,
            'message' => $message,
        ];
    }

    private function statusMessage(?string $status): string
    {
        return match ($status) {
            'approved' => 'telah disetujui',
            'rejected' => 'ditolak',
            default => 'diperbarui',
        };
    }
}
