<?php

/*
 * This file is part of the zenstruck/dom package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Dom\Tests\Node\Form;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Zenstruck\Dom;
use Zenstruck\Dom\Node\Form\Button;
use Zenstruck\Dom\Selector;

#[CoversClass(Button::class)]
final class ButtonTest extends TestCase
{
    #[Test]
    public function type_defaults_to_button_when_missing(): void
    {
        $dom = new Dom('<button>Click</button>');
        $button = $dom->findOrFail(Selector::css('button'))->ensure(Button::class);

        $this->assertSame('button', $button->type());
    }

    #[Test]
    public function type_returns_attribute_when_present(): void
    {
        $dom = new Dom((string) \file_get_contents(__DIR__.'/../../Fixtures/page.html'));
        $button = $dom->findOrFail(Selector::css('button[type="submit"][name="submit_1"]'))->ensure(Button::class);

        $this->assertSame('submit', $button->type());
    }

    #[Test]
    public function value_uses_attribute_or_text(): void
    {
        $dom = new Dom((string) \file_get_contents(__DIR__.'/../../Fixtures/page.html'));

        $valueAttr = $dom->findOrFail(Selector::css('button[type="submit"][name="submit_1"]'))->ensure(Button::class);
        $this->assertSame('b', $valueAttr->value());

        $textValue = (new Dom('<button>Label Text</button>'))
            ->findOrFail(Selector::css('button'))
            ->ensure(Button::class);

        $this->assertSame('Label Text', $textValue->value());
    }
}
