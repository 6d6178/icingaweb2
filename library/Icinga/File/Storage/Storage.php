<?php
/* Icinga Web 2 | (c) 2017 Icinga Development Team | GPLv2+ */

namespace Icinga\File\Storage;

use Icinga\Exception\NotFoundError;
use Icinga\Exception\NotReadableError;
use Icinga\Exception\NotWritableError;

abstract class Storage
{
    /**
     * Get all existing files by name
     *
     * @return  File[]
     *
     * @throws  NotReadableError
     */
    public function getFiles()
    {
    }

    /**
     * Get a file by name
     *
     * @param   string  $name
     *
     * @return  File
     *
     * @throws  NotReadableError
     * @throws  NotFoundError
     */
    public function getFile($name)
    {
    }

    /**
     * Get or create a file by name
     *
     * A fresh file will be persisted once its content has been updated.
     *
     * @param   string  $name
     *
     * @return  File
     */
    public function getOrCreateFile($name)
    {
    }

    /**
     * Delete a file by name
     *
     * @param   string  $name
     *
     * @return  $this
     *
     * @throws  NotReadableError
     * @throws  NotWritableError
     * @throws  NotFoundError
     */
    public function deleteFile($name)
    {
    }
}
