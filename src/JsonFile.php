<?php

namespace Gidato\TemporaryPath;

use Gidato\Filesystem\Model\JsonFile as GidatoJsonFile;

class JsonFile extends GidatoJsonFile implements TemporaryFile
{
    public function __destruct()
    {
        $this->drop();
    }

    public function drop() : void
    {
        $this->delete(true);
    }
}
