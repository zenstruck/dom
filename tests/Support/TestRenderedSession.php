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

use Zenstruck\Dom\Modifier;
use Zenstruck\Dom\Node;
use Zenstruck\Dom\RenderedSession;

final class TestRenderedSession extends TestSession implements RenderedSession
{
    /** @var list<array{Node, list<Modifier>}> */
    public array $modifiedClicks = [];

    /** @var list<array{Node, list<Modifier>}> */
    public array $doubleClicked = [];

    /** @var list<array{Node, list<Modifier>}> */
    public array $rightClicked = [];

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

    public function click(Node $node, Modifier ...$modifiers): void
    {
        if (!$modifiers) {
            parent::click($node);

            return;
        }

        $this->modifiedClicks[] = [$node, $modifiers];
    }

    public function doubleClick(Node $node, Modifier ...$modifiers): void
    {
        $this->doubleClicked[] = [$node, $modifiers];
    }

    public function rightClick(Node $node, Modifier ...$modifiers): void
    {
        $this->rightClicked[] = [$node, $modifiers];
    }
}
