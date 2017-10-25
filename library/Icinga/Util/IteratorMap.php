<?php
/* Icinga Web 2 | (c) 2017 Icinga Development Team | GPLv2+ */

namespace Icinga\Util;

use Iterator;

/**
 * Like {@link array_map()}, but for iterators
 */
class IteratorMap implements Iterator
{
    /**
     * The callable to apply
     *
     * @var callable
     */
    protected $callable;

    /**
     * The iterators to iterate over
     *
     * @var Iterator[]
     */
    protected $iterators;

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
     * @param   callable    $callable   The callable to apply
     * @param   Iterator    $iterator1  The first iterator to iterate over
     */
    public function __construct($callable, Iterator $iterator1)
    {
        $this->callable = $callable;
        $this->iterators = array_values(array_slice(func_get_args(), 1));
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

        foreach ($this->iterators as $iterator) {
            $iterator->next();
        }
    }

    public function key()
    {
        return $this->valid ? $this->key : null;
    }

    public function valid()
    {
        if ($this->valid === null) {
            $args = array();
            foreach ($this->iterators as $iterator) {
                if (! $iterator->valid()) {
                    $this->valid = false;
                    return false;
                }

                $args[] = $iterator->key();
                $args[] = $iterator->current();
            }

            $this->valid = true;
            list($this->key, $this->value) = call_user_func_array($this->callable, $args);
        }

        return $this->valid;
    }

    public function rewind()
    {
        $this->valid = null;
        $this->key = null;
        $this->value = null;

        foreach ($this->iterators as $iterator) {
            $iterator->rewind();
        }
    }
}
