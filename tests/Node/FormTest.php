<?php

/*
 * This file is part of the zenstruck/dom package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Dom\Tests\Node;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Zenstruck\Dom;
use Zenstruck\Dom\Node;
use Zenstruck\Dom\Node\Form;
use Zenstruck\Dom\Node\Form\Button;
use Zenstruck\Dom\Node\Form\Field;
use Zenstruck\Dom\Node\Form\Field\Checkbox;
use Zenstruck\Dom\Node\Form\Field\Input;
use Zenstruck\Dom\Node\Form\Field\Radio;
use Zenstruck\Dom\Node\Form\Field\Select\Combobox;
use Zenstruck\Dom\Node\Form\Field\Select\Multiselect;
use Zenstruck\Dom\Node\Form\Field\Select\Option;
use Zenstruck\Dom\Node\Form\Field\Textarea;
use Zenstruck\Dom\Node\Form\Label;
use Zenstruck\Dom\Selector;

#[CoversClass(Form::class)]
final class FormTest extends TestCase
{
    #[Test]
    public function fields_returns_nodes_with_name_attribute(): void
    {
        $form = $this->dom()->findOrFail(Selector::css('form'))->ensure(Form::class);
        $fields = $form->fields();

        $this->assertGreaterThan(0, \count($fields));

        foreach ($fields as $field) {
            $this->assertNotNull($field->ensure(Field::class)->name());
        }
    }

    #[Test]
    public function buttons_returns_button_nodes(): void
    {
        $form = $this->dom()->findOrFail(Selector::css('form'))->ensure(Form::class);
        $buttons = $form->buttons();

        $this->assertGreaterThan(0, \count($buttons));

        foreach ($buttons as $button) {
            $this->assertInstanceOf(Button::class, $button);
        }
    }

    #[Test]
    public function submit_buttons_returns_only_submit_type(): void
    {
        $form = $this->dom()->findOrFail(Selector::css('form'))->ensure(Form::class);
        $submitButtons = $form->submitButtons();

        $this->assertGreaterThan(0, \count($submitButtons));

        foreach ($submitButtons as $button) {
            $button = $button->ensure(Button::class);
            $this->assertSame('submit', $button->type());
        }
    }

    #[Test]
    public function submit_button_returns_first_submit_button(): void
    {
        $form = $this->dom()->findOrFail(Selector::css('form'))->ensure(Form::class);
        $submitButton = $form->submitButton();

        $this->assertInstanceOf(Button::class, $submitButton);
        $this->assertSame('submit', $submitButton->type());
        $this->assertSame('Submit', $submitButton->value());
    }

    // --- Label ---

    #[Test]
    public function label_with_for_attribute_returns_linked_field(): void
    {
        $label = $this->dom()->findOrFail(Selector::css('label[for="input1"]'))->ensure(Label::class);
        $field = $label->field();

        $this->assertInstanceOf(Input::class, $field);
        $this->assertSame('input_1', $field->name());
    }

    #[Test]
    public function wrapping_label_with_for_attribute(): void
    {
        // Label with `for` attribute pointing to a checkbox
        $label = $this->dom()->findOrFail(Selector::css('label[for="input2"]'))->ensure(Label::class);
        $field = $label->field();

        $this->assertInstanceOf(Checkbox::class, $field);
        $this->assertSame('input_2', $field->name());
    }

    #[Test]
    public function input_value_returns_value_attribute(): void
    {
        $input = $this->dom()->findOrFail(Selector::css('#input1'))->ensure(Input::class);

        $this->assertSame('input 1', $input->value());
    }

    #[Test]
    public function input_type_returns_input_type(): void
    {
        $input = $this->dom()->findOrFail(Selector::css('#input1'))->ensure(Input::class);

        $this->assertSame('text', $input->type());
    }

    #[Test]
    public function input_name_returns_name_attribute(): void
    {
        $input = $this->dom()->findOrFail(Selector::css('#input1'))->ensure(Input::class);

        $this->assertSame('input_1', $input->name());
    }

    #[Test]
    public function input_label_finds_associated_label(): void
    {
        $input = $this->dom()->findOrFail(Selector::css('#input1'))->ensure(Input::class);
        $label = $input->label();

        $this->assertInstanceOf(Label::class, $label);
        $this->assertSame('Input 1', $label->text());
    }

    #[Test]
    public function input_is_disabled_returns_false_for_enabled(): void
    {
        $input = $this->dom()->findOrFail(Selector::css('#input1'))->ensure(Input::class);

        $this->assertFalse($input->isDisabled());
    }

    #[Test]
    public function textarea_value_returns_text_content(): void
    {
        // The fixture does not have a textarea, so we build a minimal DOM with one
        $html = '<form><textarea name="bio">Some text content</textarea></form>';
        $dom = new Dom($html);
        $textarea = $dom->findOrFail(Selector::css('textarea'))->ensure(Textarea::class);

        $this->assertSame('Some text content', $textarea->value());
    }

    #[Test]
    public function checkbox_is_checked(): void
    {
        $checked = $this->dom()->findOrFail(Selector::css('#input3'))->ensure(Checkbox::class);
        $unchecked = $this->dom()->findOrFail(Selector::css('#input2'))->ensure(Checkbox::class);

        $this->assertTrue($checked->isChecked());
        $this->assertFalse($unchecked->isChecked());
    }

    #[Test]
    public function checkbox_value(): void
    {
        $checked = $this->dom()->findOrFail(Selector::css('#input3'))->ensure(Checkbox::class);
        $unchecked = $this->dom()->findOrFail(Selector::css('#input2'))->ensure(Checkbox::class);

        $this->assertSame('on', $checked->value());
        $this->assertNull($unchecked->value());
    }

    #[Test]
    public function radio_is_selected(): void
    {
        $selected = $this->dom()->findOrFail(Selector::css('#radio2'))->ensure(Radio::class);
        $notSelected = $this->dom()->findOrFail(Selector::css('#radio1'))->ensure(Radio::class);

        $this->assertTrue($selected->isSelected());
        $this->assertFalse($notSelected->isSelected());
    }

    #[Test]
    public function radio_selected_returns_checked_radio(): void
    {
        $radio = $this->dom()->findOrFail(Selector::css('#radio1'))->ensure(Radio::class);
        $selected = $radio->selected();

        $this->assertInstanceOf(Radio::class, $selected);
        $this->assertSame('option 2', $selected->value());
    }

    #[Test]
    public function radio_selected_value(): void
    {
        $radio = $this->dom()->findOrFail(Selector::css('#radio1'))->ensure(Radio::class);

        $this->assertSame('option 2', $radio->selectedValue());
    }

    #[Test]
    public function combobox_selected_option(): void
    {
        $select = $this->dom()->findOrFail(Selector::css('#input4'))->ensure(Combobox::class);
        $option = $select->selectedOption();

        $this->assertInstanceOf(Option::class, $option);
        $this->assertTrue($option->isSelected());
    }

    #[Test]
    public function combobox_selected_value(): void
    {
        $select = $this->dom()->findOrFail(Selector::css('#input4'))->ensure(Combobox::class);

        // The first option has no value attribute, so value() returns its text
        $this->assertSame('option 1', $select->selectedValue());
    }

    #[Test]
    public function combobox_selected_text(): void
    {
        $select = $this->dom()->findOrFail(Selector::css('#input4'))->ensure(Combobox::class);

        $this->assertSame('option 1', $select->selectedText());
    }

    #[Test]
    public function combobox_available_options(): void
    {
        $select = $this->dom()->findOrFail(Selector::css('#input4'))->ensure(Combobox::class);
        $options = $select->availableOptions();

        $this->assertCount(2, $options);

        foreach ($options as $option) {
            $this->assertInstanceOf(Option::class, $option);
        }
    }

    #[Test]
    public function combobox_available_values(): void
    {
        $select = $this->dom()->findOrFail(Selector::css('#input4'))->ensure(Combobox::class);

        $this->assertSame(['option 1', 'option 2'], $select->availableValues());
    }

    #[Test]
    public function multiselect_selected_options(): void
    {
        $select = $this->dom()->findOrFail(Selector::css('#input7'))->ensure(Multiselect::class);
        $selected = $select->selectedOptions();

        $this->assertCount(2, $selected);

        foreach ($selected as $option) {
            $this->assertInstanceOf(Option::class, $option);
        }
    }

    #[Test]
    public function multiselect_selected_values(): void
    {
        $select = $this->dom()->findOrFail(Selector::css('#input7'))->ensure(Multiselect::class);

        $this->assertSame(['option 1', 'option 3'], $select->selectedValues());
    }

    #[Test]
    public function multiselect_selected_texts(): void
    {
        $select = $this->dom()->findOrFail(Selector::css('#input7'))->ensure(Multiselect::class);

        $this->assertSame(['option 1', 'option 3'], $select->selectedTexts());
    }

    #[Test]
    public function button_type(): void
    {
        // input[type="submit"]
        $submitInput = $this->dom()->findOrFail(Selector::css('input[type="submit"]'))->ensure(Button::class);
        $this->assertSame('submit', $submitInput->type());

        // button[type="submit"]
        $submitButton = $this->dom()->findOrFail(Selector::css('button[type="submit"][name="submit_1"]'))->ensure(Button::class);
        $this->assertSame('submit', $submitButton->type());
    }

    #[Test]
    public function button_value(): void
    {
        // input[type="submit"] returns value attribute
        $submitInput = $this->dom()->findOrFail(Selector::css('input[type="submit"]'))->ensure(Button::class);
        $this->assertSame('Submit', $submitInput->value());

        // button with value attribute returns value attribute
        $submitButton = $this->dom()->findOrFail(Selector::css('button[type="submit"][name="submit_1"]'))->ensure(Button::class);
        $this->assertSame('b', $submitButton->value());
    }

    #[Test]
    public function option_value_returns_value_attribute_or_text(): void
    {
        // Option with explicit value attribute
        $option = $this->dom()->findOrFail(Selector::css('#input4 option[value="option 2"]'))->ensure(Option::class);
        $this->assertSame('option 2', $option->value());

        // Option without value attribute returns text content
        $option = $this->dom()->findOrFail(Selector::css('#input4 option:first-child'))->ensure(Option::class);
        $this->assertSame('option 1', $option->value());
    }

    #[Test]
    public function option_is_selected(): void
    {
        $selected = $this->dom()->findOrFail(Selector::css('#input4 option[selected]'))->ensure(Option::class);
        $notSelected = $this->dom()->findOrFail(Selector::css('#input4 option[value="option 2"]'))->ensure(Option::class);

        $this->assertTrue($selected->isSelected());
        $this->assertFalse($notSelected->isSelected());
    }

    #[Test]
    public function form_element_form_returns_enclosing_form(): void
    {
        $input = $this->dom()->findOrFail(Selector::css('#input1'))->ensure(Input::class);
        $form = $input->form();

        $this->assertInstanceOf(Form::class, $form);
    }

    #[Test]
    public function form_element_form_returns_null_when_not_in_form(): void
    {
        // Build a DOM with an input outside a form
        $html = '<div><input name="orphan" type="text" /></div>';
        $dom = new Dom($html);
        $input = $dom->findOrFail(Selector::css('input'))->ensure(Input::class);

        $this->assertNull($input->form());
    }

    #[Test]
    public function radio_collection_returns_all_radios_with_same_name(): void
    {
        $radio = $this->dom()->findOrFail(Selector::css('#radio1'))->ensure(Radio::class);
        $collection = $radio->collection();

        $this->assertCount(3, $collection);

        foreach ($collection as $node) {
            $this->assertInstanceOf(Radio::class, $node);
            $this->assertSame('input_8', $node->ensure(Radio::class)->name());
        }
    }

    // --- Form Attribute Support ---

    #[Test]
    public function form_without_id_excludes_elements_with_form_attribute(): void
    {
        $dom = $this->formAttributeDom();
        $form = $dom->findOrFail(Selector::css('[data-testid="form-no-id"]'))->ensure(Form::class);

        $fields = $form->fields();
        $fieldIds = $fields->map(static fn(Node $n) => $n->id());

        // field1 and btn1 belong to form-no-id (no form attribute)
        $this->assertContains('field1', $fieldIds);
        $this->assertContains('btn1', $fieldIds);

        // field2 and btn2 have form="form-with-id", so excluded
        $this->assertNotContains('field2', $fieldIds);
        $this->assertNotContains('btn2', $fieldIds);
    }

    #[Test]
    public function form_with_id_includes_external_elements_with_form_attribute(): void
    {
        $dom = $this->formAttributeDom();
        $form = $dom->findOrFail(Selector::css('[data-testid="form-with-id"]'))->ensure(Form::class);

        $fields = $form->fields();
        $fieldIds = $fields->map(static fn(Node $n) => $n->id());

        // Direct children
        $this->assertContains('field3', $fieldIds);
        $this->assertContains('field4', $fieldIds);
        $this->assertContains('btn3', $fieldIds);

        // External elements with form="form-with-id"
        $this->assertContains('field2', $fieldIds); // from form-no-id
        $this->assertContains('field5', $fieldIds); // checkbox outside
        $this->assertContains('field6', $fieldIds); // textarea outside
        $this->assertContains('btn2', $fieldIds);   // button from form-no-id
        $this->assertContains('btn4', $fieldIds);   // button outside
    }

    #[Test]
    public function buttons_respects_form_attribute(): void
    {
        $dom = $this->formAttributeDom();

        $formNoId = $dom->findOrFail(Selector::css('[data-testid="form-no-id"]'))->ensure(Form::class);
        $formWithId = $dom->findOrFail(Selector::css('[data-testid="form-with-id"]'))->ensure(Form::class);

        $noIdButtonIds = $formNoId->buttons()->map(static fn(Button $b) => $b->id());
        $withIdButtonIds = $formWithId->buttons()->map(static fn(Button $b) => $b->id());

        $this->assertContains('btn1', $noIdButtonIds);
        $this->assertNotContains('btn2', $noIdButtonIds);

        $this->assertContains('btn2', $withIdButtonIds);
        $this->assertContains('btn3', $withIdButtonIds);
        $this->assertContains('btn4', $withIdButtonIds);
    }

    private function dom(): Dom
    {
        return new Dom(\file_get_contents(__DIR__.'/../Fixtures/page.html'));
    }

    private function formAttributeDom(): Dom
    {
        return new Dom(\file_get_contents(__DIR__.'/../Fixtures/form_attribute.html'));
    }
}
