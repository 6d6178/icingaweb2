<?php
/* Icinga Web 2 | (c) 2017 Icinga Development Team | GPLv2+ */

namespace Tests\Icinga\File\Storage;

use Icinga\File\Storage\TemporaryLocalFileStorage;
use Icinga\Test\BaseTestCase;

class TemporaryLocalFileStorageTest extends BaseTestCase
{
    public function testConstructorAndDestructor()
    {
        new TemporaryLocalFileStorage();
    }
}
