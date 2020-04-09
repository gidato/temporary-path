<?php

namespace Gidato\TemporaryPath;

use Gidato\Filesystem\Model\BasicFile as GidatoBasicFile;

class BasicFile extends GidatoBasicFile implements TemporaryFile
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
