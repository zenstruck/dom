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

    protected function __construct(protected readonly Crawler $crawler, protected readonly ?Inspector $inspector)
    {
    }

    final public static function create(Crawler $crawler, ?Inspector $inspector): self
    {
        $node = new self($crawler, $inspector);
        $tag = \mb_strtolower($node->tag());

        return match (true) {
            'form' === $tag => new Form($crawler, $inspector),
            'label' === $tag => new Form\Label($crawler, $inspector),
            'textarea' === $tag => new Form\Field\Textarea($crawler, $inspector),
            'input' === $tag && $node->attributes()->is('type', 'checkbox') => new Form\Field\Checkbox($crawler, $inspector),
            'input' === $tag && $node->attributes()->is('type', 'radio') => new Form\Field\Radio($crawler, $inspector),
            'input' === $tag && $node->attributes()->is('type', 'file') => new Form\Field\File($crawler, $inspector),
            'input' === $tag && $node->attributes()->is('type', 'submit', 'button', 'reset', 'image') => new Form\Button($crawler, $inspector),
            'button' === $tag => new Form\Button($crawler, $inspector),
            'input' === $tag => new Form\Field\Input($crawler, $inspector),
            'option' === $tag => new Form\Field\Select\Option($crawler, $inspector),
            'select' === $tag && $node->attributes()->has('multiple') => new Form\Field\Select\Multiselect($crawler, $inspector),
            'select' === $tag => new Form\Field\Select\Combobox($crawler, $inspector),
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
        return $this->inspector?->isVisible($this) ?? $this->isVisibleInMarkup();
    }

    final public function isInert(): bool
    {
        return null !== $this->closest('[inert]');
    }

    final public function element(): \DOMElement
    {
        $element = $this->crawler->getNode(0);

        return $element instanceof \DOMElement ? $element : throw new RuntimeException('Unable to get DOMElement from node.');
    }

    final public function attributes(): Attributes
    {
        return $this->attributes ??= new Attributes($this->element());
    }

    final public function text(): string
    {
        return $this->inspector?->text($this) ?? $this->crawler->text();
    }

    final public function directText(): string
    {
        return $this->crawler->innerText();
    }

    final public function outerHtml(): string
    {
        return $this->crawler->outerHtml();
    }

    final public function innerHtml(): ?string
    {
        $html = $this->crawler->html();

        return '' === $html ? null : $html;
    }

    final public function parent(): ?self
    {
        return Nodes::create($this->crawler->ancestors(), $this->inspector)->first();
    }

    final public function next(): ?self
    {
        return Nodes::create($this->crawler->nextAll(), $this->inspector)->first();
    }

    final public function previous(): ?self
    {
        return Nodes::create($this->crawler->previousAll(), $this->inspector)->first();
    }

    final public function closest(string $selector): ?self
    {
        $closest = $this->crawler->closest($selector);

        return $closest ? self::create($closest, $this->inspector) : null;
    }

    final public function ancestor(): ?self
    {
        return $this->ancestors()->first();
    }

    final public function ancestors(): Nodes
    {
        return Nodes::create($this->crawler->ancestors(), $this->inspector);
    }

    final public function root(): self
    {
        return $this->ancestors()->last() ?? $this;
    }

    final public function siblings(): Nodes
    {
        return Nodes::create($this->crawler->siblings(), $this->inspector);
    }

    final public function children(): Nodes
    {
        return Nodes::create($this->crawler->children(), $this->inspector);
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
        return Nodes::create($this->crawler, $this->inspector)->filter($selector ?? Selector::xpath('descendant::*'));
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

    final public function attr(string $name): ?string
    {
        return $this->attributes()->get($name);
    }

    final public function data(string $name): ?string
    {
        return $this->attributes()->get('data-'.$name);
    }

    final public function hasClass(string $class): bool
    {
        return $this->attributes()->hasClass($class);
    }

    /**
     * @codeCoverageIgnore
     */
    final public function dump(): static
    {
        \function_exists('dump') ? dump($this->outerHtml()) : \var_dump($this->outerHtml());

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    final public function dd(): void
    {
        $this->dump();

        exit(1);
    }

    private function isVisibleInMarkup(): bool
    {
        if ($this->attributes()->has('hidden')) {
            return false;
        }

        if ($this->attributes()->is('type', 'hidden')) {
            return false;
        }

        $style = $this->attributes()->get('style') ?? '';

        if (\preg_match('/display\s*:\s*none/i', $style) || \preg_match('/visibility\s*:\s*hidden/i', $style)) {
            return false;
        }

        return true;
    }
}
