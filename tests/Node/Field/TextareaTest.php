<?php

/*
 * This file is part of the zenstruck/dom package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Dom\Tests\Node\Field;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Zenstruck\Dom;
use Zenstruck\Dom\Node\Form\Field\Textarea;
use Zenstruck\Dom\Selector;
use Zenstruck\Dom\Tests\Support\TestSession;

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
    public function fill_calls_session(): void
    {
        $session = new TestSession();
        $dom = new Dom('<form><textarea name="bio">Initial</textarea></form>', $session);
        $textarea = $dom->findOrFail(Selector::css('textarea'))->ensure(Textarea::class);

        $textarea->fill('Updated');

        $this->assertCount(1, $session->fills);
        $this->assertSame('Updated', $session->fills[0][1]);
    }
}
