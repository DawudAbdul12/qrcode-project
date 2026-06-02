<svg width="160" height="160" viewBox="0 0 160 160" xmlns="http://www.w3.org/2000/svg" aria-label="Active8 QR code">
    <rect width="160" height="160" fill="white" rx="8"/>
    <rect x="8" y="8" width="44" height="44" fill="black" rx="4"/>
    <rect x="16" y="16" width="28" height="28" fill="white" rx="2"/>
    <rect x="22" y="22" width="16" height="16" fill="black" rx="1"/>
    <rect x="108" y="8" width="44" height="44" fill="black" rx="4"/>
    <rect x="116" y="16" width="28" height="28" fill="white" rx="2"/>
    <rect x="122" y="22" width="16" height="16" fill="black" rx="1"/>
    <rect x="8" y="108" width="44" height="44" fill="black" rx="4"/>
    <rect x="16" y="116" width="28" height="28" fill="white" rx="2"/>
    <rect x="22" y="122" width="16" height="16" fill="black" rx="1"/>
    <g fill="black">
        @foreach (range(0, 54) as $i)
            <rect x="{{ 60 + (($i * 13) % 90) }}" y="{{ 8 + (($i * 19) % 140) }}" width="6" height="6"/>
        @endforeach
        <rect x="8" y="60" width="6" height="6"/><rect x="20" y="60" width="6" height="6"/><rect x="38" y="60" width="6" height="6"/>
        <rect x="14" y="72" width="6" height="6"/><rect x="32" y="72" width="6" height="6"/>
        <rect x="8" y="84" width="6" height="6"/><rect x="26" y="84" width="6" height="6"/>
        <rect x="20" y="96" width="6" height="6"/><rect x="38" y="96" width="6" height="6"/>
    </g>
    <rect x="58" y="70" width="44" height="24" rx="5" fill="white"/>
    <text x="80" y="86" text-anchor="middle" font-family="Georgia, serif" font-style="italic" font-weight="700" font-size="14" fill="#111" letter-spacing="-1">Active8</text>
</svg>
