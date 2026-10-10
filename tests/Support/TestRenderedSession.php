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
use Zenstruck\Dom\RenderedSession;

final class TestRenderedSession extends TestSession implements RenderedSession
{
    public function __construct(private string $text = 'rendered text', private bool $visible = false)
    {
    }

    public function text(Node $node): string
    {
        return $this->text;
    }

    public function isVisible(Node $node): bool
    {
        return $this->visible;
    }
}
