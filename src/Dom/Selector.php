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
use Zenstruck\Dom;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @phpstan-type SelectorType self|string|callable(Dom):(Node|Nodes|Crawler|null)
 */
final class Selector implements \Stringable
{
    private const TYPE_AUTO = 'auto';
    private const TYPE_CSS = 'css';
    private const TYPE_ID = 'id';
    private const TYPE_FIELD = 'field';
    private const TYPE_FIELD_FOR_NAME = 'field-for-name';
    private const TYPE_FIELD_FOR_LABEL = 'field-for-label';
    private const TYPE_CLICKABLE = 'clickable';
    private const TYPE_LINK = 'link';
    private const TYPE_BUTTON = 'button';
    private const TYPE_IMAGE = 'image';
    private const TYPE_XPATH = 'xpath';
    private const TYPE_TESTID = 'testid';
    private const TYPE_CALLBACK = '(callback)';
    private const SEPARATOR = ':==:';
    private const SEPARATOR_FORMAT = '%s'.self::SEPARATOR.'%s';
    private const XPATH_FIELD = '*[self::input or self::select or self::textarea]';
    private const VALID_TYPES = [
        self::TYPE_AUTO,
        self::TYPE_CSS,
        self::TYPE_ID,
        self::TYPE_FIELD,
        self::TYPE_FIELD_FOR_NAME,
        self::TYPE_FIELD_FOR_LABEL,
        self::TYPE_CLICKABLE,
        self::TYPE_LINK,
        self::TYPE_BUTTON,
        self::TYPE_IMAGE,
        self::TYPE_XPATH,
        self::TYPE_TESTID,
    ];
    private const PRIORITY_MAP = [
        self::TYPE_AUTO => [self::TYPE_CSS, self::TYPE_BUTTON, self::TYPE_LINK, self::TYPE_IMAGE, self::TYPE_ID, self::TYPE_FIELD_FOR_NAME, self::TYPE_FIELD_FOR_LABEL],
        self::TYPE_CLICKABLE => [self::TYPE_BUTTON, self::TYPE_LINK, self::TYPE_ID, self::TYPE_CSS, self::TYPE_IMAGE, self::TYPE_FIELD_FOR_NAME, self::TYPE_FIELD_FOR_LABEL],
        self::TYPE_FIELD => [self::TYPE_FIELD_FOR_NAME, self::TYPE_FIELD_FOR_LABEL, self::TYPE_ID, self::TYPE_CSS],
    ];

    /**
     * @param self::TYPE_* $type
     */
    private function __construct(private string $type, private string|\Closure $value)
    {
    }

    public function __toString(): string
    {
        if ($this->value instanceof \Closure) {
            return self::TYPE_CALLBACK;
        }

        if (self::TYPE_AUTO === $this->type) {
            return $this->value;
        }

        return \sprintf(self::SEPARATOR_FORMAT, $this->type, $this->value);
    }

    /**
     * @param SelectorType $value
     */
    public static function wrap(self|string|callable $value): self
    {
        return self::createForDefault(self::TYPE_AUTO, $value);
    }

    /**
     * @param SelectorType $value
     */
    public static function css(self|string|callable $value): self
    {
        return self::createForDefault(self::TYPE_CSS, $value);
    }

    /**
     * @param SelectorType $value
     */
    public static function id(self|string|callable $value): self
    {
        return self::createForDefault(self::TYPE_ID, $value);
    }

    /**
     * @param SelectorType $value
     */
    public static function field(self|string|callable $value): self
    {
        return self::createForDefault(self::TYPE_FIELD, $value);
    }

    /**
     * @param SelectorType $value
     */
    public static function fieldForName(self|string|callable $value): self
    {
        return self::createForDefault(self::TYPE_FIELD_FOR_NAME, $value);
    }

    /**
     * @param SelectorType $value
     */
    public static function fieldForLabel(self|string|callable $value): self
    {
        return self::createForDefault(self::TYPE_FIELD_FOR_LABEL, $value);
    }

    /**
     * @param SelectorType $value
     */
    public static function clickable(self|string|callable $value): self
    {
        return self::createForDefault(self::TYPE_CLICKABLE, $value);
    }

    /**
     * @param SelectorType $value
     */
    public static function link(self|string|callable $value): self
    {
        return self::createForDefault(self::TYPE_LINK, $value);
    }

    /**
     * @param SelectorType $value
     */
    public static function button(self|string|callable $value): self
    {
        return self::createForDefault(self::TYPE_BUTTON, $value);
    }

