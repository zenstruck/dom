<?php

/*
 * This file is part of the zenstruck/dom package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Node\Form;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Zenstruck\Dom;
use Zenstruck\Dom\Node\Form\Field;
use Zenstruck\Dom\Node\Form\Field\Input;
use Zenstruck\Dom\Node\Form\Label;
use Zenstruck\Dom\Selector;

#[CoversClass(Field::class)]
final class FieldTest extends TestCase
{
    #[Test]
    public function label_prefers_for_attribute_then_wrapped_label(): void
    {
        $dom = new Dom('<form><label for="a">A</label><input id="a" name="a"></form>');
        $field = $dom->findOrFail(Selector::css('#a'))->ensure(Input::class);

        $label = $field->label();

        $this->assertInstanceOf(Label::class, $label);
        $this->assertSame('A', $label->text());

        $dom = new Dom('<label>Wrapped <input name="wrapped"></label>');
        $wrapped = $dom->findOrFail(Selector::css('input'))->ensure(Input::class);

        $wrappedLabel = $wrapped->label();

        $this->assertInstanceOf(Label::class, $wrappedLabel);
        $this->assertSame('Wrapped', $wrappedLabel->directText());
    }

    #[Test]
    public function name_and_value_return_attributes(): void
    {
        $dom = new Dom('<input name="title" value="hello">');
        $field = $dom->findOrFail(Selector::css('input'))->ensure(Input::class);

        $this->assertSame('title', $field->name());
        $this->assertSame('hello', $field->value());

        $dom = new Dom('<input value="missing-name">');
        $field = $dom->findOrFail(Selector::css('input'))->ensure(Input::class);

        $this->assertNull($field->name());
    }

    #[Test]
    public function collection_is_empty_without_name_or_form(): void
    {
        $dom = new Dom('<input value="no-name">');
        $field = $dom->findOrFail(Selector::css('input'))->ensure(Input::class);

        $this->assertCount(0, $field->collection());

        $dom = new Dom('<input name="outside">');
        $field = $dom->findOrFail(Selector::css('input'))->ensure(Input::class);

        $this->assertCount(0, $field->collection());
    }

    #[Test]
    public function collection_returns_fields_with_same_name_in_form(): void
    {
        $html = '<form>'
            .'<input name="group" value="a">'
            .'<input name="group" value="b">'
            .'<input name="other" value="c">'
            .'</form>';
        $dom = new Dom($html);

        $field = $dom->findOrFail(Selector::css('input[name="group"]'))->ensure(Input::class);

        $this->assertCount(2, $field->collection());
    }

    #[Test]
    public function is_disabled_checks_attribute(): void
    {
        $dom = new Dom('<input name="enabled"><input name="disabled" disabled>');

        $enabled = $dom->findOrFail(Selector::css('input[name="enabled"]'))->ensure(Input::class);
        $disabled = $dom->findOrFail(Selector::css('input[name="disabled"]'))->ensure(Input::class);

        $this->assertFalse($enabled->isDisabled());
        $this->assertTrue($disabled->isDisabled());
    }
}
