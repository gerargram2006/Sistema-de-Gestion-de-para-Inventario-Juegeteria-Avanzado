<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProveedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        // El acceso ya lo restringe el middleware de rol en las rutas
        return true;
    }

    public function rules(): array
    {
        return [
            'razon_social' => ['required', 'string', 'max:150'],
            'ruc' => [
                'required',
                'regex:/^(10|15|17|20)\d{9}$/',
                Rule::unique('proveedores', 'ruc')->ignore($this->route('proveedor')),
            ],
            'telefono' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'email' => ['nullable', 'email', 'max:100'],
            'direccion' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'razon_social.required' => 'Ingrese la razón social del proveedor.',
            'ruc.required' => 'Ingrese el RUC.',
            'ruc.regex' => 'El RUC debe tener 11 dígitos y empezar con 10, 15, 17 o 20.',
            'ruc.unique' => 'Ya existe un proveedor con ese RUC.',
            'telefono.regex' => 'El teléfono solo puede contener números, espacios y los signos + - ( ).',
            'email.email' => 'Ingrese un correo válido.',
        ];
    }
}