    /**
     * @param SelectorType $value
     */
    public static function image(self|string|callable $value): self
    {
        return self::createForDefault(self::TYPE_IMAGE, $value);
    }

    /**
     * @param SelectorType $value
     */
    public static function xpath(self|string|callable $value): self
    {
        return self::createForDefault(self::TYPE_XPATH, $value);
    }

    /**
     * @param SelectorType $value
     */
    public static function testId(self|string|callable $value): self
    {
        return self::createForDefault(self::TYPE_TESTID, $value);
    }

    /**
     * @internal Do not use outside of zenstruck/dom. Subject to removal or signature changes without notice.
     */
    public function filter(Crawler $crawler, ?Inspector $inspector = null): Crawler
    {
        if ($this->value instanceof \Closure) {
            $return = ($this->value)(new Dom($crawler, $inspector));

            return match (true) {
                $return instanceof Crawler => $return,
                $return instanceof Node, $return instanceof Nodes => $return->crawler(),
                null === $return => new Crawler(),
                default => throw new \LogicException(\sprintf('Invalid return type from selector callback, expected %s|%s|%s|null, got "%s".', Node::class, Nodes::class, Crawler::class, \get_debug_type($return))),
            };
        }

        if (!isset(self::PRIORITY_MAP[$this->type])) {
            // an explicit type reports a malformed selector rather than "not found"
            return self::filterByType($crawler, $this->type, $this->value);
        }

        foreach (self::PRIORITY_MAP[$this->type] as $type) {
            try {
                $filtered = self::filterByType($crawler, $type, $this->value);
            } catch (\InvalidArgumentException|\Symfony\Component\CssSelector\Exception\ParseException) {
                $filtered = new Crawler();
            }

            if (\count($filtered)) {
                return $filtered;
            }
        }

        return new Crawler();
    }

    private static function filterByType(Crawler $crawler, string $type, string $value): Crawler
    {
        return match ($type) {
            self::TYPE_CSS => $crawler->filter($value),
            self::TYPE_ID => $crawler->filterXPath(\sprintf('descendant-or-self::*[@id = %s]', self::xpathQuote(\ltrim($value, '#')))),
            self::TYPE_LINK => self::filterLink($crawler, $value),
            self::TYPE_BUTTON => $crawler->selectButton($value),
            self::TYPE_IMAGE => $crawler->selectImage($value),
            self::TYPE_FIELD_FOR_NAME => $crawler->filterXPath(\sprintf('descendant-or-self::%s[@name = %s]', self::XPATH_FIELD, self::xpathQuote($value))),
            self::TYPE_FIELD_FOR_LABEL => self::filterFieldForLabel($crawler, $value),
            self::TYPE_XPATH => $crawler->filterXPath($value),
            self::TYPE_TESTID => $crawler->filterXPath(\sprintf('descendant-or-self::*[@data-testid = %s]', self::xpathQuote($value))),
            default => throw new \InvalidArgumentException(\sprintf('Invalid type "%s".', $type)),
        };
    }

    private static function filterLink(Crawler $crawler, string $value): Crawler
    {
        if (\count($link = $crawler->selectLink($value))) {
            return $link;
        }

        // try with partial link text
        if (\count($link = self::filterByType($crawler, self::TYPE_XPATH, self::xpathMatching('a', $value, exact: false)))) {
            return $link;
        }

        // try with title tag
        return self::filterByType($crawler, self::TYPE_XPATH, self::xpathMatching('a', $value, exact: false, attribute: 'title'));
    }

    /**
     * Matches the field's accessible name: a <label>, aria-labelledby, aria-label or placeholder.
     */
    private static function filterFieldForLabel(Crawler $crawler, string $value): Crawler
    {
        // an exact match from any source beats a partial one
        foreach ([true, false] as $exact) {
            if (\count($field = self::filterFieldLabelledBy($crawler, $value, $exact))) {
                return $field;
            }
        }

        return new Crawler();
    }

