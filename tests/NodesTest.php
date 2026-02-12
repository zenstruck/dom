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
use Symfony\Component\Process\Process;
use Symfony\Component\VarDumper\VarDumper;
use Zenstruck\Dom\Node;
use Zenstruck\Dom\Nodes;

#[CoversClass(Nodes::class)]
final class NodesTest extends TestCase
{
    #[Test]
    public function first_returns_first_node(): void
    {
        $crawler = (new Crawler($this->fixtureHtml()))->filter('li');
        $nodes = Nodes::create($crawler, null);

        $first = $nodes->first();

        $this->assertNotNull($first);
        $this->assertInstanceOf(Node::class, $first);
        $this->assertSame('list 1', $first->text());
    }

    #[Test]
    public function first_returns_null_for_empty_collection(): void
    {
        $crawler = new Crawler();
        $nodes = Nodes::create($crawler, null);

        $this->assertNull($nodes->first());
    }

    #[Test]
    public function first_with_selector_filters_then_returns_first(): void
    {
        $crawler = new Crawler($this->fixtureHtml());
        $nodes = Nodes::create($crawler, null);

        $first = $nodes->first('li');

        $this->assertNotNull($first);
        $this->assertSame('li', $first->tag());
        $this->assertSame('list 1', $first->text());
    }

    #[Test]
    public function first_with_selector_returns_null_when_no_match(): void
    {
        $crawler = new Crawler($this->fixtureHtml());
        $nodes = Nodes::create($crawler, null);

        $first = $nodes->first('table');

        $this->assertNull($first);
    }

    #[Test]
    public function last_returns_last_node(): void
    {
        $crawler = (new Crawler($this->fixtureHtml()))->filter('li');
        $nodes = Nodes::create($crawler, null);

        $last = $nodes->last();

        $this->assertNotNull($last);
        $this->assertInstanceOf(Node::class, $last);
        $this->assertSame('list 3', $last->text());
    }

    #[Test]
    public function last_returns_null_for_empty_collection(): void
    {
        $crawler = new Crawler();
        $nodes = Nodes::create($crawler, null);

        $this->assertNull($nodes->last());
    }

    #[Test]
    public function filter_returns_matching_nodes(): void
    {
        $crawler = new Crawler($this->fixtureHtml());
        $nodes = Nodes::create($crawler, null);

        $filtered = $nodes->filter('li');

        $this->assertInstanceOf(Nodes::class, $filtered);
        $this->assertSame(3, $filtered->count());
    }

    #[Test]
    public function filter_returns_empty_nodes_when_no_match(): void
    {
        $crawler = new Crawler($this->fixtureHtml());
        $nodes = Nodes::create($crawler, null);

        $filtered = $nodes->filter('table');

        $this->assertInstanceOf(Nodes::class, $filtered);
        $this->assertSame(0, $filtered->count());
    }

    #[Test]
    public function map_transforms_each_node(): void
    {
        $crawler = (new Crawler($this->fixtureHtml()))->filter('li');
        $nodes = Nodes::create($crawler, null);

        $texts = $nodes->map(static fn(Node $n) => $n->text());

        $this->assertSame(['list 1', 'list 2', 'list 3'], $texts);
    }

    #[Test]
    public function map_returns_empty_array_for_empty_collection(): void
    {
        $crawler = new Crawler();
        $nodes = Nodes::create($crawler, null);

        $result = $nodes->map(static fn(Node $n) => $n->text());

        $this->assertSame([], $result);
    }

    #[Test]
    public function text_joins_all_node_texts_with_spaces(): void
    {
        $crawler = (new Crawler($this->fixtureHtml()))->filter('li');
        $nodes = Nodes::create($crawler, null);

        $text = $nodes->text();

        $this->assertSame('list 1 list 2 list 3', $text);
    }

    #[Test]
    public function text_returns_null_for_empty_collection(): void
    {
        $crawler = new Crawler();
        $nodes = Nodes::create($crawler, null);

        $this->assertNull($nodes->text());
    }

    #[Test]
    public function html_joins_all_node_outer_html_with_newlines(): void
    {
        $crawler = (new Crawler('<ul><li>a</li><li>b</li></ul>'))->filter('li');
        $nodes = Nodes::create($crawler, null);

        $html = $nodes->html();

        $this->assertNotNull($html);
        $this->assertStringContainsString('<li>a</li>', $html);
        $this->assertStringContainsString('<li>b</li>', $html);
        $this->assertStringContainsString("\n", $html);
    }

    #[Test]
    public function html_returns_null_for_empty_collection(): void
    {
        $crawler = new Crawler();
        $nodes = Nodes::create($crawler, null);

        $this->assertNull($nodes->html());
    }

    #[Test]
    public function count_returns_number_of_nodes(): void
    {
        $crawler = (new Crawler($this->fixtureHtml()))->filter('li');
        $nodes = Nodes::create($crawler, null);

        $this->assertSame(3, $nodes->count());
        $this->assertCount(3, $nodes);
    }

    #[Test]
    public function count_returns_zero_for_empty_collection(): void
    {
        $crawler = new Crawler();
        $nodes = Nodes::create($crawler, null);

        $this->assertSame(0, $nodes->count());
        $this->assertCount(0, $nodes);
    }

