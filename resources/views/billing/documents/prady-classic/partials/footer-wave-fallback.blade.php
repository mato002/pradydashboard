@php
    $waveSvg = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 420 26" preserveAspectRatio="none">
  <path d="M0,26 L0,11 C70,4 140,18 210,11 C280,4 350,18 420,11 L420,26 Z" fill="#000000"/>
  <path d="M0,11 C70,4 140,18 210,11 C280,4 350,18 420,11" fill="none" stroke="#c6d600" stroke-width="2"/>
</svg>
SVG;
    $waveSrc = 'data:image/svg+xml;base64,'.base64_encode($waveSvg);
@endphp
<img src="{{ $waveSrc }}" class="pc-footer-wave pc-footer-wave-fallback pc-footer-wave-img" alt="">
