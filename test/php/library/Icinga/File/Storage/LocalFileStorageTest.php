<?php
/* Icinga Web 2 | (c) 2017 Icinga Development Team | GPLv2+ */

namespace Tests\Icinga\File\Storage;

use ErrorException;
use Exception;
use Icinga\File\Storage\LocalFileStorage;
use Icinga\File\Storage\TemporaryLocalFileStorage;
use Icinga\Test\BaseTestCase;

class LocalFileStorageTest extends BaseTestCase
{
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        error_reporting(E_ALL | E_STRICT);

        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            if (error_reporting() === 0) {
                // Error was suppressed with the @-operator
                return false; // Continue with the normal error handler
            }

            switch ($errno) {
                case E_NOTICE:
                case E_WARNING:
                case E_STRICT:
                case E_RECOVERABLE_ERROR:
                    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
            }

            return false; // Continue with the normal error handler
        });
    }

    public function testConstructor()
    {
        new LocalFileStorage('/notreadabledirectory');
    }

    public function testGetIterator()
    {
        $lfs = new TemporaryLocalFileStorage();
        $lfs->create('foobar', 'Hello world!');
        static::assertSame(['foobar'], iterator_to_array($lfs->getIterator()));
    }

    /**
     * @expectedException \Icinga\Exception\NotReadableError
     */
    public function testGetIteratorThrowsNotReadableError()
    {
        (new LocalFileStorage('/notreadabledirectory'))->getIterator();
    }

    public function testHas()
    {
        $lfs = new TemporaryLocalFileStorage();
        static::assertFalse($lfs->has('foobar'));

        $lfs->create('foobar', 'Hello world!');
        static::assertTrue($lfs->has('foobar'));
    }

    /**
     * @expectedException \Icinga\Exception\NotReadableError
     */
    public function testHasThrowsNotReadableError()
    {
        (new LocalFileStorage('/notreadabledirectory'))->has('foobar');
    }

    public function testCreate()
    {
        $lfs = new TemporaryLocalFileStorage();
        $lfs->create('foo/bar', 'Hello world!');
        static::assertSame('Hello world!', $lfs->read('foo/bar'));
    }

    /**
     * @expectedException \Icinga\Exception\AlreadyExistsException
     */
    public function testCreateThrowsAlreadyExistsException()
    {
        $lfs = new TemporaryLocalFileStorage();
        $lfs->create('foobar', 'Hello world!');
        $lfs->create('foobar', 'Hello world!');
    }

    /**
     * @expectedException \Icinga\Exception\NotWritableError
     */
    public function testCreateThrowsNotWritableError()
    {
        (new LocalFileStorage('/notwritabledirectory'))->create('foobar', 'Hello world!');
    }

    public function testRead()
    {
        $lfs = new TemporaryLocalFileStorage();
        $lfs->create('foobar', 'Hello world!');
        static::assertSame('Hello world!', $lfs->read('foobar'));
    }

    /**
     * @expectedException \Icinga\Exception\NotFoundError
     */
    public function testReadThrowsNotFoundError()
    {
        (new TemporaryLocalFileStorage())->read('foobar');
    }

    /**
     * @expectedException \Icinga\Exception\NotReadableError
     */
    public function testReadThrowsNotReadableError()
    {
        $lfs = new TemporaryLocalFileStorage();
        $lfs->create('foobar', 'Hello world!');
        chmod($lfs->resolvePath('foobar'), 0);
        $lfs->read('foobar');
    }

    public function testUpdate()
    {
        $lfs = new TemporaryLocalFileStorage();
        $lfs->create('foobar', 'Hello world!');
        $lfs->update('foobar', 'Hello universe!');
        static::assertSame('Hello universe!', $lfs->read('foobar'));
    }

    /**
     * @expectedException \Icinga\Exception\NotFoundError
     */
    public function testUpdateThrowsNotFoundError()
    {
        (new TemporaryLocalFileStorage())->update('foobar', 'Hello universe!');
    }

    /**
     * @expectedException \Icinga\Exception\NotWritableError
     */
    public function testUpdateThrowsNotWritableError()
    {
        $lfs = new TemporaryLocalFileStorage();
        $lfs->create('foobar', 'Hello world!');
        chmod($lfs->resolvePath('foobar'), 0);
        $lfs->update('foobar', 'Hello universe!');
    }

    public function testDelete()
    {
        $lfs = new TemporaryLocalFileStorage();
        $lfs->create('foobar', 'Hello world!');
        $lfs->delete('foobar');
        static::assertFalse($lfs->has('foobar'));
    }

    /**
     * @expectedException \Icinga\Exception\NotFoundError
     */
    public function testDeleteThrowsNotFoundError()
    {
        (new TemporaryLocalFileStorage())->delete('foobar');
    }

    /**
     * @expectedException \Icinga\Exception\NotWritableError
     */
    public function testDeleteThrowsNotWritableError()
    {
        $lfs = new TemporaryLocalFileStorage();
        $lfs->create('foobar', 'Hello world!');

        $baseDir = dirname($lfs->resolvePath('foobar'));
        chmod($baseDir, 0500);

        try {
            $lfs->delete('foobar');
        } catch (Exception $e) {
            chmod($baseDir, 0700);
            throw $e;
        }

        chmod($baseDir, 0700);
    }

    public function testResolvePath()
    {
        static::assertSame(
            '/notreadabledirectory/foobar',
            (new LocalFileStorage('/notreadabledirectory'))->resolvePath('./notRelevant/../foobar')
        );
    }

    public function testResolvePathAssertExistance()
    {
        $lfs = new TemporaryLocalFileStorage();
        $lfs->create('foobar', 'Hello world!');
        $lfs->resolvePath('./notRelevant/../foobar', true);
    }

    /**
     * @expectedException \Icinga\Exception\NotReadableError
     */
    public function testResolvePathThrowsNotReadableError()
    {
        (new LocalFileStorage('/notreadabledirectory'))->resolvePath('foobar', true);
    }

    /**
     * @expectedException \Icinga\Exception\NotFoundError
     */
    public function testResolvePathThrowsNotFoundError()
    {
        (new TemporaryLocalFileStorage())->resolvePath('foobar', true);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testResolvePathThrowsInvalidArgumentException()
    {
        (new LocalFileStorage('/notreadabledirectory'))->resolvePath('../foobar');
    }
}
