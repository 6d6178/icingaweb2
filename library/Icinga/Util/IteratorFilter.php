<?php
/* Icinga Web 2 | (c) 2017 Icinga Development Team | GPLv2+ */

namespace Icinga\Util;

use Iterator;
use OuterIterator;

/**
 * Like {@link array_filter()}, but for iterators
 */
class IteratorFilter implements OuterIterator
{
    /**
     * The filter
     *
     * @var callable
     */
    protected $callable;

    /**
     * The iterator to filter
     *
     * @var Iterator
     */
    protected $iterator;

    /**
     * Whether the current position is valid
     *
     * @var bool|null
     */
    protected $valid;

    /**
     * The current key
     *
     * @var mixed
     */
    protected $key;

    /**
     * The current value
     *
     * @var mixed
     */
    protected $value;

    /**
     * Constructor
     *
     * @param   callable    $callable   The filter
     * @param   Iterator    $iterator   The iterator to filter
     */
    public function __construct($callable, Iterator $iterator)
    {
        $this->callable = $callable;
        $this->iterator = $iterator;
    }

    public function current()
    {
        return $this->valid ? $this->value : null;
    }

    public function next()
    {
        $this->valid = null;
        $this->key = null;
        $this->value = null;

        $this->iterator->next();
    }

    public function key()
    {
        return $this->valid ? $this->key : null;
    }

    public function valid()
    {
        if ($this->valid === null) {
            for ($this->valid = false; $this->iterator->valid(); $this->iterator->next()) {
                $this->key = $this->iterator->key();
                $this->value = $this->iterator->current();

                if (call_user_func($this->callable, $this->key, $this->value)) {
                    $this->valid = true;
                    break;
                }
            }
        }

        return $this->valid;
    }

    public function rewind()
    {
        $this->valid = null;
        $this->key = null;
        $this->value = null;

        $this->iterator->rewind();
    }

    public function getInnerIterator()
    {
        return $this->iterator;
    }
}
