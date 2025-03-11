<?php

namespace FluxErp\Mail\Order;

use FluxErp\Models\Order;
use FluxErp\Traits\Makeable;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmation extends Mailable
{
    use Makeable, Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
        $this->when($locale = $this->order?->addressInvoice?->language?->iso_name, fn () => $this->locale($locale));
    }

    public function attachments(): array
    {
        return [];
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'flux::emails.orders.order-confirmation',
        );
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __(
                'Order Confirmation for order :order_number',
                [
                    'order_number' => $this->order->order_number,
                ]
            ),
        );
    }
}
