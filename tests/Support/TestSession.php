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

use Zenstruck\Dom\Node;
use Zenstruck\Dom\Node\Form\Field\Checkbox;
use Zenstruck\Dom\Node\Form\Field\File;
use Zenstruck\Dom\Node\Form\Field\Input;
use Zenstruck\Dom\Node\Form\Field\Radio;
use Zenstruck\Dom\Node\Form\Field\Select\Multiselect;
use Zenstruck\Dom\Node\Form\Field\Select\Option;
use Zenstruck\Dom\Node\Form\Field\Textarea;
use Zenstruck\Dom\Session;

final class TestSession implements Session
{
    /** @var list<Node> */
    public array $clicked = [];

    /** @var list<Checkbox|Radio|Option> */
    public array $selected = [];

    /** @var list<Checkbox|Multiselect> */
    public array $unselected = [];

    /** @var list<array{File, list<string>}> */
    public array $attachments = [];

    /** @var list<array{Input|Textarea, string}> */
    public array $fills = [];

    public function click(Node $node): void
    {
        $this->clicked[] = $node;
    }

    public function select(Checkbox|Radio|Option $node): void
    {
        $this->selected[] = $node;
    }

    public function unselect(Checkbox|Multiselect $node): void
    {
        $this->unselected[] = $node;
    }

    public function attach(File $node, array $filenames): void
    {
        $this->attachments[] = [$node, $filenames];
    }

    public function fill(Input|Textarea $node, string $value): void
    {
        $this->fills[] = [$node, $value];
    }
}
