<?php

declare(strict_types=1);

namespace Jhofm\PhPuml\Options;

use Jhofm\PhPuml\Options\OptionConfiguration as Conf;

/**
 * @property array config
 */
final class Option implements OptionInterface
{
    /**
     * Option constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function isArray(): bool
    {
        return $this->config[Conf::KEY_IS_ARRAY] ?? false;
    }

    /** @return array|string */
    public function getValue()
    {
        return $this->config[Conf::KEY_VALUE];
    }

    public function __toString(): string
    {
        return $this->getValue();
    }

    public function getValidValues(): ?array
    {
        return $this->config[Conf::KEY_VALUES];
    }

    public function getName(): string
    {
        return $this->config[Conf::KEY_NAME];
    }

    public function getShortName(): ?string
    {
        return $this->config[Conf::KEY_NAME_SHORT] ?? null;
    }

    public function getDescription(): ?string
    {
        return $this->config[Conf::KEY_DESCRIPTION] ?? null;
    }
}
