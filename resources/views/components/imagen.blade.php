{{--
    Espacio reservado para una imagen.
    Si el archivo existe en public/ se muestra; si no, aparece un marcador
    neutro con el texto indicado, sin romper el diseno.

    Uso: <x-imagen ruta="images/login.jpg" texto="Foto de la tienda" class="h-48" />
--}}
@props(['ruta' => null, 'texto' => 'Imagen', 'alt' => null, 'verificada' => false])

@if ($ruta && ($verificada || file_exists(public_path($ruta))))
    <img src="{{ asset($ruta) }}" alt="{{ $alt ?? $texto }}" loading="lazy" decoding="async" {{ $attributes->merge(['class' => 'object-cover']) }}>
@else
    <div role="img" aria-label="{{ $alt ?? $texto }}" {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center gap-1 bg-gray-100 text-gray-400 border border-dashed border-gray-300']) }}>
        <x-icon name="photo" class="w-1/3 max-w-[2.5rem] h-auto" />
        @if ($texto)
            <span class="text-[11px] font-medium px-2 text-center leading-tight">{{ $texto }}</span>
        @endif
    </div>
@endif
