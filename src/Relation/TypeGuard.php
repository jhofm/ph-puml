<?php

declare(strict_types=1);

namespace Jhofm\PhPuml\Relation;

use Jhofm\PhPuml\NodeParser\ClassLikeRegistry;
use Jhofm\PhPuml\Options\Options;
use PhpParser\Node\Name;

/**
 * Class TypeGuard
 */
class TypeGuard
{
    /** @var ClassLikeRegistry */
    private $classLikeRegistry;
    /** @var Options */
    private $options;

    /**
     * TypeGuard constructor.
     *
     * @param ClassLikeRegistry $classLikeRegistry
     * @param Options $options
     */
    public function __construct(ClassLikeRegistry $classLikeRegistry, Options $options)
    {
        $this->classLikeRegistry = $classLikeRegistry;
        $this->options = $options;
    }

    /**
     * @param Name $name
     *
     * @return bool
     * @throws \Jhofm\PhPuml\Options\OptionsException
     */
    public function isTypeIncluded(Name $name)
    {
        if ($this->options->get('include-external-types')) {
            return true;
        }
        return $this->classLikeRegistry->has($name);
    }
}
