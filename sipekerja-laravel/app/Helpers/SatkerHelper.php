<?php

if (!function_exists('activeSatkerId')) {
    function activeSatkerId(): ?string
    {
        return session('active_satker_id');
    }
}

if (!function_exists('activeSatkerType')) {
    function activeSatkerType(): string
    {
        return session('active_satker_type', 'provinsi');
    }
}

if (!function_exists('isProvinsi')) {
    function isProvinsi(): bool
    {
        return session('active_satker_type') === 'provinsi';
    }
}

if (!function_exists('isKabkot')) {
    function isKabkot(): bool
    {
        return session('active_satker_type') === 'kabkot';
    }
}
