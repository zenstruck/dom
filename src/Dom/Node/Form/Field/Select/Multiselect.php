<?php

/*
 * This file is part of the zenstruck/dom package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Dom\Node\Form\Field\Select;

use Zenstruck\Dom\Node\Form\Field\Select;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Multiselect extends Select
{
    public const SELECTOR = 'select[multiple]';

    /**
     * @return string[]
     */
    public function selectedValues(): array
    {
        return \array_values(\array_filter($this->selectedOptions()->map(static fn(Option $option) => $option->value()), static fn(string $value) => '' !== $value));
    }

    /**
     * @return string[]
     */
    public function selectedTexts(): array
    {
        return \array_values(\array_filter($this->selectedOptions()->map(static fn(Option $option) => $option->text()), static fn(string $text) => '' !== $text));
    }

    /**
     * @return string[]
     */
    public function value(): array
    {
        return $this->selectedValues();
    }
}
