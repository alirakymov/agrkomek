<?php

declare(strict_types=1);

namespace Qore\DealingManager;

trait MergeTrait
{
    /**
     * current
     *
     * @var mixed
     */
    private $current = null;

    /**
     * preserveNumericKeys
     *
     * @var mixed
     */
    protected $preserveNumericKeys = false;

    /**
     * merge
     *
     * @param mixed $_object
     */
    public function merge($_object)
    {
        if (is_null($this->current)) {
            $this->current = $this;
        }

        $preserveNumericKeys = false;
        if ($_object instanceof ResultInterface || $_object instanceof ModelInterface) {
            $preserveNumericKeys = $_object->preserveNumericKeys();
        }

        foreach ($_object as $key => $value) {
            if (! is_int($key) || is_int($key) && $preserveNumericKeys) {
                $this->mergeAction($key, $value, $preserveNumericKeys);
            } elseif (is_int($key)) {
                $this->current[] = $value;
            }
        }

        return $this;
    }

    /**
     * mergeAction
     *
     * @param mixed $_key
     * @param mixed $_value
     * @param mixed $_preserveNumericKeys
     */
    protected function mergeAction($_key, $_value, $_preserveNumericKeys)
    {
        if (
            isset($this->current[$_key])
            && $this->isMergeable($this->current[$_key])
            && $this->isMergeable($_value)
        ) {

            if (is_array($_value)) {
                $_value = new static($_value);
                $_value->preserveNumericKeys($_preserveNumericKeys);
            }

            $currentMergeTarget = $this->current;
            $this->current = $this->current[$_key];
            $this->merge($_value);
            $this->current = $currentMergeTarget;

        } else {
            $this->current[$_key] = $_value;
        }
    }

    /**
     * isMergeable
     *
     * @param mixed $_value
     */
    public function isMergeable($_value)
    {
        return is_array($_value) || $_value instanceof ResultInterface || $_value instanceof ModelInterface;
    }

    /**
     * preserveNumericKeys
     *
     * @param bool $_value
     */
    public function preserveNumericKeys(bool $_value = null)
    {
        if (is_null($_value)) {
            return $this->preserveNumericKeys;
        }

        $this->preserveNumericKeys = $_value;
        return $this;
    }

}
