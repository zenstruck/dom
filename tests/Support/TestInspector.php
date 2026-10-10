<?php

/*
 * This file is part of the zenstruck/dom package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Dom\Tests\Support;

use Zenstruck\Dom\Inspector;
use Zenstruck\Dom\Node;
use Zenstruck\Dom\Node\Form\Field\Checkbox;
use Zenstruck\Dom\Node\Form\Field\Input;
use Zenstruck\Dom\Node\Form\Field\Radio;
use Zenstruck\Dom\Node\Form\Field\Select\Option;
use Zenstruck\Dom\Node\Form\Field\Textarea;

final class TestInspector implements Inspector
{
    public function __construct(
        private ?string $value = null,
        private ?bool $selected = null,
        private ?string $text = null,
        private ?bool $visible = null,
    ) {
    }

    public function text(Node $node): ?string
    {
        return $this->text;
    }

    public function isVisible(Node $node): ?bool
    {
        return $this->visible;
    }

    public function value(Input|Textarea $node): ?string
    {
        return $this->value;
    }

    public function isSelected(Checkbox|Radio|Option $node): ?bool
    {
        return $this->selected;
    }
}
