<?php

/*
 * This file is part of the zenstruck/dom package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Dom\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DomCrawler\Crawler;
use Zenstruck\Dom;
use Zenstruck\Dom\Exception\RuntimeException;
use Zenstruck\Dom\Node;
use Zenstruck\Dom\Node\Form;
use Zenstruck\Dom\Node\Form\Button;
use Zenstruck\Dom\Node\Form\Field\Checkbox;
use Zenstruck\Dom\Node\Form\Field\File;
use Zenstruck\Dom\Node\Form\Field\Input;
use Zenstruck\Dom\Node\Form\Field\Radio;
use Zenstruck\Dom\Node\Form\Field\Select\Combobox;
use Zenstruck\Dom\Node\Form\Field\Select\Multiselect;
use Zenstruck\Dom\Node\Form\Field\Select\Option;
use Zenstruck\Dom\Node\Form\Field\Textarea;
use Zenstruck\Dom\Node\Form\Label;

#[CoversClass(Node::class)]
final class NodeTest extends TestCase
{
    #[Test]
    public function create_returns_form_for_form_element(): void
    {
        $crawler = (new Crawler('<form></form>'))->filter('form');
        $node = Node::create($crawler, null);

        $this->assertInstanceOf(Form::class, $node);
    }

    #[Test]
    public function create_returns_label_for_label_element(): void
    {
        $crawler = (new Crawler('<label>Name</label>'))->filter('label');
        $node = Node::create($crawler, null);

        $this->assertInstanceOf(Label::class, $node);
    }

    #[Test]
    public function create_returns_textarea_for_textarea_element(): void
    {
        $crawler = (new Crawler('<textarea></textarea>'))->filter('textarea');
        $node = Node::create($crawler, null);

        $this->assertInstanceOf(Textarea::class, $node);
    }

    #[Test]
    public function create_returns_checkbox_for_input_type_checkbox(): void
    {
        $crawler = (new Crawler('<input type="checkbox">'))->filter('input');
        $node = Node::create($crawler, null);

        $this->assertInstanceOf(Checkbox::class, $node);
    }

    #[Test]
    public function create_returns_radio_for_input_type_radio(): void
    {
        $crawler = (new Crawler('<input type="radio">'))->filter('input');
        $node = Node::create($crawler, null);

        $this->assertInstanceOf(Radio::class, $node);
    }

    #[Test]
    public function create_returns_file_for_input_type_file(): void
    {
        $crawler = (new Crawler('<input type="file">'))->filter('input');
        $node = Node::create($crawler, null);

        $this->assertInstanceOf(File::class, $node);
    }

    #[Test]
    public function create_returns_button_for_input_type_submit(): void
    {
        $crawler = (new Crawler('<input type="submit" value="Go">'))->filter('input');
        $node = Node::create($crawler, null);

        $this->assertInstanceOf(Button::class, $node);
    }

    #[Test]
    public function create_returns_button_for_button_element(): void
    {
        $crawler = (new Crawler('<button>Click</button>'))->filter('button');
        $node = Node::create($crawler, null);

        $this->assertInstanceOf(Button::class, $node);
    }

    #[Test]
    public function create_returns_input_for_input_type_text(): void
    {
        $crawler = (new Crawler('<input type="text">'))->filter('input');
        $node = Node::create($crawler, null);

        $this->assertInstanceOf(Input::class, $node);
    }

    #[Test]
    public function create_returns_input_for_input_without_type(): void
    {
        $crawler = (new Crawler('<input>'))->filter('input');
        $node = Node::create($crawler, null);

        $this->assertInstanceOf(Input::class, $node);
    }

    #[Test]
    public function create_returns_option_for_option_element(): void
    {
        $crawler = (new Crawler('<select><option>one</option></select>'))->filter('option');
        $node = Node::create($crawler, null);

        $this->assertInstanceOf(Option::class, $node);
    }

    #[Test]
    public function create_returns_multiselect_for_select_multiple(): void
    {
        $crawler = (new Crawler('<select multiple><option>one</option></select>'))->filter('select');
        $node = Node::create($crawler, null);

        $this->assertInstanceOf(Multiselect::class, $node);
    }

    #[Test]
    public function create_returns_combobox_for_select_element(): void
    {
        $crawler = (new Crawler('<select><option>one</option></select>'))->filter('select');
        $node = Node::create($crawler, null);

        $this->assertInstanceOf(Combobox::class, $node);
    }

    #[Test]
    public function create_returns_base_node_for_generic_element(): void
    {
        $crawler = (new Crawler('<div>content</div>'))->filter('div');
        $node = Node::create($crawler, null);

        $this->assertSame(Node::class, $node::class);
    }

    #[Test]
    public function is_returns_true_for_matching_type(): void
    {
        $crawler = (new Crawler('<input type="text">'))->filter('input');
        $node = Node::create($crawler, null);

        $this->assertTrue($node->is(Input::class));
        $this->assertTrue($node->is(Node::class));
    }

    #[Test]
    public function is_returns_false_for_non_matching_type(): void
    {
        $crawler = (new Crawler('<input type="text">'))->filter('input');
        $node = Node::create($crawler, null);

        $this->assertFalse($node->is(Checkbox::class));
        $this->assertFalse($node->is(Form::class));
    }

    #[Test]
    public function ensure_returns_node_when_type_matches(): void
    {
        $crawler = (new Crawler('<input type="text">'))->filter('input');
        $node = Node::create($crawler, null);

        $result = $node->ensure(Input::class);

        $this->assertSame($node, $result);
        $this->assertInstanceOf(Input::class, $result);
    }

    #[Test]
    public function ensure_throws_runtime_exception_on_type_mismatch(): void
    {
        $crawler = (new Crawler('<input type="text">'))->filter('input');
        $node = Node::create($crawler, null);

        $this->expectException(RuntimeException::class);

        $node->ensure(Checkbox::class);
    }

    #[Test]
    public function ensure_session_throws_when_no_session(): void
    {
        $crawler = (new Crawler('<div>content</div>'))->filter('div');
        $node = Node::create($crawler, null);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No interactive session available.');

        $node->click();
    }

    #[Test]
    public function parent_returns_parent_node(): void
    {
        $dom = new Dom($this->fixtureHtml());
        $link = $dom->find('#link a');

        $this->assertNotNull($link);

        $parent = $link->parent();

        $this->assertNotNull($parent);
        $this->assertSame('p', $parent->tag());
        $this->assertSame('link', $parent->id());
    }

    #[Test]
    public function next_returns_next_sibling(): void
    {
        $dom = new Dom($this->fixtureHtml());
        $div2 = $dom->find('#div2');

        $this->assertNotNull($div2);

        $next = $div2->next();

        $this->assertNotNull($next);
        $this->assertSame('div3', $next->id());
    }

    #[Test]
    public function previous_returns_previous_sibling(): void
    {
        $dom = new Dom($this->fixtureHtml());
        $div3 = $dom->find('#div3');

        $this->assertNotNull($div3);

        $previous = $div3->previous();

        $this->assertNotNull($previous);
        $this->assertSame('div2', $previous->id());
    }

    #[Test]
    public function closest_finds_matching_ancestor(): void
    {
        $dom = new Dom($this->fixtureHtml());
        $li = $dom->find('li');

        $this->assertNotNull($li);

        $ul = $li->closest('ul');

        $this->assertNotNull($ul);
        $this->assertSame('ul', $ul->tag());
    }

    #[Test]
    public function closest_returns_null_when_no_match(): void
    {
        $dom = new Dom($this->fixtureHtml());
        $li = $dom->find('li');

        $this->assertNotNull($li);

        $this->assertNull($li->closest('table'));
    }

    #[Test]
    public function ancestors_returns_all_ancestor_nodes(): void
    {
        $dom = new Dom($this->fixtureHtml());
        $div5 = $dom->find('#div5');

        $this->assertNotNull($div5);

        $ancestors = $div5->ancestors();

        $this->assertGreaterThanOrEqual(3, $ancestors->count());
    }

    #[Test]
    public function root_returns_document_root(): void
    {
        $dom = new Dom($this->fixtureHtml());
        $div5 = $dom->find('#div5');

        $this->assertNotNull($div5);

        $root = $div5->root();

        $this->assertSame('html', $root->tag());
    }

    #[Test]
    public function root_returns_html_element_for_full_document(): void
    {
        $crawler = new Crawler('<!DOCTYPE html><html><body><div id="deep"><span>content</span></div></body></html>');
        $span = $crawler->filter('span');
        $node = Node::create($span, null);

        $root = $node->root();

        $this->assertSame('html', $root->tag());
    }

    #[Test]
    public function siblings_returns_sibling_nodes(): void
    {
        $dom = new Dom($this->fixtureHtml());
        $div3 = $dom->find('#div3');

        $this->assertNotNull($div3);

        $siblings = $div3->siblings();

        $this->assertGreaterThanOrEqual(2, $siblings->count());
    }

    #[Test]
    public function children_returns_child_nodes(): void
    {
        $dom = new Dom($this->fixtureHtml());
        $div1 = $dom->find('#div1');

        $this->assertNotNull($div1);

        $children = $div1->children();

        $this->assertSame(4, $children->count());
    }

    #[Test]
    public function text_returns_element_text_content(): void
    {
        $dom = new Dom($this->fixtureHtml());
        $node = $dom->find('#link');

        $this->assertNotNull($node);
        $this->assertStringContainsString('a link.', $node->text());
        $this->assertStringContainsString('not a link', $node->text());
    }

    #[Test]
    public function direct_text_returns_only_direct_text(): void
    {
        $dom = new Dom($this->fixtureHtml());
        $node = $dom->find('#link');

        $this->assertNotNull($node);

        $directText = $node->directText();

        $this->assertStringContainsString('not a link', $directText);
    }

    #[Test]
    public function outer_html_includes_element_itself(): void
    {
        $dom = new Dom($this->fixtureHtml());
        $node = $dom->find('#link');

        $this->assertNotNull($node);

        $outerHtml = $node->outerHtml();

        $this->assertStringContainsString('<p id="link">', $outerHtml);
        $this->assertStringContainsString('</p>', $outerHtml);
        $this->assertStringContainsString('<a href="/page2"', $outerHtml);
    }

    #[Test]
    public function inner_html_returns_element_contents(): void
    {
        $dom = new Dom($this->fixtureHtml());
        $node = $dom->find('#link');

        $this->assertNotNull($node);

        $innerHtml = $node->innerHtml();

        $this->assertNotNull($innerHtml);
        $this->assertStringContainsString('<a href="/page2"', $innerHtml);
        $this->assertStringNotContainsString('<p id="link">', $innerHtml);
    }

    #[Test]
    public function inner_html_returns_null_for_empty_element(): void
    {
        $crawler = (new Crawler('<span id="empty"></span>'))->filter('#empty');
        $node = Node::create($crawler, null);

        $this->assertNull($node->innerHtml());
    }

    #[Test]
    public function is_visible_returns_false_for_input_type_hidden(): void
    {
        $crawler = (new Crawler('<input type="hidden">'))->filter('input');
        $node = Node::create($crawler, null);

        $this->assertFalse($node->isVisible());
    }

    #[Test]
    public function is_visible_returns_false_for_hidden_attribute(): void
    {
        $crawler = (new Crawler('<div hidden>content</div>'))->filter('div');
        $node = Node::create($crawler, null);

        $this->assertFalse($node->isVisible());
    }

    #[Test]
    public function is_visible_returns_false_for_display_none(): void
    {
        $crawler = (new Crawler('<div style="display:none">content</div>'))->filter('div');
        $node = Node::create($crawler, null);

        $this->assertFalse($node->isVisible());
    }

    #[Test]
    public function is_visible_returns_false_for_visibility_hidden(): void
    {
        $crawler = (new Crawler('<div style="visibility:hidden">content</div>'))->filter('div');
        $node = Node::create($crawler, null);

        $this->assertFalse($node->isVisible());
    }

    #[Test]
    public function is_visible_returns_true_for_normal_element(): void
    {
        $crawler = (new Crawler('<div>content</div>'))->filter('div');
        $node = Node::create($crawler, null);

        $this->assertTrue($node->isVisible());
    }

    #[Test]
    public function tag_returns_element_tag_name(): void
    {
        $crawler = (new Crawler('<span>content</span>'))->filter('span');
        $node = Node::create($crawler, null);

        $this->assertSame('span', $node->tag());
    }

    #[Test]
    public function id_returns_element_id(): void
    {
        $crawler = (new Crawler('<div id="my-id">content</div>'))->filter('div');
        $node = Node::create($crawler, null);

        $this->assertSame('my-id', $node->id());
    }

    #[Test]
    public function id_returns_null_when_no_id(): void
    {
        $crawler = (new Crawler('<div>content</div>'))->filter('div');
        $node = Node::create($crawler, null);

        $this->assertNull($node->id());
    }

    private function fixtureHtml(): string
    {
        return \file_get_contents(__DIR__.'/Fixtures/page.html');
    }
}
