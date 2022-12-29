<?php

declare(strict_types=1);

namespace Jhofm\PhPuml\Test\Functional\Fixtures\DepPhp5;

class Foo
{
    private Bar $bar;

    public function __construct(Bar $bar)
    {
        $this->bar = $bar;
    }
}