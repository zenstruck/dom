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

use Zenstruck\Dom\Node\Form\Field\Checkbox;
use Zenstruck\Dom\Node\Form\Field\Input;
use Zenstruck\Dom\Node\Form\Field\Radio;
use Zenstruck\Dom\Node\Form\Field\Select\Option;
use Zenstruck\Dom\Node\Form\Field\Textarea;

/**
 * Live state the markup cannot carry, such as what a browser renders or what a user has typed.
 * Return null to fall back to the markup.
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 */
interface Inspector
{
    /**
     * The node's text as a user sees it, excluding anything the browser does not render.
     */
    public function text(Node $node): ?string;

    public function isVisible(Node $node): ?bool;

    public function value(Input|Textarea $node): ?string;

    public function isSelected(Checkbox|Radio|Option $node): ?bool;
}
