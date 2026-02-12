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
use Zenstruck\Dom\Exception\RuntimeException;
use Zenstruck\Dom\Node\Form\Field\Radio;
use Zenstruck\Dom\Selector;
use Zenstruck\Dom\Tests\Support\TestSession;

#[CoversClass(Radio::class)]
final class RadioTest extends TestCase
{
    #[Test]
    public function select_without_value_uses_self(): void
    {
        $session = new TestSession();
        $radio = $this->dom($session)->findOrFail(Selector::css('#radio1'))->ensure(Radio::class);

        $radio->select();

        $this->assertCount(1, $session->selected);
        $this->assertSame('option 1', $session->selected[0]->value());
    }

    #[Test]
    public function select_with_value_uses_matching_radio(): void
    {
        $session = new TestSession();
        $radio = $this->dom($session)->findOrFail(Selector::css('#radio1'))->ensure(Radio::class);

        $radio->select('option 3');

        $this->assertCount(1, $session->selected);
        $this->assertSame('option 3', $session->selected[0]->value());
    }

    #[Test]
    public function select_with_value_throws_when_missing(): void
    {
        $radio = $this->dom(new TestSession())->findOrFail(Selector::css('#radio1'))->ensure(Radio::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Could not find radio with value "missing".');

        $radio->select('missing');
    }

    #[Test]
    public function selected_returns_null_when_none_checked(): void
    {
        $dom = new Dom('<form><input type="radio" name="group" value="a"><input type="radio" name="group" value="b"></form>');
        $radio = $dom->findOrFail(Selector::css('input[value="a"]'))->ensure(Radio::class);

        $this->assertNull($radio->selected());
        $this->assertNull($radio->selectedValue());
    }

    private function dom(?TestSession $session = null): Dom
    {
        return new Dom((string) \file_get_contents(__DIR__.'/../../../Fixtures/page.html'), $session);
    }
}
