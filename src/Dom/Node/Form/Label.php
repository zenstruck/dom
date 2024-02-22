<?php

/*
 * This file is part of the zenstruck/dom package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Dom\Node\Form;

use Zenstruck\Dom\Selector;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Label extends Element
{
    public function field(): ?Field
    {
        if ($for = $this->attributes()->get('for')) {
            return $this->form()?->descendants(Selector::id($for))->first()?->ensure(Field::class);
        }

        // check if wrapping field
        return $this->descendants(Field::class)->first()?->ensure(Field::class);
    }
}
