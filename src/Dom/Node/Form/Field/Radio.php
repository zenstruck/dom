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

use Zenstruck\Dom\Node\Form\Field;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Radio extends Field
{
    public const SELECTOR = 'input[type="radio"]';

    public function isSelected(): bool
    {
        return $this->attributes()->has('checked');
    }

    public function selected(): ?self
    {
        foreach ($this->collection() as $radio) {
            // hack for panther
            $radio = $radio->ensure(self::class);

            if ($radio->isSelected()) {
                return $radio;
            }
        }

        return null;
    }

    public function selectedValue(): ?string
    {
        return $this->selected()?->value();
    }
}
