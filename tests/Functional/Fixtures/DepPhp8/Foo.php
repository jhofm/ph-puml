<?php

declare(strict_types=1);

namespace Jhofm\PhPuml\Test\Functional\Fixtures\DepPhp8;

class Foo
{
    public function __construct(private Bar $bar)
    {

    }
}
