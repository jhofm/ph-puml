<?php

declare(strict_types=1);

namespace Jhofm\PhPuml\Relation;

use Jhofm\PhPuml\NodeParser\ClassLikeRegistry;
use Jhofm\PhPuml\Options\OptionsException;
use Jhofm\PhPuml\Options\Options;
use PhpParser\Node\Name;

class TypeGuard
{
    public function __construct(
        private readonly ClassLikeRegistry $classLikeRegistry,
        private readonly Options $options)
    {
    }

    /**
     * @throws OptionsException
     */
    public function isTypeIncluded(Name $name): bool
    {
        if ($this->options->get('include-external-types')) {
            return true;
        }
        return $this->classLikeRegistry->has($name);
    }
}
