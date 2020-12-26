<?php

declare(strict_types=1);

namespace Jhofm\PhPuml\Options;

use Jhofm\PhPuml\Options\OptionConfiguration as Conf;

/**
 * Class Option
 */
final class Option implements OptionInterface
{
    /** @var array */
    private $config;

    /**
     * Option constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /** @return bool */
    public function isArray(): bool
    {
        return ($this->config[Conf::KEY_IS_ARRAY] ?? false);
    }

    /** @return array|string */
    public function getValue()
    {
        return $this->config[Conf::KEY_VALUE];
    }

    /** @return string */
    public function __toString(): string
    {
        return $this->getValue();
    }

    /** @return array|null */
    public function getValidValues(): ?array
    {
        return $this->config[Conf::KEY_VALUES];
    }

    /** @return string|null */
    public function getName(): string
    {
        return $this->config[Conf::KEY_NAME];
    }

    /** @return string|null */
    public function getShortName(): ?string
    {
        return ($this->config[Conf::KEY_NAME_SHORT] ?? null);
    }

    /** @return string|null */
    public function getDescription(): ?string
    {
        return ($this->config[Conf::KEY_DESCRIPTION] ?? null);
    }
}
