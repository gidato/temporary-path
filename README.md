# Gidato / Temporary-Path

Creates new temporary file or directory Gidato/Filesystem/Model objects.

If directory/file is then created, it will automatically disappear when the object is destroyed via the destruct method.

This can be called manually as well via the drop() method.

To facilitate this, each file type must be mapped to an equivalent Temporary file type.  Both BasicFile and JsonFile have been set up, but others can be added to the FileClassMapper - see examples below.

## Installation
```

composer require gidato/temporary-path

```

## Example Use

```php
<?php

use Gidato\TemporaryPath\Temporary;
use Gidato\Filesystem\Model\Base;

$base = new Base('/filesbase');
$temporary =  new Temporary($base->directory('tmp'));

// creates a TemporaryDirectory
$file = $temporary->file('test.json');
$directory = $temporary->directory();
$directory->create();
$directory->drop();

// creates a TemporaryBasicFile
$file = $temporary->file('filename');

// creates a TemporaryJsonFile
$file = $temporary->file('test.json');

// new file types - ConfigJsonFile extends JsonFile
$base->getFileTypesHandler()->addType('config.json', ConfigJsonFile::class);

// creates a TemporaryJsonFile as mapper not set up to convert ConfigJsonFile to anything else
$file = $temporary->file('config.json');

// now set up a mapper for ConfigJsonFile
$temporary->getFileClassMapper()->addType(ConfigJsonFile::class, TemporaryConfigJsonFile::class);

// now creates a TemporaryConfigJsonFile
$file = $temporary->file('config.json');

```
