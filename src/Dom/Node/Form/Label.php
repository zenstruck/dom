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
        $field = ($for = $this->attributes()->get('for'))
            ? $this->root()->descendant(Selector::id($for))
            : $this->descendant(Selector::css('input,select,textarea'))
        ;

        return $field instanceof Field ? $field : null;
    }
}
