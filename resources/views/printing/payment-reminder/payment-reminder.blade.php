@use(\Illuminate\Support\Fluent)
<x-flux::print.first-page-header
    :address="Fluent::make($model->order->address_invoice)"
    :subject="$subject"
/>
<main>
    <div>
        {!! $text !!}
    </div>
</main>
