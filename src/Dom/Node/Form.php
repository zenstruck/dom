<?php

/*
 * This file is part of the zenstruck/dom package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Dom\Node;

use Zenstruck\Dom\Node;
use Zenstruck\Dom\Node\Form\Button;
use Zenstruck\Dom\Node\Form\Field;
use Zenstruck\Dom\Nodes;
use Zenstruck\Dom\Selector;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @phpstan-import-type SelectorType from Selector
 */
final class Form extends Node
{
    public const SELECTOR = 'form';

    public function fields(Selector|string|callable $selector = Field::SELECTOR): Nodes
    {
        return $this->findNodesForForm($selector);
    }

    public function buttons(): Nodes
    {
        return $this->findNodesForForm(Button::SELECTOR);
    }

    public function submitButtons(): Nodes
    {
        return $this->findNodesForForm('input[type="submit"],input[type="image"],button[type="submit"],button:not([type])');
    }

    public function submitButton(): ?Button
    {
        return $this->submitButtons()->first()?->ensure(Button::class);
    }

    /**
     * Find form elements, accounting for the HTML5 `form` attribute.
     *
     * Elements with a `form` attribute pointing to another form are excluded.
     * Elements outside this form but with `form="{this-form-id}"` are included.
     */
    private function findNodesForForm(Selector|string|callable $selector): Nodes
    {
        $formId = $this->id();

        if (null === $formId || '' === $formId) {
            return $this->descendants($selector)->reduce(fn(Node $node) => $this->owns($node));
        }

        // one document-wide pass keeps document order, which positional lookups rely on
        return $this->root()
            ->descendants($selector)
            ->reduce(fn(Node $node) => $node->attributes()->is('form', $formId) || $this->owns($node))
        ;
    }

    private function owns(Node $node): bool
    {
        if ($node->attributes()->has('form')) {
            return false;
        }

        // css filtering matches descendant-or-self, which would count the form as its own field
        if ($node->element()->isSameNode($this->element())) {
            return false;
        }

        return (bool) $node->closest('form')?->element()->isSameNode($this->element());
    }
}
