<x-layouts.print>
    <x-print.first-page-header :address="$model->order->addressInvoice" :subject="$subject" />
    <main>
        <div>
            {!! $text !!}
        </div>
    </main>
</x-layouts.print>
