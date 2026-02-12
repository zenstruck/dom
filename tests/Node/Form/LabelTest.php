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
use Zenstruck\Dom\Node\Form\Field\File;
use Zenstruck\Dom\Node\Form\Field\Input;
use Zenstruck\Dom\Node\Form\Label;
use Zenstruck\Dom\Selector;

#[CoversClass(Label::class)]
final class LabelTest extends TestCase
{
    #[Test]
    public function field_resolves_by_for_attribute(): void
    {
        $dom = new Dom((string) \file_get_contents(__DIR__.'/../../Fixtures/page.html'));
        $label = $dom->findOrFail(Selector::css('label[for="input1"]'))->ensure(Label::class);

        $field = $label->field();

        $this->assertInstanceOf(Input::class, $field);
        $this->assertSame('input_1', $field->name());
    }

    #[Test]
    public function field_resolves_wrapped_input(): void
    {
        $dom = new Dom((string) \file_get_contents(__DIR__.'/../../Fixtures/page.html'));
        $label = $dom->findOrFail(Selector::css('#input9'))->closest('label')->ensure(Label::class);

        $field = $label->field();

        $this->assertInstanceOf(File::class, $field);
        $this->assertSame('input_9[]', $field->name());
    }

    #[Test]
    public function field_returns_null_when_missing(): void
    {
        $dom = new Dom('<label>Orphan</label>');
        $label = $dom->findOrFail(Selector::css('label'))->ensure(Label::class);

        $this->assertNull($label->field());
    }
}
