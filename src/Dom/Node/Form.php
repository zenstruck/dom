<?php

/*
 * This file is part of the zenstruck/dom package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Dom\Node;

use Symfony\Component\DomCrawler\Crawler;
use Zenstruck\Dom\Node;
use Zenstruck\Dom\Node\Form\Button;
use Zenstruck\Dom\Node\Form\Field;
use Zenstruck\Dom\Nodes;
use Zenstruck\Dom\Selector;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @phpstan-import-type SelectorType from Selector
 */
final class Form extends Node
{
    public const SELECTOR = 'form';

    public function fields(Selector|string|callable $selector = Field::SELECTOR): Nodes
    {
        return $this->findNodesForForm($selector);
    }

    public function buttons(): Nodes
    {
        return $this->findNodesForForm(Button::SELECTOR);
    }

    public function submitButtons(): Nodes
    {
        return $this->findNodesForForm('input[type="submit"],button[type="submit"]');
    }

    public function submitButton(): ?Button
    {
        return $this->submitButtons()->first()?->ensure(Button::class);
    }

    private function findNodesForForm(Selector|string|callable $selector): Nodes
    {
        $formId = $this->attributes()->get('id');

        // Filter out nodes that explicitly have a "form" attribute
        $directDescendantsCrawler = $this->descendants($selector)
            ->crawler()
            ->reduce(function (Crawler $crawler) {
                return !$crawler->getNode(0)?->attributes?->getNamedItem('form');
        });

        // If the form doesn't have an id, return the nodes that match the selector and that don't have a "form" attribute.
        if (!\is_string($formId) || '' === $formId) {
            return Nodes::create($directDescendantsCrawler, $this->session);
        }

        // Find nodes in all the document that match the selector and have a "form" attribute that matches the form's id.
        $referencingNodesCrawler = $this->ancestors()->last()
            ?->descendants($selector)
            ->crawler()
            ->reduce(function (Crawler $crawler) use ($formId) {
                return $formId === $crawler->getNode(0)?->attributes?->getNamedItem('form')?->nodeValue;
            });

        if (null !== $referencingNodesCrawler && $referencingNodesCrawler->count() > 0) {
            // Merge descendant nodes and nodes with a matching "form" attribute.
            $directDescendantsCrawler->addNodes(\iterator_to_array($referencingNodesCrawler->getIterator()));
        }

        return Nodes::create($directDescendantsCrawler, $this->session);
    }
}
