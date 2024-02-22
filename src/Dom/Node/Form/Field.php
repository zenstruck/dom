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

        if ($id && $label = $this->form()?->descendent(Selector::css(\sprintf('label[for="%s"]', $id)))) {
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
            return Nodes::create(new Crawler(), $this->session);
        }

        return $this->form()?->descendents(Selector::field($name)) ?? Nodes::create(new Crawler(), $this->session);
    }

    final public function isDisabled(): bool
    {
        return $this->attributes()->has('disabled');
    }

    public function value(): mixed
    {
        return $this->attributes()->get('value');
    }
}
