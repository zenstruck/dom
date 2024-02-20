<?php

/*
 * This file is part of the zenstruck/dom package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Dom;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Panther\DomCrawler\Crawler as PantherCrawler;
use Zenstruck\Dom\Exception\RuntimeException;
use Zenstruck\Dom\Node\Attributes;
use Zenstruck\Dom\Node\Form;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @phpstan-import-type SelectorType from Selector
 */
class Node
{
    public const SELECTOR = '*';

    private Attributes $attributes;

    private function __construct(private Crawler $crawler)
    {
    }

    final public static function create(Crawler $crawler): self
    {
        $node = new self($crawler);
        $tag = \mb_strtolower($node->tag());

        return match (true) {
            'form' === $tag => new Form($crawler),
            'label' === $tag => new Form\Label($crawler),
            'textarea' === $tag => new Form\Field\Textarea($crawler),
            'input' === $tag && $node->attributes()->is('type', 'checkbox') => new Form\Field\Checkbox($crawler),
            'input' === $tag && $node->attributes()->is('type', 'radio') => new Form\Field\Radio($crawler),
            'input' === $tag && $node->attributes()->is('type', 'file') => new Form\Field\File($crawler),
            'input' === $tag && $node->attributes()->is('type', 'submit', 'button', 'reset', 'image') => new Form\Button($crawler),
            'button' === $tag => new Form\Button($crawler),
            'input' === $tag => new Form\Field\Input($crawler),
            'option' === $tag => new Form\Field\Select\Option($crawler),
            'select' === $tag && $node->attributes()->has('multiple') => new Form\Field\Select\Multiselect($crawler),
            'select' === $tag => new Form\Field\Select\Combobox($crawler),
            default => $node,
        };
    }

    final public function crawler(): Crawler
    {
        return $this->crawler;
    }

    final public function tag(): string
    {
        return $this->crawler->nodeName();
    }

    final public function isVisible(): bool
    {
        if ($this->crawler instanceof PantherCrawler) {
            return $this->crawler->isDisplayed();
        }

        return true;
    }

    final public function element(): \DOMElement
    {
        $element = $this->normalizedCrawler()->getNode(0);

        return $element instanceof \DOMElement ? $element : throw new RuntimeException('Unable to get DOMElement from node.');
    }

    final public function attributes(): Attributes
    {
        return $this->attributes ??= new Attributes($this->element());
    }

    final public function text(): string
    {
        return $this->normalizedCrawler()->text();
    }

    final public function directText(): string
    {
        return $this->normalizedCrawler()->innerText();
    }

    final public function outerHtml(): string
    {
        return $this->normalizedCrawler()->outerHtml();
    }

    final public function innerHtml(): ?string
    {
        $html = $this->normalizedCrawler()->html();

        return '' === $html ? null : $html;
    }

    final public function parent(): ?self
    {
        return Nodes::create($this->crawler->ancestors())->first();
    }

    final public function next(): ?self
    {
        return Nodes::create($this->crawler->nextAll())->first();
    }

    public function previous(): ?self
    {
        return Nodes::create($this->crawler->previousAll())->first();
    }

    final public function closest(string $selector): ?self
    {
        $closest = $this->crawler->closest($selector);

        return $closest ? self::create($closest) : null;
    }

    /**
     * @param SelectorType $selector
     */
    final public function ancestor(Selector|string|callable $selector): ?self
    {
        return $this->ancestors($selector)->first();
    }

    /**
     * @param SelectorType|null $selector
     */
    final public function ancestors(Selector|string|callable|null $selector = null): Nodes
    {
        return $this->applySelectorTo($this->crawler->ancestors(), $selector);
    }

    /**
     * @param SelectorType|null $selector
     */
    final public function siblings(Selector|string|callable|null $selector = null): Nodes
    {
        return $this->applySelectorTo($this->crawler->siblings(), $selector);
    }

    /**
     * @param SelectorType|null $selector
     */
    final public function children(Selector|string|callable|null $selector = null): Nodes
    {
        return $this->applySelectorTo($this->crawler->children(), $selector);
    }

    /**
     * @param SelectorType $selector
     */
    final public function descendent(Selector|string|callable $selector): ?self
    {
        return $this->descendents($selector)->first();
    }

    /**
     * @param SelectorType|null $selector
     */
    final public function descendents(Selector|string|callable|null $selector = null): Nodes
    {
        return $this->applySelectorTo($this->crawler, $selector ?? Selector::xpath('descendant::*'));
    }

    /**
     * @template T of self
     *
     * @param class-string<T> $type
     */
    final public function is(string $type): bool
    {
        return $this instanceof $type;
    }

    /**
     * @template T of self
     *
     * @param class-string<T> $type
     *
     * @return T
     */
    final public function ensure(string $type): self
    {
        if ($this instanceof $type) {
            return $this;
        }

        throw new RuntimeException(\sprintf('Expected "%s", got "%s".', $type, $this::class));
    }

    final public function id(): ?string
    {
        return $this->attributes()->get('id');
    }

    final public function dump(): static
    {
        \function_exists('dump') ? dump($this->outerHtml()) : \var_dump($this->outerHtml());

        return $this;
    }

    final public function dd(): void
    {
        $this->dump();

        exit(1);
    }

    /**
     * @param SelectorType|null $selector
     */
    private function applySelectorTo(Crawler $crawler, Selector|string|callable|null $selector = null): Nodes
    {
        $nodes = Nodes::create($crawler);

        return $selector ? $nodes->filter($selector) : $nodes;
    }

    private function normalizedCrawler(): Crawler
    {
        if (!$this->crawler instanceof PantherCrawler) {
            return $this->crawler;
        }

        if (!$element = $this->crawler->getElement(0)) {
            throw new RuntimeException('Unable to get element from PantherCrawler.');
        }

        if (!\method_exists($element, 'getDomProperty')) {
            throw new RuntimeException('Unable to get outerHTML from PantherCrawler.');
        }

        $html = $element->getDomProperty('outerHTML') ?? throw new RuntimeException('Unable to get outerHTML from PantherCrawler.');

        return (new Crawler($html))->filter($this->tag());
    }
}
