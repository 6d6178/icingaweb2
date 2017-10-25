<?php
/* Icinga Web 2 | (c) 2017 Icinga Development Team | GPLv2+ */

namespace Icinga\File\Storage;

use Icinga\Exception\AlreadyExistsException;
use Icinga\Exception\NotFoundError;
use Icinga\Exception\NotReadableError;
use Icinga\Exception\NotWritableError;
use IteratorAggregate;
use Traversable;

interface StorageInterface extends IteratorAggregate
{
    /**
     * Iterate over all existing files' paths
     *
     * @return  Traversable
     *
     * @throws  NotReadableError
     */
    public function getIterator();

    /**
     * Return whether the given file exists
     *
     * @param   string  $path
     *
     * @return  bool
     *
     * @throws  NotReadableError
     */
    public function has($path);

    /**
     * Create the given file with the given content
     *
     * @param   string  $path
     * @param   mixed   $content
     *
     * @return  $this
     *
     * @throws  AlreadyExistsException
     * @throws  NotWritableError
     */
    public function create($path, $content);

    /**
     * Load the content of the given file
     *
     * @param   string  $path
     *
     * @return  mixed
     *
     * @throws  NotReadableError
     * @throws  NotFoundError
     */
    public function read($path);

    /**
     * Overwrite the given file with the given content
     *
     * @param   string  $path
     * @param   mixed   $content
     *
     * @return  $this
     *
     * @throws  NotFoundError
     * @throws  NotWritableError
     */
    public function update($path, $content);

    /**
     * Delete the given file
     *
     * @param   string  $path
     *
     * @return  $this
     *
     * @throws  NotFoundError
     * @throws  NotWritableError
     */
    public function delete($path);

    /**
     * Get the absolute path to the given file
     *
     * @param   string  $path
     *
     * @return  string
     *
     * @throws  NotReadableError
     * @throws  NotFoundError
     */
    public function resolvePath($path);
}
