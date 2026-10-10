<?php

/*
 * This file is part of the zenstruck/dom package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Dom;

use Zenstruck\Dom\Node\Form\Field;
use Zenstruck\Dom\Node\Form\Field\Checkbox;
use Zenstruck\Dom\Node\Form\Field\File;
use Zenstruck\Dom\Node\Form\Field\Input;
use Zenstruck\Dom\Node\Form\Field\Radio;
use Zenstruck\Dom\Node\Form\Field\Select\Multiselect;
use Zenstruck\Dom\Node\Form\Field\Select\Option;
use Zenstruck\Dom\Node\Form\Field\Textarea;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
interface Session
{
    public function click(Node $node): void;

    public function select(Checkbox|Radio|Option $node): void;

    public function unselect(Checkbox|Multiselect $node): void;

    /**
     * @param string[] $filenames
     */
    public function attach(File $node, array $filenames): void;

    public function fill(Input|Textarea $node, string $value): void;

    /**
     * The field's current value. Typing changes a DOM property, which the markup never carries,
     * so {@see Field::value()} cannot answer this once {@see self::fill()} has been called.
     */
    public function value(Input|Textarea $node): ?string;

    /**
     * Whether the choice is currently on, for the same reason {@see self::value()} exists.
     */
    public function isSelected(Checkbox|Radio|Option $node): bool;
}
