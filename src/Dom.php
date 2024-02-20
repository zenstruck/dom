<?php

/*
 * This file is part of the zenstruck/dom package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck;

use Symfony\Component\DomCrawler\Crawler;
use Zenstruck\Dom\Assertion;
use Zenstruck\Dom\Exception\RuntimeException;
use Zenstruck\Dom\Node;
use Zenstruck\Dom\Nodes;
use Zenstruck\Dom\Selector;
use Zenstruck\Dom\Session;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @phpstan-import-type SelectorType from Selector
 */
final class Dom
{
    private Crawler $crawler;
    private Assertion $assertion;

    public function __construct(string|Crawler $crawler, private ?Session $session = null)
    {
        if (\is_string($crawler)) {
            $crawler = new Crawler($crawler);
        }

        $this->crawler = $crawler;
    }

    /**
     * @param SelectorType $selector
     */
    public function find(Selector|string|callable $selector): ?Node
    {
        return Nodes::create($this->crawler, $this->session)->first($selector);
    }

    /**
     * @param SelectorType $selector
     *
     * @throws RuntimeException If the node is not found
     */
    public function findOrFail(Selector|string|callable $selector): Node
    {
        return $this->find($selector) ?? throw new RuntimeException(\sprintf('Could not find node with selector "%s".', Selector::wrap($selector)));
    }

    /**
     * @param SelectorType $selector
     */
    public function findAll(Selector|string|callable $selector): Nodes
    {
        return Nodes::create($this->crawler, $this->session)->filter($selector);
    }

    public function crawler(): Crawler
    {
        return $this->crawler;
    }

    public function assert(): Assertion
    {
        if (!\class_exists(Assert::class)) {
            throw new \LogicException('The "zenstruck/assert" package is required to use the "assert" method. Run "composer require zenstruck/assert".');
        }

        return $this->assertion ??= new Assertion($this);
    }

    /**
     * @param SelectorType|null $selector
     */
    public function dump(Selector|string|callable|null $selector = null): static
    {
        $dump = static fn(mixed $what) => \function_exists('dump') ? dump($what) : \var_dump($what);

        null === $selector ? $dump($this->crawler->outerHtml()) : $this->findAll($selector)->dump();

        return $this;
    }

    /**
     * @param SelectorType $selector
     */
    public function dd(Selector|string|callable|null $selector = null): void
    {
        $this->dump($selector);

        exit(1);
    }
}
