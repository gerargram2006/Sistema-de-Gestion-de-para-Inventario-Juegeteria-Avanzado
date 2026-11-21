<?php

namespace App\Console\Commands;

use Illuminate\Foundation\Console\ServeCommand as BaseServeCommand;

/**
 * Extiende el comando `serve` de Laravel para activar OPcache en el servidor
 * PHP integrado (`-d opcache.enable_cli=1`), evitando recompilar cientos de
 * archivos en cada petición cuando `opcache.enable_cli=0` en php.ini.
 */
class ServeCommand extends BaseServeCommand
{
    /**
     * Get the full server command.
     *
     * @return array
     */
    protected function serverCommand()
    {
        $command = parent::serverCommand();

        array_splice($command, 1, 0, ['-d', 'opcache.enable_cli=1']);

        return $command;
    }
}
