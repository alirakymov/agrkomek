<?php

declare(strict_types=1);

namespace Qore\ORM\Entity;

interface EntityInterface
{
    /**
     * Get value from storage
     *
     * @param $_key
     *
     * @return mixed
     */
    public function originalGet(string $_key);

    public function originalSet(string $_key, $_value);

}
