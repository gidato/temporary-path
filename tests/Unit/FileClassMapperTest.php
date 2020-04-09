<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Gidato\TemporaryPath\FileClassMapper;
use Gidato\TemporaryPath\BasicFile as TemporaryBasicFile;
use Gidato\TemporaryPath\JsonFile as TemporaryJsonFile;
use Gidato\TemporaryPath\TemporaryFile;
use Gidato\Filesystem\Memory;
use Gidato\Filesystem\Model\Base;
use Gidato\Filesystem\Model\File;
use Gidato\Filesystem\Model\BasicFile;
use Gidato\Filesystem\Model\JsonFile;
use InvalidArgumentException;

class FileClassMapperTest extends TestCase
{
    protected $filesystem;
    protected $base;
    protected $mapper;

    public function setUp() : void
    {
        $this->filesystem = new Memory();
        $this->base = new Base('/test', $this->filesystem);
        $this->mapper = new FileClassMapper();
    }

    public function testClassForBasicFile()
    {
        $testFile = new BasicFile($this->base, 'filename');
        $this->assertEquals(TemporaryBasicFile::class, $this->mapper->map($testFile));
    }

    public function testClassForJsonFile()
    {
        $testFile = new JsonFile($this->base, 'filename.json');
        $this->assertEquals(TemporaryJsonFile::class, $this->mapper->map($testFile));
    }

    public function testClassForUnknownFileType()
    {
        $testFile = new ExcelFile;
        $this->assertEquals(TemporaryBasicFile::class, $this->mapper->map($testFile));
    }

    public function testAddingNewClassMapping()
    {
        $mapper = new FileClassMapper();
        $mapper->addMapping(ExcelFile::class, TemporaryExcelFile::class);
        $testFile = new ExcelFile;
        $this->assertEquals(TemporaryExcelFile::class, $mapper->map($testFile));
    }

    public function testAddingClassDoesntExist()
    {
        $mapper = new FileClassMapper();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Class unknown does not exist");
        $mapper->addMapping('unknown', TemporaryExcelFile::class);
    }

    public function testAddingMappedClassDoesntExist()
    {
        $mapper = new FileClassMapper();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Class unknown-to does not exist");
        $mapper->addMapping(ExcelFile::class, 'unknown-to');
    }

    public function testAddingClassAlreadySetUp()
    {
        $mapper = new FileClassMapper();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Class Gidato\Filesystem\Model\File already set up");
        $mapper->addMapping(File::class, TemporaryBasicFile::class);
    }

    public function testReplacingClassDoesntExist()
    {
        $mapper = new FileClassMapper();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Class unknown does not exist");
        $mapper->replaceMapping('unknown', TemporaryExcelFile::class);
    }

    public function testReplacingMappedClassDoesntExist()
    {
        $mapper = new FileClassMapper();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Class unknown-to does not exist");
        $mapper->replaceMapping(ExcelFile::class, 'unknown-to');
    }

    public function testReplacingClassNotSetUp()
    {
        $mapper = new FileClassMapper();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Class Tests\Unit\ExcelFile has not been set up");
        $mapper->replaceMapping(ExcelFile::class, TemporaryExcelFile::class);
    }

    public function testReplacingBasic()
    {
        $mapper = new FileClassMapper();
        $mapper->replaceMapping(File::class, TemporaryExcelFile::class);

        $testFile = new BasicFile($this->base, 'filename');
        $this->assertEquals(TemporaryExcelFile::class, $mapper->map($testFile));

        /* not set up, but should still use the new basic file */
        $testFile = new ExcelFile();
        $this->assertEquals(TemporaryExcelFile::class, $mapper->map($testFile));
    }

    public function testWithInheritedClassNotSetUp()
    {
        $testFile = new ConfigFile($this->base, 'filename.json');
        $this->assertEquals(TemporaryJsonFile::class, $this->mapper->map($testFile));
    }
}

class ExcelFile extends File
{
}

class TemporaryExcelFile extends ExcelFile implements TemporaryFile
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

class ConfigFile extends JsonFile
{

}
