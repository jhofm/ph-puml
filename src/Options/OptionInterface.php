<?php

declare(strict_types=1);

namespace Jhofm\PhPuml\Options;

/**
 * Interface OptionInterface
 */
interface OptionInterface
{
    public function getName(): string;
    public function getShortName(): ?string;
    public function getDescription(): ?string;
    public function getValidValues(): ?array;
    public function getValue();
    public function isArray(): bool;
    public function __toString(): string;
}
