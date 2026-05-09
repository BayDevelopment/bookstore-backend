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

class OrderConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public OrderModel $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '✅ Pesanan Kamu Berhasil Dibuat!');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.order-confirmation');
    }
}
