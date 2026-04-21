@extends('flux::printing.order.order')

@section('positions.header')
    <tr>
        <th style="padding-right: 32px; text-align: left; font-weight: 400">
            {{ __('Pos.') }}
        </th>
        <th style="padding-right: 32px; text-align: left; font-weight: 400">
            {{ __('Name') }}
        </th>
        <th style="padding-right: 32px; text-align: center; font-weight: 400">
            {{ __('Amount') }}
        </th>
    </tr>
@endsection

@section('positions.positions')
    @foreach($model->orderPositions as $position)
        <tbody>
            <tr @if($loop->odd) style="background: #f2f4f7" @endif>
                <td
                    class="pos"
                    style="
                        padding-top: 16px;
                        padding-bottom: 16px;
                        padding-right: 32px;
                        vertical-align: top;
                    "
                >
                    {{ $position->total_net_price ? $position->slug_position : '' }}
                </td>
                <td
                    style="padding-top: 16px; padding-bottom: 16px; padding-right: 32px; vertical-align: top; padding-left: {{ $position->depth * 15 }}px"
                >
                    @if($position->is_alternative)
                        <x-badge
                            color="amber"
                            style="margin-bottom: 8px"
                            :text="__('Alternative')"
                            position="right"
                        />
                    @endif

                    <p style="font-style: italic; font-size: 12px">
                        {{ $position->product_number }}
                    </p>
                    <p style="font-weight: 600">{{ $position->name }}</p>
                </td>
                <td
                    style="
                        padding-top: 16px;
                        padding-bottom: 16px;
                        padding-right: 32px;
                        text-align: center;
                        vertical-align: top;
                    "
                >
                    {{ Number::format($position->amount) }} {{ data_get($position, 'product.unit.abbreviation') }}
                </td>
            </tr>
        </tbody>
    @endforeach
@endsection

@section('summary')

@endsection

@section('total')

@endsection
