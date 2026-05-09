<?php

namespace App\Mail;

use App\Models\OrderModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public OrderModel $order) {}

    public function envelope(): Envelope
    {
        $subject = match ($this->order->status) {
            'confirmed' => '✅ Pesanan Kamu Diterima!',  // ❌ bukan 'accepted'
            'rejected'  => '❌ Pesanan Kamu Ditolak',
            default     => 'Update Status Pesanan',
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-status',
            with: ['order' => $this->order],  // ✅ tambahkan ini
        );
    }
}
