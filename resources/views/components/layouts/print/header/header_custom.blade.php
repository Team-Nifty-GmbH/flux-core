<header>
        <div class="header-images">
            <img src="data:image/svg+xml;base64,{{ base64_encode(file_get_contents(resource_path('/img/RNGY_logo_custom.svg'))) }}" class="rennergyLogo">
            <div class="header-center">
                <div style="display: inline-block; padding-left: 15px; padding-right: 0px">
                    <div style="font-size: 15pt; color: #a61680; font-weight: bold; ">SAUBERE ENERGIE MIT ZUKUNFT</div>
                    <div style="font-size: 7pt; letter-spacing: 0.65px">Holzheizungen · Sonnenenergie · Wärmepumpen · Energiesysteme</div>
                </div>
            </div>
        </div>
        <div style="background: rgb(163,38,141); background: linear-gradient(90deg, rgba(163,38,141,1) 0%, rgba(121,32,104,1) 100%); height:1mm"></div>
        {{ $slot }}
</header>
