<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configurarPoliticaDeContrasenas();
    }

    /**
     * Politica unica de contrasenas (RNF-03) para el alta de usuarios, el
     * cambio desde el perfil y el restablecimiento con token: minimo 8
     * caracteres con mayusculas, minusculas y numeros.
     *
     * Antes se usaba el valor por defecto de Laravel (solo 8 caracteres), lo
     * que permitia claves como "12345678".
     */
    private function configurarPoliticaDeContrasenas(): void
    {
        Password::defaults(fn () => Password::min(8)->letters()->mixedCase()->numbers());
    }
}
