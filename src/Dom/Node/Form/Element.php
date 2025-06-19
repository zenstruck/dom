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

use Zenstruck\Dom\Node;
use Zenstruck\Dom\Node\Form;
use Zenstruck\Dom\Selector;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class Element extends Node
{
    final public function form(): ?Form
    {
        if (\is_string($formId = $this->attributes()->get('form'))) {
            $form = $this->ancestors()->last()?->descendant(Selector::id($formId))?->ensure(Form::class);
        }

        return $form ?? $this->closest('form')?->ensure(Form::class);
    }
}
