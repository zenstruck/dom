<?php

/*
 * This file is part of the zenstruck/dom package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Dom\Tests\Node\Form\Field\Select;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Zenstruck\Dom;
use Zenstruck\Dom\Node\Form\Field\Checkbox;
use Zenstruck\Dom\Selector;
use Zenstruck\Dom\Tests\Support\TestSession;

#[CoversClass(Checkbox::class)]
final class CheckboxTest extends TestCase
{
    #[Test]
    public function check_uncheck_call_session(): void
    {
        $session = new TestSession();
        $checkbox = $this->dom($session)->findOrFail(Selector::css('#input2'))->ensure(Checkbox::class);

        $checkbox->check();
        $checkbox->uncheck();

        $this->assertCount(1, $session->selected);
        $this->assertCount(1, $session->unselected);
    }

    #[Test]
    public function value_is_on_only_when_checked(): void
    {
        $checked = $this->dom()->findOrFail(Selector::css('#input3'))->ensure(Checkbox::class);
        $unchecked = $this->dom()->findOrFail(Selector::css('#input2'))->ensure(Checkbox::class);

        $this->assertSame('on', $checked->value());
        $this->assertNull($unchecked->value());
    }

    private function dom(?TestSession $session = null): Dom
    {
        return new Dom((string) \file_get_contents(__DIR__.'/../../../../Fixtures/page.html'), $session);
    }
}
