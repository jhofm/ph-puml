<?php

declare(strict_types=1);

namespace Jhofm\PhPuml\Test\Fixtures;

class SelfFactory
{
    public static function createSelf(): self
    {
        return new self();
    }

    //PHP8 https://wiki.php.net/rfc/static_return_type
    public static function createStatic(): static
    {
        return new static();
    }
}