    #[Test]
    public function iteration_yields_node_instances(): void
    {
        $crawler = (new Crawler($this->fixtureHtml()))->filter('li');
        $nodes = Nodes::create($crawler, null);

        $collected = [];

        foreach ($nodes as $node) {
            $this->assertInstanceOf(Node::class, $node);
            $collected[] = $node->text();
        }

        $this->assertSame(['list 1', 'list 2', 'list 3'], $collected);
    }

    #[Test]
    public function empty_collection_behavior(): void
    {
        $crawler = new Crawler();
        $nodes = Nodes::create($crawler, null);

        $this->assertNull($nodes->first());
        $this->assertNull($nodes->last());
        $this->assertSame(0, $nodes->count());
        $this->assertNull($nodes->text());
        $this->assertNull($nodes->html());
        $this->assertSame([], $nodes->map(static fn(Node $n) => $n->text()));

        $iterated = false;

        foreach ($nodes as $node) {
            $iterated = true;
        }

        $this->assertFalse($iterated);
    }

    #[Test]
    public function merge_combines_two_node_collections_from_same_document(): void
    {
        $crawler = new Crawler('<div><ul id="list1"><li id="a">A</li><li id="b">B</li></ul><ul id="list2"><li id="c">C</li></ul></div>');
        $list1Items = $crawler->filter('#list1 li');
        $list2Items = $crawler->filter('#list2 li');

        $nodes1 = Nodes::create($list1Items, null);
        $nodes2 = Nodes::create($list2Items, null);

        $merged = $nodes1->merge($nodes2);

        $this->assertCount(3, $merged);
        $this->assertSame(['a', 'b', 'c'], $merged->map(static fn(Node $n) => $n->id()));
    }

    #[Test]
    public function merge_preserves_original_collections(): void
    {
        $crawler = new Crawler('<div><ul id="list1"><li>A</li></ul><ul id="list2"><li>B</li></ul></div>');
        $list1Items = $crawler->filter('#list1 li');
        $list2Items = $crawler->filter('#list2 li');

        $nodes1 = Nodes::create($list1Items, null);
        $nodes2 = Nodes::create($list2Items, null);

        $nodes1->merge($nodes2);

        $this->assertCount(1, $nodes1);
        $this->assertCount(1, $nodes2);
    }

    #[Test]
    public function merge_with_empty_collection(): void
    {
        $crawler = new Crawler('<ul><li>A</li><li>B</li></ul>');
        $items = $crawler->filter('li');
        $empty = new Crawler();

        $nodes = Nodes::create($items, null);
        $emptyNodes = Nodes::create($empty, null);

        $this->assertCount(2, $nodes->merge($emptyNodes));
    }

    #[Test]
    public function reduce_filters_nodes_by_callback(): void
    {
        $crawler = (new Crawler('<ul><li id="a">A</li><li id="b">B</li><li id="c">C</li></ul>'))->filter('li');
        $nodes = Nodes::create($crawler, null);

        $filtered = $nodes->reduce(static fn(Node $n) => $n->id() !== 'b');

        $this->assertCount(2, $filtered);
        $this->assertSame(['a', 'c'], $filtered->map(static fn(Node $n) => $n->id()));
    }

    #[Test]
    public function reduce_preserves_original_collection(): void
    {
        $crawler = (new Crawler('<ul><li>A</li><li>B</li></ul>'))->filter('li');
        $nodes = Nodes::create($crawler, null);

        $nodes->reduce(static fn(Node $n) => false);

        $this->assertCount(2, $nodes);
    }

    #[Test]
    public function reduce_returns_empty_when_nothing_matches(): void
    {
        $crawler = (new Crawler('<ul><li>A</li><li>B</li></ul>'))->filter('li');
        $nodes = Nodes::create($crawler, null);

        $filtered = $nodes->reduce(static fn(Node $n) => false);

        $this->assertCount(0, $filtered);
    }

    #[Test]
    public function dump_outputs_each_node(): void
    {
        $nodes = Nodes::create((new Crawler($this->fixtureHtml()))->filter('li'), null);

        $output = $this->captureDumpOutput(static fn() => $nodes->dump());

        $this->assertStringContainsString('list 1', $output);
        $this->assertStringContainsString('list 2', $output);
        $this->assertStringContainsString('list 3', $output);
    }

    #[Test]
    public function dd_exits_with_output(): void
    {
        $projectRoot = \dirname(__DIR__);
        $code = <<<'PHP'
require "vendor/autoload.php";
$dom = new \Zenstruck\Dom(file_get_contents("tests/Fixtures/page.html"));
$dom->findAll('li')->dd();
PHP;
        $process = new Process([\PHP_BINARY, '-r', $code], $projectRoot);

        $process->run();

        $this->assertSame(1, $process->getExitCode());
        $this->assertStringContainsString('list 1', $process->getOutput());
    }

    private function fixtureHtml(): string
    {
        return \file_get_contents(__DIR__.'/Fixtures/page.html');
    }

    private function captureDumpOutput(callable $callback): string
    {
        $output = '';
        $previous = VarDumper::setHandler(static function(mixed $value) use (&$output): void {
            $output .= \is_string($value) ? $value : (string) $value;
        });

        try {
            $callback();
        } finally {
            VarDumper::setHandler($previous);
        }

        return $output;
    }
}
