<style>
    /**** Home Page Layout ****/
    .home-page .page-title {
        padding: 0 50px;
        text-align:center;
        color: #4F5B93;
    }

    .reasons-to-use h2 {
        font-size:1.3em;
        font-weight:normal;
        text-align: center;
        margin: 0 30px 60px 30px;
    }
    @media screen and (min-width: 750px) {
        .reasons-to-use h2 { margin-bottom:90px; }
    }

    .reasons-to-use {
        margin:0 auto 60px auto;
    }

    .home-page .sample-code { max-width:700px; margin-bottom:0; }
    .home-page .sample-code h2 { font-size:24px; }
    .home-page .try-playground { text-align:center; padding:80px 0 30px 0; }

    a.btn {
        background-color: #FFAB76;
        background-image: linear-gradient(hsla(23, 100%, 83%, 1), hsla(23, 100%, 63%, 1));
        display: inline-flex;
        font-weight: bold;
        text-decoration: none;
        cursor: pointer;
        box-shadow: 0 1px 2px 0 rgba(0,0,0,.5);
        color: white;
        border-radius: 32px;
        transition: all 0.2s;
    }
    a.btn .text {
        padding: 15px 30px;
        font-size: 16px;
        line-height: 16px;
    }
    a.btn .icon-container {
        background-color: hsla(23, 100%, 53%, 1);
        background-image: linear-gradient(hsla(23, 100%, 68%, 1), hsla(23, 100%, 48%, 1));
        border-top-right-radius: 32px;
        border-bottom-right-radius: 32px;
        transition: all 0.2s;
        display:flex;
        align-items:center;
    }
    html[lang='ar'] a.btn .icon-container {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
        border-top-left-radius: 32px;
        border-bottom-left-radius: 32px;
    }
    a.btn .arrow {
        display: inline-block;
        width: 0;
        height: 0;
        border-top: 8px solid transparent;
        border-left: 16px solid white;
        border-bottom: 8px solid transparent;
        margin: 16px;
    }
    html[lang='ar'] a.btn .arrow {
        border-left: 0;
        border-right: 16px solid white;
        margin: 16px;
    }
    a.btn:hover {
        background-color: hsla(23, 100%, 53%, 1);
        background-image: linear-gradient(hsla(23, 100%, 68%, 1), hsla(23, 100%, 48%, 1));
        box-shadow: 0 2px 5px 0 rgba(0,0,0,.5);
        transform: translateY(-3px);
    }
    a.btn:hover .icon-container {
        background-color: #FFAB76;
        background-image: linear-gradient(hsla(23, 100%, 83%, 1), hsla(23, 100%, 63%, 1));
    }

    /**** Card Layout ****/
    ul.cards {
        display: flex;
        flex-direction: column;
        margin: 0 20px;
    }
    @media (min-width:600px) {
        ul.cards { margin:0 40px; }
    }
    ul.cards li {
        display: flex;
        flex-direction: column;
        background-color: #fff;
        margin: 20px 20px 40px;
        padding: 0;
        box-shadow: 0 1px 5px 0 rgba(0,0,0,.5);
        border-radius: 4px;
        transition: box-shadow .2s ease-in-out;
        text-align: left;
    }
    ul.cards li div.img {
        background-color: #FFAB76;
        display: flex;
        flex-direction: column;
        justify-content: center;
        background-image: url('../img/card-background.svg');
        background-size: cover;
        border-top-left-radius: 4px;
        border-top-right-radius: 4px;
        background-position-y: center;
    }
    ul.cards li img {
        max-height:120px;
        padding: 20px;
        transform: scale3d(1, 1, 1);
        transition: transform .2s ease-in-out;
    }
    ul.cards li div.text {
        padding:20px;
    }
    ul.cards li div.text h3 {
        font-size: 1.5em; margin-bottom:10px;
        transition: all .2s ease-in-out;
    }
    ul.cards li div.text p {
        transition: color .2s ease-in-out;
        color:hsla(229, 30%, 60%, 1);
    }

    /* Card Hover */
    ul.cards li:hover { box-shadow: 0 0 40px 0 rgba(0,0,0,.5); }
    ul.cards li:hover img { transform: scale3d(1.1, 1.1, 1.1) rotate(-5deg); }
    ul.cards li:hover div.text h3 { margin-left:20px; color:hsla(229, 30%, 35%, 1); }
    ul.cards li:hover div.text h3.hover10 { margin-left:10px; color:hsla(229, 30%, 35%, 1); }
    ul.cards li:hover div.text p { color:hsla(229, 30%, 50%, 1); }

    /* Card Media Queries */
    @media screen and (min-width: 500px) {
        ul.cards li { flex-direction: row; }
        ul.cards li div.img {
            border-top-right-radius: 0;
            border-bottom-left-radius: 4px;
        }
    }

    @media screen and (min-width: 700px) {
        ul.cards li { margin: 0 auto 40px auto; }
    }

    @media screen and (min-width: 768px) {
        ul.cards {
            flex-flow: row wrap;
            margin: 0 20px;
        }
        ul.cards li { max-width: calc(50% - 60px); flex-direction: column; }
        ul.cards div.img { background-position-y: initial; }
    }

    @media screen and (min-width: 900px) {
        ul.cards li { flex-direction: row; }
    }

    @media screen and (min-width: 1000px) {
        ul.cards {
            flex-flow: row wrap;
            max-width: 1200px;
            margin: 0 auto;
        }
        ul.cards li { max-width: calc(50% - 120px); margin: 0 auto 80px auto; }
    }

    /**** Site Title Layout and CSS Animation ****/
    #svg-title-text { margin: -40px auto -20px -85px; animation: title-text 10s ease infinite; }
    @keyframes title-text {
        0%, 60%, 100% { transform: scale3d(0.8, 0.8, 0.8); }
        80%, 90% { transform: scale3d(1, 1, 1); }
    }
    @media screen and (min-width: 400px) { #svg-title-text { margin-left:-70px; } }
    @media screen and (min-width: 425px) { #svg-title-text { margin-left:-50px; } }
    @media screen and (min-width: 450px) { #svg-title-text { margin-left:-30px; } }
    @media screen and (min-width: 500px) { #svg-title-text { margin-left:-10px; } }
    @media screen and (min-width: 520px) { #svg-title-text { margin-left:auto; } }

    /* Rocketship Layout and CSS Animation */
	#svg-rocket { margin: -50px auto -30px -60px; animation: rocket 10s ease infinite; width:400px; height:400px; }
    @keyframes rocket {
        0%, 25%, 30%, 55%, 60%, 100% { transform: scale3d(0.67, 0.67, 0.67) rotate(45deg) translate3d(0, 0, 0); }
        15%, 45% { transform: scale3d(0.67, 0.67, 0.67) rotate(45deg) translate3d(0, -100px, 0); }
        80%, 90% { transform: scale3d(1, 1, 1) rotate(0deg); }
    }
    @media screen and (min-width: 400px) { #svg-rocket { margin-left:-55px; } }
    @media screen and (min-width: 425px) { #svg-rocket { margin-left:-40px; } }
    @media screen and (min-width: 450px) { #svg-rocket { margin-left:-30px; } }
    @media screen and (min-width: 500px) { #svg-rocket { margin-left:auto; } }
    @media screen and (min-width: 700px) { #svg-rocket { width:600px; height:600px; } }

    #svg-rocket #Fire { animation: fire 10s ease infinite; }
    @keyframes fire {
        0%, 25%, 30%, 55%, 60%, 100% { opacity: 0; }
        1%, 15%, 31%, 45%, 61%, 80%, 90% { opacity: 1; }
    }

    /*
    Animation using IntersectionObserver
    These get set by JavaScript near the bottom of this file.

    The first two cards in [reasons-to-use] do not use animation unless on a narrow
    screen (e.g.: phone) to avoid taking focus away from the main Rocket and H1 animation.
    */
    [data-animate="show-and-scale"] { opacity: 0; transform: scale(.5); }
    [data-animate="show-and-scale"].show-and-scale { animation: show-and-scale .5s ease-in-out forwards; }

    @media (max-width: 500px) {
        [data-animate="show-and-scale-mobile"] { opacity: 0; transform: scale(.5); }
        [data-animate="show-and-scale-mobile"].show-and-scale-mobile { animation: show-and-scale .5s ease-in-out forwards; }
    }

    [data-animate="move-from-right"] { transform: translateX(100px); }
    [data-animate].move-from-right { animation: move-from-right .5s ease-in-out forwards; }

    @keyframes show-and-scale {
        from { opacity: 0; transform: scale(.5); }
        to { opacity: 1; transform: scale(1); }
    }

    @keyframes move-from-right {
        from { transform: translateX(100px); }
        to { transform: translateX(0); }
    }
</style>
<script nomodule>
    (function() {
        'use strict';
        var isIE = (navigator.userAgent.indexOf('Trident/') !== -1);
        if (isIE) {
            // IE Only - Card Images will appear slightly larger, if using max-width they stretch
            var style = document.createElement('style');
            var css = '@media screen and (-ms-high-contrast: active), (-ms-high-contrast: none) {';
            css += '    ul.cards li img {',
            css += '        max-height: inherit;',
            css += '        max-width: 100px;',
            css += '    }',
            css += '}';
            style.innerHTML = css;
            document.head.appendChild(style);
        }
    })();
</script>
<?php
// Once full translations are made this will go at the top of the file for the <html> element
$html_dir = ($app->lang === 'ar' ? 'rtl' : 'ltr');
?>
<div class="home-page" dir="<?= $html_dir ?>">
    <div class="page-title">
        <h1>
            <!--
            After [Copy SVG Code] from Sketch the following changes were made when pasting here:
                Add [id="svg-title-text"]
                Add <title> and <desc> with "FastSitePHP"
                * On the Sketch Version 62 (latest tested version in 1/2020) the export did not work well
                  so the next line was manually used.
                Replace [font-family="Corbel"] with font-family="Helvetica, Roboto, Arial" and set font-size="76"
                Also search JavaScript on this page for '#text-2' as Corbel is being used with Windows
            -->
            <svg id="svg-title-text" width="440px" height="107px" viewBox="31 51 440 107" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                <title>FastSitePHP</title>
                <desc>FastSitePHP</desc>
                <defs>
                    <linearGradient x1="50%" y1="0%" x2="50%" y2="100%" id="linearGradient-1">
                        <stop stop-color="#4F5B93" stop-opacity="0.8" offset="0%"></stop>
                        <stop stop-color="#4F5B93" offset="100%"></stop>
                    </linearGradient>
                    <text id="text-2" font-family="Helvetica, Roboto, Arial" font-size="76" font-weight="normal">
                        <tspan x="32" y="131">FastSitePHP</tspan>
                    </text>
                    <filter x="-50%" y="-50%" width="200%" height="200%" filterUnits="objectBoundingBox" id="filter-3">
                        <feOffset dx="1" dy="2" in="SourceAlpha" result="shadowOffsetOuter1"></feOffset>
                        <feGaussianBlur stdDeviation="1" in="shadowOffsetOuter1" result="shadowBlurOuter1"></feGaussianBlur>
                        <feColorMatrix values="0 0 0 0 0.88627451   0 0 0 0 0.894117647   0 0 0 0 0.937254902  0 0 0 1 0" type="matrix" in="shadowBlurOuter1"></feColorMatrix>
                    </filter>
                </defs>
                <g id="FastSitePHP" stroke="none" fill="none">
                    <use fill="#4F5B93" fill-opacity="1" filter="url(#filter-3)" xlink:href="#text-2"></use>
                    <use fill="url(#linearGradient-1)" fill-rule="evenodd" xlink:href="#text-2"></use>
                    <use fill="#4F5B93" fill-opacity="1" xlink:href="#text-2"></use>
                </g>
            </svg>
        </h1>
        <!--
            After Sketch export to SVG the following changes were made when pasting here:
                Exclude xml header
                Add [id="svg-rocket"]
                Remove Generator Comment
                Remove height and width attributes
                Update <title> and <desc> with "FastSitePHP Rocketship"
        -->
        <svg id="svg-rocket" viewBox="0 0 400 400" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
            <title>FastSitePHP Rocketship</title>
            <desc>FastSitePHP Rocketship</desc>
            <defs>
                <filter x="-6.0%" y="-5.4%" width="112.0%" height="110.9%" filterUnits="objectBoundingBox" id="filter-1">
                    <feOffset dx="0" dy="0" in="SourceAlpha" result="shadowOffsetOuter1"></feOffset>
                    <feGaussianBlur stdDeviation="5" in="shadowOffsetOuter1" result="shadowBlurOuter1"></feGaussianBlur>
                    <feColorMatrix values="0 0 0 0 0   0 0 0 0 0   0 0 0 0 0  0 0 0 0.5 0" type="matrix" in="shadowBlurOuter1" result="shadowMatrixOuter1"></feColorMatrix>
                    <feMerge>
                        <feMergeNode in="shadowMatrixOuter1"></feMergeNode>
                        <feMergeNode in="SourceGraphic"></feMergeNode>
                    </feMerge>
                </filter>
                <linearGradient x1="50%" y1="0%" x2="50%" y2="100%" id="linearGradient-2">
                    <stop stop-color="#D1CB23" offset="0%"></stop>
                    <stop stop-color="#F8E71C" offset="100%"></stop>
                </linearGradient>
                <radialGradient cx="74.6337891%" cy="84.893549%" fx="74.6337891%" fy="84.893549%" r="105.365349%" gradientTransform="translate(0.746338,0.848935),scale(0.708333,1.000000),rotate(-180.000000),translate(-0.746338,-0.848935)" id="radialGradient-3">
                    <stop stop-color="#BDC4E1" offset="0%"></stop>
                    <stop stop-color="#8892BF" offset="100%"></stop>
                </radialGradient>
                <radialGradient cx="50%" cy="122.352431%" fx="50%" fy="122.352431%" r="137.35119%" gradientTransform="translate(0.500000,1.223524),scale(0.745934,1.000000),rotate(-90.000000),translate(-0.500000,-1.223524)" id="radialGradient-4">
                    <stop stop-color="#FF003C" offset="0%"></stop>
                    <stop stop-color="#BC051B" offset="100%"></stop>
                </radialGradient>
                <linearGradient x1="50%" y1="0%" x2="50%" y2="121.08028%" id="linearGradient-5">
                    <stop stop-color="#EEEEEE" offset="0%"></stop>
                    <stop stop-color="#888888" offset="100%"></stop>
                </linearGradient>
                <radialGradient cx="50%" cy="86.3463407%" fx="50%" fy="86.3463407%" r="219.569669%" gradientTransform="translate(0.500000,0.863463),scale(1.000000,0.324675),rotate(-122.013156),translate(-0.500000,-0.863463)" id="radialGradient-6">
                    <stop stop-color="#BBBED0" offset="0%"></stop>
                    <stop stop-color="#FFFFFF" offset="100%"></stop>
                </radialGradient>
                <radialGradient cx="19.9456788%" cy="-57.8596444%" fx="19.9456788%" fy="-57.8596444%" r="249.769639%" gradientTransform="translate(0.199457,-0.578596),scale(0.453730,1.000000),rotate(45.057671),translate(-0.199457,0.578596)" id="radialGradient-7">
                    <stop stop-color="#8794C5" offset="0%"></stop>
                    <stop stop-color="#4F5B93" offset="100%"></stop>
                </radialGradient>
                <radialGradient cx="50%" cy="122.352431%" fx="50%" fy="122.352431%" r="159.007974%" gradientTransform="translate(0.500000,1.223524),scale(1.000000,0.863801),rotate(-90.000000),translate(-0.500000,-1.223524)" id="radialGradient-8">
                    <stop stop-color="#FF003C" offset="0%"></stop>
                    <stop stop-color="#BC051B" offset="100%"></stop>
                </radialGradient>
                <linearGradient x1="48.6823242%" y1="0%" x2="51.3176758%" y2="100%" id="linearGradient-9">
                    <stop stop-color="#8794C5" stop-opacity="0.2" offset="0%"></stop>
                    <stop stop-color="#4F5B93" stop-opacity="0.2" offset="100%"></stop>
                </linearGradient>
                <filter x="-18.2%" y="-10.1%" width="136.4%" height="120.3%" filterUnits="objectBoundingBox" id="filter-10">
                    <feOffset dx="0" dy="0" in="SourceAlpha" result="shadowOffsetOuter1"></feOffset>
                    <feGaussianBlur stdDeviation="2" in="shadowOffsetOuter1" result="shadowBlurOuter1"></feGaussianBlur>
                    <feColorMatrix values="0 0 0 0 0   0 0 0 0 0   0 0 0 0 0  0 0 0 0.5 0" type="matrix" in="shadowBlurOuter1" result="shadowMatrixOuter1"></feColorMatrix>
                    <feMerge>
                        <feMergeNode in="shadowMatrixOuter1"></feMergeNode>
                        <feMergeNode in="SourceGraphic"></feMergeNode>
                    </feMerge>
                </filter>
            </defs>
            <g id="FastSitePHP_Rocketship" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                <g id="Rocketship" filter="url(#filter-1)" transform="translate(8.000000, 10.000000)">
                    <g id="Fire" transform="translate(131.000000, 294.000000)" stroke="#F5A623">
                        <path d="M61.0525589,22.4410162 L50.5813826,5.64101615 L17.8996703,5.64101615 L17.8996703,22.4410162 C17.8996703,22.4410162 12.5224784,40.0410162 28.8633364,52.0410162 C45.2041944,64.0410162 32.7789322,85.6410162 32.7789322,85.6410162 C59.2189914,85.6410162 61.0525589,22.4410162 61.0525589,22.4410162 Z" id="Flame-Left" stroke-width="1.6" fill="url(#linearGradient-2)" transform="translate(39.052559, 45.641016) rotate(-330.000000) translate(-39.052559, -45.641016) "></path>
                        <path d="M105.052559,22.4410162 L94.5813826,5.64101615 L61.8996703,5.64101615 L61.8996703,22.4410162 C61.8996703,22.4410162 56.5224784,40.0410162 72.8633364,52.0410162 C89.2041944,64.0410162 76.7789322,85.6410162 76.7789322,85.6410162 C103.218991,85.6410162 105.052559,22.4410162 105.052559,22.4410162 Z" id="Flame-Right" stroke-width="2" fill="url(#linearGradient-2)" transform="translate(83.052559, 45.641016) scale(-1, 1) rotate(-330.000000) translate(-83.052559, -45.641016) "></path>
                        <path d="M69.3202879,84.2398106 C79.0189701,86.5487216 85.9629055,89.8927409 85.9998534,69.9032975 C86.0467128,44.5516063 74.8538971,24 61,24 C47.1461029,24 35.9532872,44.5516063 36.0001466,69.9032975 C36.047006,95.2549886 47.2064088,83.0755481 61,83.0755481 C63.9175432,83.0755481 66.7180137,83.620436 69.3202879,84.2398106 Z" id="Flame-Center-2" stroke-width="2" fill="#F8E71C"></path>
                        <path d="M82,25.8 L71.5288237,9 L38.8471114,9 L38.8471114,25.8 C38.8471114,25.8 33.4699196,43.4 49.8107775,55.4 C66.1516355,67.4 53.7263733,89 53.7263733,89 C80.1664325,89 82,25.8 82,25.8 Z" id="Flame-Center-1" stroke-width="2" fill="url(#linearGradient-2)"></path>
                    </g>
                    <g id="Wing-Left" transform="translate(0.000000, 149.000000)" stroke="#4F5B93" stroke-width="2">
                        <path d="M-5.68434189e-14,0 L192,124.865497 C192,124.865497 88.6825271,136 37.6,136 C24.6825271,136 -5.68434189e-14,124.865497 -5.68434189e-14,124.865497 L-5.68434189e-14,0 Z" id="Wing-Left-Bg" fill="url(#radialGradient-3)" transform="translate(96.000000, 68.000000) scale(-1, 1) translate(-96.000000, -68.000000) "></path>
                        <path d="M60,85.8450292 L-5.68434189e-14,124.865497 C-5.68434189e-14,124.865497 26.8812938,127.762489 60,130.601077 L60,85.8450292 L60,85.8450292 Z" id="Wing-Left-Tip" fill="url(#radialGradient-4)"></path>
                    </g>
                    <g id="Wing-Right" transform="translate(192.000000, 149.000000)" stroke="#4F5B93" stroke-width="2">
                        <path d="M0,0 L192,124.865497 C192,124.865497 88.6825271,136 37.6,136 C24.6825271,136 0,124.865497 0,124.865497 L0,0 Z" id="Wing-Right-Bg" fill="url(#radialGradient-3)"></path>
                        <path d="M132,85.8450292 L192,124.865497 C192,124.865497 165.118706,127.762489 132,130.601077 L132,85.8450292 L132,85.8450292 Z" id="Wing-Right-Tip" fill="url(#radialGradient-4)"></path>
                    </g>
                    <g id="Body" transform="translate(142.000000, 0.000000)">
                        <polygon id="Engine-Nozzle" stroke="#4F5B93" stroke-width="2" fill="url(#linearGradient-5)" points="90 322 10 322 21.8624686 276 78.1375314 276"></polygon>
                        <path d="M50,298.673529 C77.6142375,298.673529 100,342.011184 100,241.846678 C100,141.682171 64.2857143,-7.20857018e-15 50,-7.58796861e-17 C35.7142857,1.10784342e-14 0,141.682171 0,241.846678 C0,342.011184 22.3857625,298.673529 50,298.673529 Z" id="Frame" stroke="#4F5B93" stroke-width="2" fill="url(#radialGradient-6)"></path>
                        <path d="M99.5889378,263 C96.4286782,335.431258 75.4316587,298.673529 50,298.673529 C24.5683413,298.673529 3.57132178,335.431258 0.411062248,263 L99.5889378,263 L99.5889378,263 Z" id="Bottom" stroke="#4F5B93" stroke-width="2" fill="url(#radialGradient-7)"></path>
                        <path d="M72.8907171,53 C64.2590789,21.2016298 55.5262303,-2.75918234e-15 50,0 C44.4737697,4.31489153e-15 35.7409211,21.2016298 27.1092829,53 L72.8907171,53 L72.8907171,53 Z" id="Tip" stroke="#4F5B93" stroke-width="2" fill="url(#radialGradient-8)"></path>
                        <path d="M50,0 C64.2857143,-7.1326905e-15 100,141.682171 100,241.846678 C100,342.011184 77.6142375,298.673529 50,298.673529 L50,4.29472718e-15 L50,0 Z" id="Shadow" fill="url(#linearGradient-9)"></path>
                    </g>
                    <g id="PHP-Label" filter="url(#filter-10)" transform="translate(170.000000, 144.000000)">
                        <rect id="PHP-Rect" stroke="#4F5B93" stroke-width="2" fill="#FFFFFF" x="1" y="1" width="42" height="77"></rect>
                        <text id="PHP" transform="translate(22.000000, 39.000000) rotate(-90.000000) translate(-22.000000, -39.000000) " font-family="Arial-BoldMT, Arial" font-size="28.8" font-weight="bold" fill="#4F5B93">
                            <tspan x="-8" y="49">PHP</tspan>
                        </text>
                    </g>
                </g>
            </g>
        </svg>
        <script>
            // Is the User using Safari on a Mac Device that doesn't support Retina?
            // This will include devices such as iPad 2 and older Mac Laptops.
            //
            // If so then including the SVG drop-shadows filters causes the animated
            // SVG to become blurry and not properly render so remove the drop-shadow
            // filters for all SVG Elements on the Rocketship so that it looks good
            // during animation.
            //
            // This Script is intended to be ran inline on the page immediately
            // after the SVG for the Rocketship.
            //
            // Also update the Page Title font for Windows.
            //
            /* Validates with [jshint] */
            /* jshint strict: true */
            (function() {
                "use strict"; // Invoke Strict Mode

                // Check Device Type from the User-Agent
                var ua = navigator.userAgent;
                var removeFilters = (ua.indexOf("Mac OS X") > -1 &&
                    ua.indexOf("Chrome/") === -1 &&
                    ua.indexOf("Safari/") > -1 &&
                    window.devicePixelRatio === 1);

                // Remove filter on if Mac/Safari and not Retina
                if (removeFilters) {
                    var svgElements = document.querySelectorAll("#svg-rocket g[filter]");
                    Array.prototype.forEach.call(svgElements, function(g) {
                        g.setAttribute("filter", "");
                    });
                }

                // Use Corbel with Windows
                if (ua.indexOf('Windows NT') !== -1) {
                    var text2 = document.querySelector('#text-2');
                    if (text2 !== null && text2.getAttribute('font-family') === 'Helvetica, Roboto, Arial') {
                        text2.setAttribute('font-family', 'Corbel, Helvetica, Roboto, Arial');
                        text2.setAttribute('font-size', '84');
                    }
                }
            })();
        </script>
    </div>
    <div class="reasons-to-use">
        <h2><?= $app->escape($i18n['page_desc']) ?></h2>
	    <ul class="cards">
		    <li>
		    	<div class="img">
                    <img src="<?= $app->rootDir() ?>img/icons/Better-Sites.svg" alt="<?= $app->escape($i18n['better_title']) ?>" data-animate="show-and-scale-mobile">
                </div>
		    	<div class="text">
			    	<h3><?= $app->escape($i18n['better_title']) ?></h3>
			    	<p><?= $app->escape($i18n['better_desc']) ?></p>
		    	</div>
		    </li>
		    <li>
                <div class="img">
                    <img src="<?= $app->rootDir() ?>img/icons/Performance.svg" alt="<?= $app->escape($i18n['performance_title']) ?>" data-animate="show-and-scale-mobile">
                </div>
		    	<div class="text">
			    	<h3><?= $app->escape($i18n['performance_title']) ?></h3>
			    	<p><?= $app->escape($i18n['performance_desc']) ?></p>
		    	</div>
		    </li>
		    <li>
                <div class="img">
                    <img src="<?= $app->rootDir() ?>img/icons/Lightswitch.svg" alt="<?= $app->escape($i18n['setup_title']) ?>" data-animate="show-and-scale">
                </div>
		    	<div class="text">
                    <h3><?= $app->escape($i18n['setup_title']) ?></h3>
			    	<p><?= $app->escape($i18n['setup_desc']) ?></p>
		    	</div>
		    </li>
		    <li>
                <div class="img">
                    <img src="<?= $app->rootDir() ?>img/icons/Samples.svg" alt="<?= $app->escape($i18n['learn_title']) ?>" data-animate="show-and-scale">
                </div>
		    	<div class="text">
                    <h3 class="hover10"><?= $app->escape($i18n['learn_title']) ?></h3>
			    	<p><?= $app->escape($i18n['learn_desc']) ?></p>
		    	</div>
		    </li>
		    <li>
                <div class="img">
                    <img src="<?= $app->rootDir() ?>img/icons/Security-Lock.svg" alt="<?= $app->escape($i18n['security_title']) ?>" data-animate="show-and-scale">
                </div>
		    	<div class="text">
                    <h3><?= $app->escape($i18n['security_title']) ?></h3>
			    	<p><?= $app->escape($i18n['security_desc']) ?></p>
		    	</div>
		    </li>
		    <li>
                <div class="img">
                    <img src="<?= $app->rootDir() ?>img/icons/Clipboard.svg" alt="<?= $app->escape($i18n['test_title']) ?>" data-animate="show-and-scale">
                </div>
		    	<div class="text">
                    <h3><?= $app->escape($i18n['test_title']) ?></h3>
			    	<p><?= $app->escape($i18n['test_desc']) ?></p>
		    	</div>
		    </li>
	    </ul>
    </div>
    <section class="content sample-code">
        <h2><?= $app->escape($i18n['sample_code']) ?></h2>
        <pre><code class="language-php"><?= $app->escape($sample_code) ?></code></pre>
    </section>
    <div class="try-playground" data-animate="move-from-right">
        <a href="<?= $app->rootUrl() . $app->lang ?>/playground" class="btn">
            <span class="text"><?= $app->escape($i18n['create_site']) ?></span>
            <span class="icon-container">
                <span class="arrow"></span>
            </span>
        </a>
    </div>
</div>

<!-- Page Animation using IntersectionObserver -->
<script type="module">
    const animationObserver = new IntersectionObserver((entries, observer) => {
        for (const entry of entries) {
            if (entry.isIntersecting) {
                const className = entry.target.getAttribute('data-animate');
                entry.target.classList.add(className);
                observer.unobserve(entry.target);
            }
        }
    });
    const elements = document.querySelectorAll('[data-animate]');
    for (const element of elements) {
        animationObserver.observe(element);
    }
</script>
<script nomodule>
    (function() {
        'use strict';

        function animate() {
            var animationObserver = new IntersectionObserver(function(entries, observer) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        var className = entry.target.getAttribute('data-animate');
                        entry.target.classList.add(className);
                        observer.unobserve(entry.target);
                    }
                });
            });
            var elements = document.querySelectorAll('[data-animate]');
            Array.prototype.forEach.call(elements, function(element) {
                animationObserver.observe(element);
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            var hasObserver = (window.IntersectionObserver !== undefined);
            if (hasObserver) {
                animate();
            } else {
                var url = 'https://cdnjs.cloudflare.com/polyfill/v3/polyfill.min.js?version=4.8.0&features=IntersectionObserver';
                var script = document.createElement('script');
                script.onload = animate;
                script.src = url;
                document.querySelector('head').appendChild(script);
            }
        });
    })();
</script>