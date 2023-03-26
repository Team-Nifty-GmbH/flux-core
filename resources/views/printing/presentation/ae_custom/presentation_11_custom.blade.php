@extends('print.presentation.presentation_css_custom')

<style>
    .presentation-11 .presentation-bar{
        width: fit-content;
        background-color: black;
        font-size: 2rem;
        margin-bottom: 1.5rem;
        color:white;
        text-align: left;
        padding-left: 0.2rem;
        padding-right: 0.2rem;
    }

    .presentation-11 .presentation-bar:focus-visible{
        outline: none;
    }

    .presentation-11 .presentation-header{
        font-size: 3rem;
        color: black;
        text-align: left;
        width: 100%;
        margin-bottom: 1rem;
    }

    .presentation-11 .presentation-header:focus-visible{
        outline: none;
    }

    .presentation-11 .presentation-text-field{
        width: 100%;
        font-size: 1.5rem;
        overflow-y: hidden;
        height: auto;
        font-family: "DejaVu Sans Condensed";
        text-align: left;
    }
    .presentation-11 .presentation-text-field-container{
        height:100%;
        width:50%;
        padding-left:2rem;
        padding-right:2rem;
        padding-top: 5rem;
        background-color: #FFFFFF;
        position: relative;
    }
    .presentation-11 .presentation-text-field:focus-visible{
        outline: none;
    }

    .presentation-11 .presentation-right-side{
        position: relative;
        width:50%;
        height:100%;
    }

    .presentation-11 .presentation-miscellaneous-logo{
        position: absolute;
        left:2rem;
        bottom: 2rem;
    }
    .presentation-11 .presentation-miscellaneous-company{
        position: absolute;
        right: 1rem;
        bottom: 2rem;
    }

    .presentation-11 .presentation-image{
        position: relative;
        top:0;
        left:0;
        max-width:100%;
        max-height:100%;
        user-drag: none;
        -webkit-user-drag: none;
        user-select: none;
        -moz-user-select: none;
        -webkit-user-select: none;
        -ms-user-select: none;

    }

    .presentation-11 .presentation-image-headers{
        position: absolute;
        left:0;
        top: 65%;
    }

    .presentation-11 .presentation-image-header{
        margin-bottom: 1rem;
        background-color: #FFFFFF ;
        height: 3rem;
        min-width: 0;
        font-size: 2rem;
        width: fit-content;
        padding-left: 1rem;
        padding-right: 1rem;
        text-align: left;
    }

    .presentation-11 .presentation-image-header:focus-visible{
        outline: none;
    }
    .presentation-11 .presentation-image-container{
        width:100%;
        height:33.3%;
        display: flex;
        justify-content: center;
        align-items: center;
        background-color: lightgrey;
        overflow: hidden;
    }

    #presentation-img-2{
        background-color: #adadad;
    }

    .presentation-11 .presentation-image-fallback-add { display: none}
    @page  {
        size: A4 landscape;
        margin: 0;
    }
</style>
<div class="presentation-11 presentation-main {{ now() }}">
    <div class="presentation-text-field-container">
        <div class="presentation-header" jsonkey="presentation_header" contenteditable="true">{!! $presentation_header ?? 'Titel' !!}</div>
        <div class="presentation-bar" contenteditable="true" jsonkey="presentation_bar">{!! $presentation_bar ?? 'Subtitel' !!}</div>
        <div contenteditable="true" class="presentation-text-field" jsonkey="presentation_text">
            {!! $presentation_text ?? ' Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam
            nonumy eirmod tempor invidunt ut labore et dolore magna
            aliquyam erat, sed diam voluptua. At vero eos et accusam et justo
            duo dolores et ea rebum. Stet clita kasd gubergren, no sea
            takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum
            dolor sit amet, consetetur sadipscing elitr, sed diam nonumy
            eirmod tempor invidunt ut labore et dolore magna aliquyam erat,
            sed diam voluptua. At vero eos et accusam et justo duo dolores et
            ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est
            Lorem ipsum dolor sit amet.' !!}
        </div>
        <div class="presentation-miscellaneous-logo">Logo</div>
    </div>
    <div class="presentation-right-side">
        <div class="presentation-image-container" id="presentation-img-1">
            {!! $presentation_img_1 ??  '<img jsonkey="presentation_img_1" class="presentation-image" src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/2d/VW_Golf_1.4_TSI_BlueMotion_Technology_CUP_%28VII%29_%E2%80%93_Heckansicht%2C_15._Juni_2014%2C_D%C3%BCsseldorf.jpg/2560px-VW_Golf_1.4_TSI_BlueMotion_Technology_CUP_%28VII%29_%E2%80%93_Heckansicht%2C_15._Juni_2014%2C_D%C3%BCsseldorf.jpg"/>' !!}
            <div class="presentation-image-fallback-add">Add Img on right click</div>
        </div>
        <div class="presentation-image-container" id="presentation-img-2">
            {!! $presentation_img_2 ??  '<img jsonkey="presentation_img_2" class="presentation-image" src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/2d/VW_Golf_1.4_TSI_BlueMotion_Technology_CUP_%28VII%29_%E2%80%93_Heckansicht%2C_15._Juni_2014%2C_D%C3%BCsseldorf.jpg/2560px-VW_Golf_1.4_TSI_BlueMotion_Technology_CUP_%28VII%29_%E2%80%93_Heckansicht%2C_15._Juni_2014%2C_D%C3%BCsseldorf.jpg"/>' !!}
            <div class="presentation-image-fallback-add">Add Img on right click</div>
        </div>
        <div class="presentation-image-container" id="presentation-img-3">
            {!! $presentation_img_3 ??  '<img jsonkey="presentation_img_3" class="presentation-image" src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/2d/VW_Golf_1.4_TSI_BlueMotion_Technology_CUP_%28VII%29_%E2%80%93_Heckansicht%2C_15._Juni_2014%2C_D%C3%BCsseldorf.jpg/2560px-VW_Golf_1.4_TSI_BlueMotion_Technology_CUP_%28VII%29_%E2%80%93_Heckansicht%2C_15._Juni_2014%2C_D%C3%BCsseldorf.jpg"/>' !!}
            <div class="presentation-image-fallback-add">Add Img on right click</div>
        </div>
        <div class="presentation-image-headers">
            <div jsonkey="presentation_img_header_1" class="presentation-image-header" contenteditable="true">{!! $presentation_img_header_1 ?? 'Text1' !!}</div>
            <div jsonkey="presentation_img_header_2" class="presentation-image-header" contenteditable="true">{!! $presentation_img_header_2 ?? 'Text2' !!}</div>
            <div jsonkey="presentation_img_header_3" class="presentation-image-header" contenteditable="true">{!! $presentation_img_header_3 ?? 'Text3' !!}</div>
            <div jsonkey="presentation_img_header_4" class="presentation-image-header" contenteditable="true">{!! $presentation_img_header_4 ?? 'Text4' !!}</div>
            <div jsonkey="presentation_img_header_5" class="presentation-image-header" contenteditable="true">{!! $presentation_img_header_5 ?? 'Text5' !!}</div>
        </div>
        <div class="presentation-miscellaneous-company">Company Name</div>
    </div>
</div>
