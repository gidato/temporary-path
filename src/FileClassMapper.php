<?php

namespace Gidato\TemporaryPath;

use Gidato\Filesystem\Model;
use Gidato\Filesystem\Model\File;
use InvalidArgumentException;

/**
 * FileClassMapper
 *
 * Keeps track of temporary file classes and how to determine them based file class passed in
 *
 * This is a singleton - not nice I know.
 * However, to make this service available in several framworks, not much else can be relied upon
 * while allowing someone to extend the file types available
 *
 */

class FileClassMapper
{
    private $map = [
        Model\JsonFile::class => JsonFile::class,
        Model\File::class => BasicFile::class,
    ];

    public function addMapping(string $from, string $to) : void
    {
        if (!class_exists($from)) {
            throw new InvalidArgumentException("Class {$from} does not exist");
        }

        if (!class_exists($to)) {
            throw new InvalidArgumentException("Class {$to} does not exist");
        }

        if (!empty($this->map[$from])) {
            throw new InvalidArgumentException("Class {$from} already set up");
        }

        $this->map[$from] = $to;
    }

    public function replaceMapping(string $from, string $to) : void
    {
        if (!class_exists($from)) {
            throw new InvalidArgumentException("Class {$from} does not exist");
        }

        if (!class_exists($to)) {
            throw new InvalidArgumentException("Class {$to} does not exist");
        }

        if (empty($this->map[$from])) {
            throw new InvalidArgumentException("Class {$from} has not been set up");
        }

        $this->map[$from] = $to;
    }


    public function map(File $file) : string
    {
        $fileClass = get_class($file);
        while (empty($this->map[$fileClass])) {
            $fileClass = get_parent_class($fileClass);
        }

        return $this->map[$fileClass];
    }
}
