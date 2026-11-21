@php $p = $proveedor ?? null; @endphp

<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
    <div class="md:col-span-2">
        <x-input-label for="razon_social" value="Razón social" />
        <x-text-input id="razon_social" name="razon_social" type="text" class="mt-1 block w-full"
            :value="old('razon_social', $p?->razon_social)" maxlength="150" required />
        <x-input-error :messages="$errors->get('razon_social')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="ruc" value="RUC" />
        <x-text-input id="ruc" name="ruc" type="text" inputmode="numeric" class="mt-1 block w-full"
            :value="old('ruc', $p?->ruc)" maxlength="11" required />
        <x-input-error :messages="$errors->get('ruc')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="telefono" value="Teléfono" />
        <x-text-input id="telefono" name="telefono" type="text" class="mt-1 block w-full"
            :value="old('telefono', $p?->telefono)" maxlength="20" />
        <x-input-error :messages="$errors->get('telefono')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="email" value="Correo electrónico" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
            :value="old('email', $p?->email)" maxlength="100" />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="direccion" value="Dirección" />
        <x-text-input id="direccion" name="direccion" type="text" class="mt-1 block w-full"
            :value="old('direccion', $p?->direccion)" maxlength="255" />
        <x-input-error :messages="$errors->get('direccion')" class="mt-2" />
    </div>
</div>