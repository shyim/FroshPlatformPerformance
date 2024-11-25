<?php

namespace Frosh\Performance\Disabled;

class HtmlSanitizer extends \Shopware\Core\Framework\Util\HtmlSanitizer
{
    public function sanitize(string $text, ?array $options = [], bool $override = false, ?string $field = null): string
    {
        return $text;
    }
}
