<?php

/*
 * This file is part of the zenstruck/dom package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Dom\Tests\Node\Form\Field;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Zenstruck\Dom;
use Zenstruck\Dom\Node\Form\Field\Textarea;
use Zenstruck\Dom\Selector;
use Zenstruck\Dom\Tests\Support\TestInspector;

#[CoversClass(Textarea::class)]
final class TextareaTest extends TestCase
{
    #[Test]
    public function value_returns_direct_text(): void
    {
        $dom = new Dom('<form><textarea name="bio">Some text</textarea></form>');
        $textarea = $dom->findOrFail(Selector::css('textarea'))->ensure(Textarea::class);

        $this->assertSame('Some text', $textarea->value());
    }

    #[Test]
    public function value_defers_to_the_inspector(): void
    {
        $markup = '<form><textarea name="bio">Initial</textarea></form>';

        $this->assertSame('Initial', (new Dom($markup))->findOrFail(Selector::css('textarea'))->ensure(Textarea::class)->value());
        $this->assertSame('typed', (new Dom($markup, new TestInspector('typed')))->findOrFail(Selector::css('textarea'))->ensure(Textarea::class)->value());
    }
}
