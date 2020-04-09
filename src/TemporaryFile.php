<?php

namespace Gidato\TemporaryPath;

interface TemporaryFile
{
    public function __destruct();
    public function drop() : void;
}
