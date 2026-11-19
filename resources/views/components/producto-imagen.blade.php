{{--
    Foto de un juguete: public/images/productos/{SKU}.jpg, .png o .webp.
    Si no existe, se muestra el marcador de imagen.
--}}
@props(['producto', 'texto' => null])

<x-imagen :ruta="$producto->rutaImagen()" :verificada="true" :texto="$texto" :alt="$producto->nombre" {{ $attributes->merge(['class' => 'rounded-lg object-contain bg-white border border-gray-200 p-0.5']) }} />
