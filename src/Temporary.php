<?php

namespace Gidato\TemporaryPath;

use Ramsey\Uuid\Uuid;
use Gidato\Filesystem\Model\Directory as GidatoDirectory;

class Temporary
{
    private $tempdir;
    private $classMapper;

    public function __construct(GidatoDirectory $tempdir, ?FileClassMapper $classMapper = null)
    {
        $this->tempdir = $tempdir;
        $this->classMapper = empty($classMapper) ? new FileClassMapper() : $classMapper;
    }

    public function getFileClassMapper() : FileClassMapper
    {
        return $this->classMapper;
    }

    public function directory() : Directory
    {
        return Directory::castFrom($this->tempdir->directory(Uuid::Uuid4()));
    }

    public function file(string $extension = '') : TemporaryFile
    {
        $extension = empty($extension) ? '' : '.' . ltrim($extension, '.');
        $file = $this->tempdir->file(Uuid::Uuid4() . $extension);
        $fileClass = $this->classMapper->map($file);
        return $fileClass::castFrom($file);
    }

}
