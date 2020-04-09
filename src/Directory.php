<?php

namespace Gidato\TemporaryPath;

use Gidato\Filesystem\Model\Directory as GidatoDirectory;

class Directory extends GidatoDirectory
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
