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

    private function __construct(protected readonly Crawler $crawler, protected readonly ?Session $session)
    {
    }

    final public static function create(Crawler $crawler, ?Session $session): self
    {
        $node = new self($crawler, $session);
        $tag = \mb_strtolower($node->tag());

        return match (true) {
            'form' === $tag => new Form($crawler, $session),
            'label' === $tag => new Form\Label($crawler, $session),
            'textarea' === $tag => new Form\Field\Textarea($crawler, $session),
            'input' === $tag && $node->attributes()->is('type', 'checkbox') => new Form\Field\Checkbox($crawler, $session),
            'input' === $tag && $node->attributes()->is('type', 'radio') => new Form\Field\Radio($crawler, $session),
            'input' === $tag && $node->attributes()->is('type', 'file') => new Form\Field\File($crawler, $session),
            'input' === $tag && $node->attributes()->is('type', 'submit', 'button', 'reset', 'image') => new Form\Button($crawler, $session),
            'button' === $tag => new Form\Button($crawler, $session),
            'input' === $tag => new Form\Field\Input($crawler, $session),
            'option' === $tag => new Form\Field\Select\Option($crawler, $session),
            'select' === $tag && $node->attributes()->has('multiple') => new Form\Field\Select\Multiselect($crawler, $session),
            'select' === $tag => new Form\Field\Select\Combobox($crawler, $session),
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
        if ($this->crawler instanceof PantherCrawler && 'title' === $this->tag()) {
            return $this->normalizedCrawler()->text();
        }

        return $this->crawler->text();
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
        return Nodes::create($this->crawler->ancestors(), $this->session)->first();
    }

    final public function next(): ?self
    {
        return Nodes::create($this->crawler->nextAll(), $this->session)->first();
    }

    public function previous(): ?self
    {
        return Nodes::create($this->crawler->previousAll(), $this->session)->first();
    }

    final public function closest(string $selector): ?self
    {
        $closest = $this->crawler->closest($selector);

        return $closest ? self::create($closest, $this->session) : null;
    }

    final public function ancestor(): ?self
    {
        return $this->ancestors()->first();
    }

    final public function ancestors(): Nodes
    {
        return Nodes::create($this->crawler->ancestors(), $this->session);
    }

    final public function siblings(): Nodes
    {
        return Nodes::create($this->crawler->siblings(), $this->session);
    }

    final public function children(): Nodes
    {
        return Nodes::create($this->crawler->children(), $this->session);
    }

    /**
     * @param SelectorType $selector
     */
    final public function descendant(Selector|string|callable $selector): ?self
    {
        return $this->descendants($selector)->first();
    }

    /**
     * @param SelectorType|null $selector
     */
    final public function descendants(Selector|string|callable|null $selector = null): Nodes
    {
        return Nodes::create($this->crawler, $this->session)->filter($selector ?? Selector::xpath('descendant::*'));
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

    final public function click(): void
    {
        $this->ensureSession()->click($this);
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

    final protected function ensureSession(): Session
    {
        return $this->session ?? throw new RuntimeException('No interactive session available.');
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
