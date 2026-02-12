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
use Zenstruck\Dom\Node\Form;
use Zenstruck\Dom\Node\Form\Element;
use Zenstruck\Dom\Node\Form\Field\Checkbox;
use Zenstruck\Dom\Node\Form\Field\Input;
use Zenstruck\Dom\Selector;

#[CoversClass(Element::class)]
final class ElementTest extends TestCase
{
    #[Test]
    public function form_returns_closest_ancestor_form(): void
    {
        $dom = $this->dom();
        $input = $dom->findOrFail(Selector::css('#field1'))->ensure(Input::class);

        $form = $input->form();

        $this->assertInstanceOf(Form::class, $form);
        $this->assertSame('form-no-id', $form->attributes()->get('data-testid'));
    }

    #[Test]
    public function form_returns_form_via_form_attribute(): void
    {
        $dom = $this->dom();

        // field5 is outside any form but has form="form-with-id"
        $field = $dom->findOrFail(Selector::css('#field5'))->ensure(Checkbox::class);
        $form = $field->form();

        $this->assertInstanceOf(Form::class, $form);
        $this->assertSame('form-with-id', $form->id());
    }

    #[Test]
    public function form_returns_null_for_invalid_form_attribute(): void
    {
        $dom = $this->dom();

        // orphan2 has form="nonexistent" pointing to a non-existent form
        $field = $dom->findOrFail(Selector::css('#orphan2'))->ensure(Input::class);

        $this->assertNull($field->form());
    }

    #[Test]
    public function form_returns_null_when_not_in_form(): void
    {
        $dom = $this->dom();

        // orphan1 has no form attribute and is not inside any form
        $field = $dom->findOrFail(Selector::css('#orphan1'))->ensure(Input::class);

        $this->assertNull($field->form());
    }

    private function dom(): Dom
    {
        return new Dom(\file_get_contents(__DIR__.'/../../Fixtures/form_attribute.html'));
    }
}
