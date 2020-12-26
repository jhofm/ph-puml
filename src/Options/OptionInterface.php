<?php

declare(strict_types=1);

namespace Jhofm\PhPuml\Options;

/**
 * Interface OptionInterface
 */
interface OptionInterface
{
    /** @return string */
    public function getName(): string;

    /** @return string|null */
    public function getShortName(): ?string;

    /** @return string|null */
    public function getDescription(): ?string;

    /** @return array|null */
    public function getValidValues(): ?array;

    /** @return mixed */
    public function getValue();

    /** @return bool */
    public function isArray(): bool;

    /** @return string */
    public function __toString(): string;
}
