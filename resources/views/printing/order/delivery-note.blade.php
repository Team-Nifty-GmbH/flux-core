@extends('flux::printing.order.order')

@section('positions.header')
    <tr>
        <th class="pr-8 text-left font-normal">
            {{ __('Pos.') }}
        </th>
        <th class="pr-8 text-left font-normal">
            {{ __('Name') }}
        </th>
        <th class="pr-8 text-center font-normal">
            {{ __('Amount') }}
        </th>
    </tr>
@endsection

@section('positions.positions')
    @foreach ($model->orderPositions as $position)
        <tbody class="bg-uneven">
            <tr>
                <td class="pos py-4 pr-8 align-top">
                    {{ $position->total_net_price ? $position->slug_position : '' }}
                </td>
                <td
                    class="py-4 pr-8 align-top"
                    style="padding-left: {{ $position->depth * 15 }}px"
                >
                    @if ($position->is_alternative)
                        <x-badge
                            color="amber"
                            class="mb-2"
                            :text="__('Alternative')"
                            position="right"
                        />
                    @endif

                    <p class="font-italic text-xs">
                        {{ $position->product_number }}
                    </p>
                    <p class="font-semibold">
                        {{ $position->name }}
                    </p>
                </td>
                <td class="py-4 pr-8 text-center align-top">
                    {{ format_number($position->amount) }}
                    {{ data_get($position, 'product.unit.abbreviation') }}
                </td>
            </tr>
        </tbody>
    @endforeach
@endsection

@section('summary')
    
@endsection

@section('total')
    
@endsection
