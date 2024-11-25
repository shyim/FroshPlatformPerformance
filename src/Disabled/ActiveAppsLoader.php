<?php

namespace Frosh\Performance\Disabled;

use Shopware\Core\Framework\App\ActiveAppsLoader as CoreService;

class ActiveAppsLoader extends CoreService
{
    /**
     * @noinspection MagicMethodsValidityInspection
     * @noinspection PhpMissingParentConstructorInspection
     */
    public function __construct()
    {
    }

    public function getActiveApps(): array
    {
        return [];
    }

    public function reset(): void
    {
    }
}
