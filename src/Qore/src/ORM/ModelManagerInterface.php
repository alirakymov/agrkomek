<?php

declare(strict_types=1);

namespace Qore\ORM;

use Qore\Database\Adapter\Adapter;

interface ModelManagerInterface
{
    public function getAdapter() : Adapter;
    public function getMapper(string $_entity) : Mapper\Mapper;
}
