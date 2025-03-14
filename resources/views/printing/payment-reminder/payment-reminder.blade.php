<x-flux::print.first-page-header
    :address="$model->order->addressInvoice"
    :subject="$subject"
/>
<main>
    <div>
        {!! $text !!}
    </div>
</main>
