<?php
/* Icinga Web 2 | (c) 2017 Icinga Development Team | GPLv2+ */

namespace Icinga\File\Storage;

use Icinga\Exception\AlreadyExistsException;
use Icinga\Exception\NotFoundError;
use Icinga\Exception\NotReadableError;
use Icinga\Exception\NotWritableError;

interface StorageInterface
{
    /**
     * Get all existing files' paths
     *
     * @return  string[]
     *
     * @throws  NotReadableError
     */
    public function getFiles();

    /**
     * Create the given file with the given content
     *
     * @param   string  $path
     * @param   string  $content
     *
     * @return  $this
     *
     * @throws  NotReadableError
     * @throws  AlreadyExistsException
     * @throws  NotWritableError
     */
    public function createFile($path, $content);

    /**
     * Load the content of the given file
     *
     * @param   string  $path
     *
     * @return  string
     *
     * @throws  NotReadableError
     * @throws  NotFoundError
     */
    public function readFile($path);

    /**
     * Overwrite the given file with the given content
     *
     * @param   string  $path
     * @param   string  $content
     *
     * @return  $this
     *
     * @throws  NotReadableError
     * @throws  NotFoundError
     * @throws  NotWritableError
     */
    public function updateFile($path, $content);

    /**
     * Delete the given file
     *
     * @param   string  $path
     *
     * @return  $this
     *
     * @throws  NotReadableError
     * @throws  NotFoundError
     * @throws  NotWritableError
     */
    public function deleteFile($path);

    /**
     * Get the absolute path to the given file in the local file system
     *
     * If the storage is not local, the file will be downloaded to a temporary local file first.
     *
     * @return  string
     *
     * @throws  NotReadableError
     * @throws  NotFoundError
     * @throws  NotWritableError
     */
    public function getLocalPath();
}
