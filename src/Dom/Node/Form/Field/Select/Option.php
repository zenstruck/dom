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

use Symfony\Component\DomCrawler\Crawler;
use Zenstruck\Dom\Node\Form\Field;
use Zenstruck\Dom\Node\Form\Field\Select;
use Zenstruck\Dom\Nodes;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Option extends Field
{
    public const SELECTOR = 'option';

    public function value(): string
    {
        return $this->attributes()->get('value') ?? $this->text();
    }

    public function isSelected(): bool
    {
        return $this->attributes()->has('selected');
    }

    public function collection(): Nodes
    {
        return $this->selector()?->availableOptions() ?? Nodes::create(new Crawler(), $this->session);
    }

    public function selector(): ?Select
    {
        return $this->closest('select')?->ensure(Select::class);
    }

    public function select(): void
    {
        $this->ensureSession()->select($this);
    }
}
