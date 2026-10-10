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

use Symfony\Component\DomCrawler\Crawler;
use Zenstruck\Dom\Node;
use Zenstruck\Dom\Nodes;
use Zenstruck\Dom\Selector;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class Field extends Element
{
    public const SELECTOR = '[name]';

    final public function label(): ?Label
    {
        $id = $this->attributes()->get('id');

        if (null !== $id && '' !== $id && $label = $this->root()->descendants('label')->reduce(static fn(Node $label) => $id === $label->attr('for'))->first()) {
            return $label->ensure(Label::class);
        }

        // check if wrapped in a label
        return $this->closest('label')?->ensure(Label::class);
    }

    final public function name(): ?string
    {
        return $this->attributes()->get('name');
    }

    public function collection(): Nodes
    {
        if (!$name = $this->name()) {
            return Nodes::create(new Crawler(), $this->inspector);
        }

        return $this->form()?->fields(Selector::fieldForName($name)) ?? Nodes::create(new Crawler(), $this->inspector);
    }

    final public function isDisabled(): bool
    {
        if ($this->attributes()->has('disabled') || $this->closest('optgroup[disabled]')) {
            return true;
        }

        if (!$fieldset = $this->closest('fieldset[disabled]')) {
            return false;
        }

        // the fieldset's first legend stays enabled
        $legend = $fieldset->crawler()->children('legend')->getNode(0);

        return !$legend || !$this->closest('legend')?->element()->isSameNode($legend);
    }

    final public function isRequired(): bool
    {
        return $this->attributes()->has('required');
    }

    final public function isReadonly(): bool
    {
        return $this->attributes()->has('readonly');
    }

    public function value(): mixed
    {
        return $this->attributes()->get('value');
    }
}
