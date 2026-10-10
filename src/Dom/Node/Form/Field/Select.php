<?php

/*
 * This file is part of the zenstruck/dom package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Dom\Node\Form\Field;

use Zenstruck\Dom\Node;
use Zenstruck\Dom\Node\Form\Field;
use Zenstruck\Dom\Node\Form\Field\Select\Option;
use Zenstruck\Dom\Nodes;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class Select extends Field
{
    public const SELECTOR = 'select';

    final public function availableOptions(): Nodes
    {
        return $this->descendants('option');
    }

    final public function optionMatching(string $value): ?Option
    {
        $value = \mb_strtolower($value);

        // todo use xpath?
        return $this->optionEqualTo($value) ?? $this->optionContaining($value);
    }

    /**
     * @return string[]
     */
    final public function availableValues(): array
    {
        return \array_values(\array_filter($this->availableOptions()->map(static fn(Option $option) => $option->value()), static fn(string $value) => '' !== $value));
    }

    final public function selectedOptions(): Nodes
    {
        $indexes = $this->inspector?->selectedIndexes($this);

        if (null === $indexes) {
            return $this->availableOptions()->reduce(static fn(Node $option) => $option->ensure(Option::class)->isSelected());
        }

        return $this->availableOptions()->reduce(static fn(Node $option, int $index) => \in_array($index, $indexes, true));
    }

    final public function isMultiple(): bool
    {
        return $this->attributes()->has('multiple');
    }

    private function optionEqualTo(string $value): ?Option
    {
        foreach ($this->availableOptions() as $option) {
            $option = $option->ensure(Option::class);

            if ($value === \mb_strtolower($option->value()) || $value === \mb_strtolower($option->text())) {
                return $option;
            }
        }

        return null;
    }

    private function optionContaining(string $value): ?Option
    {
        foreach ($this->availableOptions() as $option) {
            $option = $option->ensure(Option::class);

            if (\str_contains(\mb_strtolower($option->value()), $value) || \str_contains(\mb_strtolower($option->text()), $value)) {
                return $option;
            }
        }

        return null;
    }
}