    private static function filterFieldLabelledBy(Crawler $crawler, string $value, bool $exact): Crawler
    {
        // the first label may not point at a field (eg a label for a custom widget)
        foreach (self::filterByType($crawler, self::TYPE_XPATH, self::xpathMatching('label', $value, $exact)) as $label) {
            if (\count($field = self::fieldForLabelElement($crawler, new Crawler($label, $crawler->getUri(), $crawler->getBaseHref())))) {
                return $field;
            }
        }

        foreach (self::filterByType($crawler, self::TYPE_XPATH, self::xpathMatching('*[@id]', $value, $exact)) as $labelling) {
            if (\count($field = self::fieldLabelledByElement($crawler, $labelling))) {
                return $field;
            }
        }

        if (\count($field = self::filterByType($crawler, self::TYPE_XPATH, self::xpathMatching(self::XPATH_FIELD, $value, $exact, 'aria-label')))) {
            return $field;
        }

        return self::filterByType($crawler, self::TYPE_XPATH, self::xpathMatching(self::XPATH_FIELD, $value, $exact, 'placeholder'));
    }

    private static function fieldLabelledByElement(Crawler $crawler, \DOMNode $labelling): Crawler
    {
        if (!$labelling instanceof \DOMElement) {
            return new Crawler();
        }

        return self::filterByType($crawler, self::TYPE_XPATH, \sprintf(
            'descendant-or-self::%s[contains(concat(" ", normalize-space(@aria-labelledby), " "), %s)]',
            self::XPATH_FIELD,
            self::xpathQuote(' '.$labelling->getAttribute('id').' '),
        ));
    }

    private static function fieldForLabelElement(Crawler $crawler, Crawler $label): Crawler
    {
        if ($id = $label->attr('for')) {
            return self::filterByType($crawler, self::TYPE_ID, $id)->filter('input,select,textarea');
        }

        // try and find field nested in label
        return $label->filter('input,select,textarea');
    }

    /**
     * @param self::TYPE_* $type
     * @param SelectorType $value
     */
    private static function createForDefault(string $type, self|string|callable $value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        if (\is_callable($value) && !\is_string($value)) {
            return new self(self::TYPE_CALLBACK, \Closure::fromCallable($value));
        }

        if (1 === \count($parts = \explode(self::SEPARATOR, $value, 2))) {
            return new self($type, $value);
        }

        if (!\in_array($parts[0], self::VALID_TYPES, true)) {
            $parts[0] = $type;
        }

        return new self($parts[0], $parts[1]);
    }

    private static function xpathMatching(string $element, string $value, bool $exact, ?string $attribute = null): string
    {
        $value = \mb_strtolower($value);
        $format = $exact ? 'descendant-or-self::%s[%s = %s]' : 'descendant-or-self::%s[contains(%s, %s)]';

        return \sprintf($format, $element, self::xpathNormalize($attribute, $value), self::xpathQuote($value));
    }

    private static function xpathQuote(string $value): string
    {
        if (!\str_contains($value, '"')) {
            return \sprintf('"%s"', $value);
        }

        if (!\str_contains($value, "'")) {
            return \sprintf("'%s'", $value);
        }

        $parts = [];

        foreach (\explode('"', $value) as $i => $part) {
            if ($i > 0) {
                $parts[] = "'\"'";
            }

            if ('' !== $part) {
                $parts[] = \sprintf('"%s"', $part);
            }
        }

        return \sprintf('concat(%s)', \implode(', ', $parts));
    }

    /**
     * XPath 1.0 can only case-fold through translate(), so the table holds the uppercase forms of the
     * (lowercased) needle's characters, in any script: any other uppercase character could never
     * match the needle anyway.
     */
    private static function xpathNormalize(?string $attribute, string $needle): string
    {
        $subject = \sprintf('normalize-space(%s)', $attribute ? '@'.\ltrim($attribute, '@') : '.');
        $upper = $lower = '';

        foreach (\array_unique(\mb_str_split($needle)) as $char) {
            $variants = self::uppercaseVariants($char);
            $upper .= \implode('', $variants);
            $lower .= \str_repeat($char, \count($variants));
        }

        if ('' === $upper) {
            return $subject;
        }

        return \sprintf('translate(%s, %s, %s)', $subject, self::xpathQuote($upper), self::xpathQuote($lower));
    }

    /**
     * Upper and title case differ for digraphs (eg "ǆ" has "Ǆ" and "ǅ"). translate() maps single
     * characters, so a multi-character form (eg "ß" to "SS") cannot be folded.
     *
     * @return list<string>
     */
    private static function uppercaseVariants(string $char): array
    {
        $variants = \array_unique([\mb_strtoupper($char), \mb_convert_case($char, \MB_CASE_TITLE)]);

        return \array_values(\array_filter($variants, static fn(string $variant) => $variant !== $char && 1 === \mb_strlen($variant)));
    }
}
