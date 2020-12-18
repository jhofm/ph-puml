<?php

declare(strict_types=1);

namespace Jhofm\PhPuml\Options;

use JsonSerializable;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class Options
 */
final class Options implements JsonSerializable
{
    public const OPTION_EXCLUDE = 'exclude';
    public const VALUE_EXCLUDE_VENDOR = '~(?:^|/)vendor/~';

    /** @var array $defaults */
    private static $defaults = [
        self::OPTION_EXCLUDE => [self::VALUE_EXCLUDE_VENDOR]
    ];

    private static $flags = [
        self::OPTION_EXCLUDE => InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY
    ];

    /** @var array $options */
    protected $options;

    /**
     * @param array $options
     * @return Options
     */
    public static function fromArray(array $options): Options
    {
        return new self(
            array_merge(self::$defaults, $options)
        );
    }

    /**
     * @return array
     */
    public static function getDefaults(): array
    {
        return self::$defaults;
    }

    /**
     * @return array
     */
    public static function getFlags(string $option): ?int
    {
        return self::$flags[$option] ?? null;
    }

    /**
     * Options constructor.
     * @param array $options
     */
    private function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * @param string $name
     *
     * @return mixed
     * @throws OptionsException
     */
    public function __get(string $name)
    {
        if (!array_key_exists($name, $this->options)) {
            throw new OptionsException(sprintf('Unknown option "%s".', $name));
        }
        return $this->options[$name];
    }

    /**
     * [@inheritdoc}
     */
    public function jsonSerialize()
    {
        return $this->options;
    }
}
