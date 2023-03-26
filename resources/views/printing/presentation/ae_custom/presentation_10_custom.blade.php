@extends('print.presentation.ae_custom.presentation_css_custom')

<style>
    .presentation-10 .presentation-miscellaneous-logo{
        position: absolute;
        right:2rem;
        bottom: 2rem;
    }
    .presentation-10 .presentation-miscellaneous-company{
        position: absolute;
        left: 2rem;
        bottom: 2rem;
    }

    .presentation-10 .presentation-image{
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

    .presentation-10 .presentation-image-container{
        flex-grow:1;
        height:100%;
        width:100%;
        display: flex;
        justify-content: center;
        align-items: center;
        background-color: lightgrey;
        overflow: hidden;
    }


    .presentation-10 .presentation-image-fallback-add { display: none}

    .presentation-10 .presentation-text-field-container {
        position:absolute;
        top:10rem;
        z-index: 10;
        width:100%;
        padding-left: 3rem;
        padding-right:1rem;
    }


    .presentation-10 .presentation-header{
        font-size: 5rem;
        font-weight: bold;
        color: black;
        text-align: center;
        width: 100%;
    }

    .presentation-10 .presentation-header:focus-visible{
        outline: none;
    }

    .presentation-10 .presentation-bar{
        width: fit-content;
        background-color: black;
        font-size: 2rem;
        margin-bottom: 1.5rem;
        color:white;
        text-align: center;
        padding-left: 0.2rem;
        padding-right: 0.2rem;
        margin-left: auto;
        margin-right: auto;
    }

    .presentation-10 .presentation-bar:focus-visible{
        outline: none;
    }
    @page  {
        size: A4 landscape;
        margin: 0;
    }
</style>
<section class="presentation-10 presentation-main {{ now() }}">
    <div class="presentation-text-field-container">
        <div class="presentation-header" jsonkey="presentation_header" contenteditable="true">{!! $presentation_header ?? 'Titelaa' !!}</div>
        <div class="presentation-bar" contenteditable="true" jsonkey="presentation_bar">{!! $presentation_bar ?? 'Subtitel' !!}</div>
    </div>
    <div class="presentation-image-container" id="presentation-img-1">
        {!! $presentation_img_1 ??  '<img jsonkey="presentation_img_1" class="presentation-image" src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/2d/VW_Golf_1.4_TSI_BlueMotion_Technology_CUP_%28VII%29_%E2%80%93_Heckansicht%2C_15._Juni_2014%2C_D%C3%BCsseldorf.jpg/2560px-VW_Golf_1.4_TSI_BlueMotion_Technology_CUP_%28VII%29_%E2%80%93_Heckansicht%2C_15._Juni_2014%2C_D%C3%BCsseldorf.jpg"/>' !!}
        <div class="presentation-image-fallback-add">Add Img on right click</div>
    </div>
    <div class="presentation-miscellaneous-company">Company Name</div>
    <div class="presentation-miscellaneous-logo">Logo</div>
</section>
