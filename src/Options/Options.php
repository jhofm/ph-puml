<?php

declare(strict_types=1);

namespace Jhofm\PhPuml\Options;

use IteratorAggregate;
use JsonSerializable;
use Jhofm\PhPuml\Options\OptionConfiguration as Conf;

/**
 * Class Options
 */
final class Options implements JsonSerializable, IteratorAggregate
{
    /** @var array $options */
    private $options;

    /**
     * Options constructor.
     *
     * @param array $options
     *
     * @throws OptionsException
     */
    public function __construct(array $options)
    {
        $this->validateConfig($options);
        $this->options = $options;
    }

    /**
     * @param array $options
     *
     * @throws OptionsException
     */
    private function validateConfig(array $options)
    {
        foreach ($options as $name => $option) {
            foreach ([Conf::KEY_VALUE] as $requiredField) {
                if (!array_key_exists($requiredField, $option)) {
                    throw new OptionsException(sprintf('Option "%s" is missing required field "%s".', $name, $requiredField));
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        foreach ($this->options as $name => $option) {
            $option[Conf::KEY_NAME] = $name;
            yield $name => new Option($option);
        }
    }

    /**
     * @param array $values
     *
     * @return Options
     * @throws OptionsException
     */
    public function setValues(array $values): self
    {
        foreach ($values as $name => $value) {
            if ($this->has($name)) {
                $this->set($name, $value);
            }
        }
        return $this;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->options);
    }

    /**
     * @param string $name
     *
     * @return mixed
     * @throws OptionsException
     */
    public function __get(string $name)
    {
        return $this->get($name);
    }

    /**
     * @param string $name
     *
     * @return mixed
     * @throws OptionsException
     */
    public function get(string $name)
    {
       $this->validate($name);
        return $this->options[$name][Conf::KEY_VALUE] ?? null;
    }

    /**
     * @param string $name
     * @return Option
     * @throws OptionsException
     */
    public function getOption(string $name): OptionInterface
    {
        $this->validate($name);
        $option = $this->options[$name];
        $option[Conf::KEY_NAME] = $name;
        return new Option($option);
    }

    /**
     * @param string $name
     * @param $value
     *
     * @return Options
     * @throws OptionsException
     */
    public function __set(string $name, $value): self
    {
        return $this->set($name, $value);
    }

    /**
     * @param string $name
     * @param $value
     *
     * @return self
     * @throws OptionsException
     */
    public function set(string $name, $value): self
    {
        $this->validate($name, $value);
        $this->options[$name][Conf::KEY_VALUE] = $value;
        return $this;
    }

    /**
     * [@inheritdoc}
     */
    public function jsonSerialize()
    {
        return $this->options;
    }

    /**
     * @param string $name
     * @param null $value
     *
     * @throws OptionsException
     */
    private function validate(string $name, $value = null)
    {
        if (!$this->has($name)) {
            throw new OptionsException(sprintf('Unknown option "%s".', $name));
        }
        if ($value !== null
            && array_key_exists(Conf::KEY_VALUES, $this->options[$name])
            && !in_array($value, $this->options[$name][Conf::KEY_VALUES])
        ) {
            throw new OptionsException(sprintf('Value "%s" is not valid for option "%s".', $value, $name));
        }
    }
}
