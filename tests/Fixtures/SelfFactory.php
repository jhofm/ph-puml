<?php

declare(strict_types=1);

namespace Jhofm\PhPuml\Test\Fixtures;

/**
 * Class SelfFactory
 */
class SelfFactory
{
    /**
     * @return SelfFactory
     */
    public static function createSelf(): self
    {
        return new self();
    }

    /**
     * PHP8 https://wiki.php.net/rfc/static_return_type
     *
     * @return SelfFactory
     **/
    public static function createStatic(): static
    {
        return new static();
    }
}
