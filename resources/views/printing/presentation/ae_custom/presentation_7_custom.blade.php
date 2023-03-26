@extends('print.presentation.ae_custom.presentation_css_custom')

<style>
    .presentation-8 .presentation-image{
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


    .presentation-8 .presentation-image-container{
        flex-grow:1;
        height:100%;
        width:100%;
        display: flex;
        justify-content: center;
        align-items: center;
        background-color: lightgrey;
        overflow: hidden;
    }


    .presentation-8 .presentation-image-fallback-add { display: none}
    @page  {
        size: A4 landscape;
        margin: 0;
    }
</style>
<div class="presentation-7 presentation-main {{ now() }}">
    <div class="presentation-image-container" id="presentation-img-1">
        {!! $presentation_img_1 ??  '<img jsonkey="presentation_img_1" class="presentation-image" src="https://g1.img-dpreview.com/3ACBE6D011274856888F900E563D7A85.jpg"/>' !!}
        <div class="presentation-image-fallback-add">Add Img on right click</div>
    </div>
</div>
