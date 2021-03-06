<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Gidato\Filesystem\Memory;
use Gidato\Filesystem\Model\Base;
use Gidato\Filesystem\Model\Directory;
use Gidato\Filesystem\Model\File;
use Gidato\Filesystem\Model\JsonFile;
use Gidato\TemporaryPath\Temporary;
use Gidato\TemporaryPath\FileClassMapper;

class TemporaryTest extends TestCase
{
    protected $filesystem;
    protected $base;
    protected $temporary;

    public function setUp() : void
    {
        $this->filesystem = new Memory();
        $this->filesystem->mkdir('/test/temp', 0777, true);
        $this->base = new Base('/test', $this->filesystem);
        $this->temporary = new Temporary($this->base->directory('temp'));
    }

    public function testNewDirectory()
    {
        $directory = $this->temporary->directory();
        $this->assertInstanceOf(Directory::class, $directory);
        $this->assertEquals('/test/temp', $directory->parent->fullPath);
        $this->assertNotEmpty($directory->name);
    }

    public function testDirectoryCanBeDroppedAfterCreationAndAllFilesDisappear()
    {
        $directory = $this->temporary->directory();
        $directory->create();
        $path = $directory->fullPath;

        $directory->file('test')->create();

        $this->assertTrue($this->filesystem->file_exists($path));
        $this->assertTrue($this->filesystem->is_dir($path));

        $directory->drop();

        $this->assertFalse($this->filesystem->file_exists($path));
    }

    public function testDirectoryDisappearsOnDestruct()
    {
        $directory = $this->temporary->directory();
        $directory->create();
        $path = $directory->fullPath;
        $this->assertTrue($this->filesystem->file_exists($path));
        $this->assertTrue($this->filesystem->is_dir($path));

        unset($directory);

        $this->assertFalse($this->filesystem->file_exists($path));
    }

    public function testNewFileNoExtension()
    {
        $file = $this->temporary->file();
        $this->assertInstanceOf(File::class, $file);
        $this->assertEquals('/test/temp', $file->parent->fullPath);
        $this->assertNotEmpty($file->name);
        $this->assertFalse(strpos($file->name,'.'));
    }

    public function testNewFileWithExtension()
    {
        $file = $this->temporary->file('txt');
        $this->assertInstanceOf(File::class, $file);
        $this->assertEquals('/test/temp', $file->parent->fullPath);
        $this->assertNotEmpty($file->name);
        $this->assertEquals('.txt', substr($file->name, -4));

        $file = $this->temporary->file('.txt');
        $this->assertInstanceOf(File::class, $file);
        $this->assertEquals('/test/temp', $file->parent->fullPath);
        $this->assertNotEmpty($file->name);
        $this->assertEquals('.txt', substr($file->name, -4));

        $file = $this->temporary->file('.json');
        $this->assertInstanceOf(JsonFile::class, $file);
        $this->assertEquals('/test/temp', $file->parent->fullPath);
        $this->assertNotEmpty($file->name);
        $this->assertEquals('.json', substr($file->name, -5));
    }

    public function testNewFileCanBeDropped()
    {
        $file = $this->temporary->file();
        $file->create();
        $path = $file->fullPath;

        $this->assertTrue($this->filesystem->file_exists($path));
        $this->assertTrue($this->filesystem->is_file($path));

        $file->drop();

        $this->assertFalse($this->filesystem->file_exists($path));

        /* and a json file */
        $file = $this->temporary->file('.json');
        $file->create();
        $path = $file->fullPath;

        $this->assertTrue($this->filesystem->file_exists($path));
        $this->assertTrue($this->filesystem->is_file($path));

        $file->drop();

        $this->assertFalse($this->filesystem->file_exists($path));
    }

    public function testNewFileAutomaticallDroppedOnDesctuction()
    {
        $file = $this->temporary->file();
        $file->create();
        $path = $file->fullPath;

        $this->assertTrue($this->filesystem->file_exists($path));
        $this->assertTrue($this->filesystem->is_file($path));

        unset($file);

        $this->assertFalse($this->filesystem->file_exists($path));

        /* and a json file */
        $file = $this->temporary->file('.json');
        $file->create();
        $path = $file->fullPath;

        $this->assertTrue($this->filesystem->file_exists($path));
        $this->assertTrue($this->filesystem->is_file($path));

        unset($file);

        $this->assertFalse($this->filesystem->file_exists($path));
    }

    public function testGettingClassMapper()
    {
        $this->assertInstanceOf(FileClassMapper::class, $this->temporary->getFileClassMapper());
    }

    public function testInitialisingWithClassMapper()
    {
        $mapper = new FileClassMapper();
        $temporary = new Temporary($this->base->directory('temp'), $mapper);
        $this->assertSame($mapper, $temporary->getFileClassMapper());
    }

    public function testTrackChanges()
    {
        $code1 = $this->temporary->track();
        $d1 = $this->temporary->directory();
        $d1->create();
        $code2 = $this->temporary->track();
        $d2 = $this->temporary->directory();
        $d2->create();
        $d3 = $this->temporary->directory();
        $f1 = $this->temporary->file();
        $f1->create();
        $f2 = $this->temporary->file();
        $f2->create();
        $f2->drop();

        $this->assertTrue($d1->exists());
        $this->assertTrue($d2->exists());
        $this->assertFalse($d3->exists());
        $this->assertTrue($f1->exists());
        $this->assertFalse($f2->exists());

        // should leave only those since code2 that still exists;
        $this->temporary->dropSince($code2);

        $this->assertTrue($d1->exists());
        $this->assertFalse($d2->exists());
        $this->assertFalse($d3->exists());
        $this->assertFalse($f1->exists());
        $this->assertFalse($f2->exists());

        // should delete all since code1 that still exist
        $this->temporary->dropSince($code1);
        $this->assertFalse($d1->exists());
        $this->assertFalse($d2->exists());
        $this->assertFalse($d3->exists());
        $this->assertFalse($f1->exists());
        $this->assertFalse($f2->exists());

    }

}
