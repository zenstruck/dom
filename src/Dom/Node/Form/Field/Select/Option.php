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
        return $this->inspector?->isSelected($this) ?? $this->isSelectedInMarkup();
    }

    public function collection(): Nodes
    {
        return $this->selector()?->availableOptions() ?? Nodes::create(new Crawler(), $this->inspector);
    }

    public function selector(): ?Select
    {
        return $this->closest('select')?->ensure(Select::class);
    }

    private function isSelectedInMarkup(): bool
    {
        $select = $this->selector();

        if (!$select instanceof Combobox) {
            return $this->attributes()->has('selected');
        }

        // a single select shows its last selected option, or its first enabled one when none is
        $element = $select->element();
        $xpath = $this->xpath();
        $selected = ($xpath->query('(.//option[@selected])[last()]', $element) ?: null)?->item(0);

        if (!$selected && (int) $select->attributes()->get('size') <= 1) {
            $selected = ($xpath->query('(.//option[not(@disabled) and not(ancestor::optgroup[@disabled])])[1]', $element) ?: null)?->item(0);
        }

        return (bool) $selected?->isSameNode($this->element());
    }
}
