@php
    // Built entirely from asset() (not route()/url()) so every URL in this manifest shares
    // the same base as the rest of the app's static resources — this project sets ASSET_URL
    // explicitly in .env specifically to get the correct public-facing host+path, whereas
    // route()/url() reflect whatever host the current request came in on, which can differ.
    $manifest = [
        'name' => 'Navsons Group Management',
        'short_name' => 'Navsons Group',
        'start_url' => asset('admin/dashboard'),
        'scope' => asset('/'),
        'display' => 'standalone',
        'orientation' => 'portrait',
        'background_color' => '#ffffff',
        'theme_color' => '#696cff',
        'icons' => [
            [
                'src' => asset('assets/img/pwa/icon-192.png'),
                'sizes' => '192x192',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
            [
                'src' => asset('assets/img/pwa/icon-192-maskable.png'),
                'sizes' => '192x192',
                'type' => 'image/png',
                'purpose' => 'maskable',
            ],
            [
                'src' => asset('assets/img/pwa/icon-512.png'),
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
            [
                'src' => asset('assets/img/pwa/icon-512-maskable.png'),
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'maskable',
            ],
        ],
    ];
@endphp
{!! json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}
