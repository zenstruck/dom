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
use Zenstruck\Dom\Node\Form\Field\Input;
use Zenstruck\Dom\Selector;
use Zenstruck\Dom\Tests\Support\TestInspector;

#[CoversClass(Input::class)]
final class InputTest extends TestCase
{
    #[Test]
    public function type_defaults_to_button(): void
    {
        $dom = new Dom('<form><input name="no_type" value="v"></form>');
        $input = $dom->findOrFail(Selector::css('input'))->ensure(Input::class);

        $this->assertSame('button', $input->type());
    }

    #[Test]
    public function value_defers_to_the_inspector(): void
    {
        $markup = $this->dom()->findOrFail(Selector::css('#input1'))->ensure(Input::class);
        $live = $this->dom(new TestInspector('typed'))->findOrFail(Selector::css('#input1'))->ensure(Input::class);

        $this->assertNotSame('typed', $markup->value());
        $this->assertSame('typed', $live->value());
    }

    private function dom(?TestInspector $inspector = null): Dom
    {
        return new Dom((string) \file_get_contents(__DIR__.'/../../../Fixtures/page.html'), $inspector);
    }
}
