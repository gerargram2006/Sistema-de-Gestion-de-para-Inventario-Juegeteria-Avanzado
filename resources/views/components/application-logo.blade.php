{{--
    Logotipo de la tienda. Si existe public/images/logo.png se usa esa imagen;
    si no, se muestra un isotipo generico (bloque de construccion).
--}}
@if (file_exists(public_path('images/logo.png')))
    <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" {{ $attributes->merge(['class' => 'object-contain']) }}>
@else
    <svg viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="{{ config('app.name') }}" {{ $attributes }}>
        <rect x="4" y="14" width="40" height="28" rx="6" fill="#4f46e5" />
        <rect x="10" y="7" width="10" height="9" rx="3" fill="#6366f1" />
        <rect x="28" y="7" width="10" height="9" rx="3" fill="#6366f1" />
        <path d="M15 34V22l9 7 9-7v12" fill="none" stroke="#fff" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" />
    </svg>
@endif
