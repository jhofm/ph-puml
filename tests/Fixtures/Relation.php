<?php

declare(strict_types=1);

namespace Jhofm\PhPuml\Test\Fixtures;

class Foo
{

}

/**
 * Class Relation
 *
 * Conflicting short name
 */
class Relation
{
    /**
     * Relation constructor.
     *
     * @param Foo $a
     */
    public function __construct(Foo $a)
    {
    }
}
