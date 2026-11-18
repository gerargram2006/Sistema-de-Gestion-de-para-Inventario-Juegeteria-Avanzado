{{--
    Foto de un juguete. Busca public/images/productos/{SKU}.jpg, .png o .webp;
    si no existe, muestra el marcador de imagen.
--}}
@props(['producto', 'texto' => null])

@php
    $ruta = collect(['jpg', 'png', 'webp'])
        ->map(fn ($ext) => "images/productos/{$producto->sku}.{$ext}")
        ->first(fn ($archivo) => file_exists(public_path($archivo)));
@endphp

<x-imagen :ruta="$ruta" :texto="$texto" :alt="$producto->nombre" {{ $attributes->merge(['class' => 'rounded-lg']) }} />
