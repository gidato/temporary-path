<?php

namespace Gidato\TemporaryPath;

use Ramsey\Uuid\Uuid;
use Gidato\Filesystem\Model\Directory as GidatoDirectory;

class Temporary
{
    private $tempdir;
    private $classMapper;
    private $tracking;

    public function __construct(GidatoDirectory $tempdir, ?FileClassMapper $classMapper = null)
    {
        $this->tempdir = $tempdir;
        $this->classMapper = empty($classMapper) ? new FileClassMapper() : $classMapper;
        $this->tracking = [];
    }

    public function getFileClassMapper() : FileClassMapper
    {
        return $this->classMapper;
    }

    public function directory() : Directory
    {
        return Directory::castFrom($this->tempdir->directory($this->getName()));
    }

    public function file(string $extension = '') : TemporaryFile
    {
        $file = $this->tempdir->file($this->getName($extension));
        $fileClass = $this->classMapper->map($file);
        return $fileClass::castFrom($file);
    }

    public function track() : string
    {
        $code = Uuid::Uuid4();
        $this->tracking[(string) $code] = [];
        return $code;
    }

    private function getName(?string $extension = "") : string
    {
        $extension = empty($extension) ? '' : '.' . ltrim($extension, '.');
        $name = Uuid::Uuid4() . $extension;
        $this->trackName($name);
        return $name;
    }

    private function trackName($name) : void
    {
        foreach ($this->tracking as $code => $names) {
            $this->tracking[$code][] = $name;
        }
    }

    public function dropSince(string $code) : void
    {
        foreach ($this->tracking[$code] ?? [] as $name) {
            $path = $this->tempdir->with($name);
            if ($path->exists()) {
                $path->delete(true);
            }
        }
    }

}
