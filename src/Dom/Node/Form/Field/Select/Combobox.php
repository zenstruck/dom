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
final class Combobox extends Select
{
    public const SELECTOR = 'select:not([multiple])';

    public function selectedOption(): ?Option
    {
        return $this->selectedOptions()->first()?->ensure(Option::class);
    }

    public function selectedValue(): ?string
    {
        return $this->selectedOption()?->value();
    }

    public function selectedText(): ?string
    {
        return $this->selectedOption()?->text();
    }

    public function value(): ?string
    {
        return $this->selectedValue();
    }
}
