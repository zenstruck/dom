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

/**
 * A session driving a real browser, which can answer from what is rendered. The markup alone
 * cannot: a stylesheet hides an element with no inline style of its own to detect.
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 */
interface RenderedSession extends Session
{
    /**
     * The node's text as a user sees it, excluding anything the browser does not render.
     */
    public function text(Node $node): string;

    public function isVisible(Node $node): bool;
}
