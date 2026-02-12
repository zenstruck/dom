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
    /**
     * Find the form this element belongs to.
     *
     * Supports the HTML5 `form` attribute which allows elements
     * to be associated with a form outside their DOM hierarchy.
     */
    final public function form(): ?Form
    {
        $formId = $this->attributes()->get('form');

        if (\is_string($formId) && '' !== $formId) {
            $form = $this->root()->descendant(Selector::id($formId));

            if ($form instanceof Form) {
                return $form;
            }
        }

        return $this->closest('form')?->ensure(Form::class);
    }
}
