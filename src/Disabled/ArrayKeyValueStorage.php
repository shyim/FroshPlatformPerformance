<?php

namespace Frosh\Performance\Disabled;

use Shopware\Core\Framework\Adapter\Storage\AbstractKeyValueStorage;

class ArrayKeyValueStorage extends AbstractKeyValueStorage
{
    public function has(string $key): bool
    {
        return false;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $default;
    }

    public function set(string $key, mixed $value): void
    {
    }

    public function remove(string $key): void
    {
    }
}
