# zenstruck/dom

[![CI](https://github.com/zenstruck/dom/actions/workflows/ci.yml/badge.svg)](https://github.com/zenstruck/dom/actions/workflows/ci.yml)
[![Code Coverage](https://codecov.io/gh/zenstruck/dom/branch/1.x/graph/badge.svg?token=R7OHYYGPKM)](https://codecov.io/gh/zenstruck/dom)
[![Latest Version](https://img.shields.io/packagist/v/zenstruck/dom.svg)](https://packagist.org/packages/zenstruck/dom)

Functional testing with Symfony can be verbose when working with the DOM. This library provides
an expressive, auto-completable, fluent wrapper around Symfony's native DomCrawler with intelligent
selector resolution and a chainable assertion API:

```php
public function testViewPostAndComments()
{
    $dom = new Dom($this->client->request('GET', '/posts/3'));

    $dom->assert()
        ->contains('My First Post')
        ->hasElement('h1')
        ->hasElementCount('#comments li', 2)
        ->fieldEquals('Search', '')
        ->fieldChecked('Subscribe')
        ->fieldSelected('Category', 'Technology')
    ;

    // find elements with intelligent selector resolution
    $title = $dom->findOrFail('h1');
    $title->text(); // "My First Post"

    // form elements are automatically typed
    $select = $dom->findOrFail('Category')->ensure(Combobox::class);
    $select->selectedText(); // "Technology"
}
```

Combine this library with [zenstruck/browser](https://github.com/zenstruck/browser) to get
the full fluent browser testing experience:

```php
public function testViewPostAndAddComment()
{
    $this->browser()
        ->visit('/posts/3')
        ->assertSuccessful()
        ->assertSeeIn('h1', 'My First Post')
        ->fillField('Comment', 'Great post!')
        ->click('Submit')
        ->assertSeeIn('#comments', 'Great post!')
    ;
}
```

## Installation

```
composer require zenstruck/dom --dev
```

> [!NOTE]
> For assertions, `zenstruck/assert` is required: `composer require --dev zenstruck/assert`.

## Usage

```php
use Zenstruck\Dom;

/** @var \Zenstruck\Dom $dom */
$dom = new Dom($html); // string, Crawler, or Response

// FINDING ELEMENTS
$dom->find('h1');                   // Node|null - first matching element
$dom->findOrFail('h1');             // Node - throws if not found
$dom->findAll('li');                // Nodes - collection of matching elements

// SELECTOR RESOLUTION (strings are auto-detected in this order)
$dom->find('.my-class');            // 1. CSS selector
$dom->find('Submit');               // 2. Button text/value
$dom->find('Click here');           // 3. Link text/title
$dom->find('Logo');                 // 4. Image alt text
$dom->find('main-content');         // 5. Element ID
$dom->find('email');                // 6. Field name attribute
$dom->find('Email Address');        // 7. Field label text

// EXPLICIT SELECTORS (force a specific type)
$dom->find(Selector::css('.my-class'));
$dom->find(Selector::xpath('//div[@class="foo"]'));
$dom->find(Selector::id('main-content'));
$dom->find(Selector::button('Submit'));
$dom->find(Selector::link('Click here'));
$dom->find(Selector::image('Logo'));
$dom->find(Selector::field('email'));              // by name or label
$dom->find(Selector::fieldForName('email'));       // by name only
$dom->find(Selector::fieldForLabel('Email'));      // by label only
$dom->find(Selector::clickable('Submit'));         // buttons first, then links

// SEPARATOR SYNTAX (inline type forcing)
$dom->find('css:==:.my-class');
$dom->find('xpath:==://div[@class="foo"]');
$dom->find('id:==:main-content');
$dom->find('button:==:Submit');
$dom->find('link:==:Click here');
$dom->find('field:==:email');

// CALLBACK SELECTORS
$dom->find(function (Dom $dom): ?Node {
    return $dom->find('ul')?->children()->first();
});
```

### Node

Every matched element is returned as a `Zenstruck\Dom\Node` (or a more specific subclass
for form elements):

```php
/** @var \Zenstruck\Dom\Node $node */

// TRAVERSAL
$node->parent();              // immediate parent Node or null
$node->ancestors();           // all ancestor Nodes
$node->children();            // direct child Nodes
$node->siblings();            // sibling Nodes
$node->next();                // next sibling Node or null
$node->previous();            // previous sibling Node or null
$node->closest('form');       // closest ancestor matching selector
$node->descendant('.item');   // first descendant matching selector
$node->descendants('.item');  // all descendants matching selector

// CONTENT
$node->text();                // full text content (including children)
$node->directText();          // only the node's own text
$node->outerHtml();           // the node's outer HTML
$node->innerHtml();           // the node's inner HTML

// INTROSPECTION
$node->tag();                 // tag name (e.g. "div", "input")
$node->id();                  // value of id attribute or null
$node->isVisible();           // visibility check
$node->attributes();          // Attributes object

// TYPE GUARDS
$node->is(Checkbox::class);            // true/false
$node->ensure(Checkbox::class);        // returns typed node or throws
```

### Nodes Collection

`findAll()` and traversal methods return a `Zenstruck\Dom\Nodes` collection:

```php
/** @var \Zenstruck\Dom\Nodes $nodes */

$nodes->count();               // number of matched nodes
$nodes->first();               // first Node or null
$nodes->last();                // last Node or null
$nodes->filter('.active');     // narrow down with a selector
$nodes->text();                // concatenated text of all nodes
$nodes->html();                // concatenated outer HTML
$nodes->map(fn(Node $n) => $n->text()); // map to array

foreach ($nodes as $node) {
    // iterable
}
```

### Form Elements

Nodes are automatically resolved to their specific form element type:

```php
/** @var \Zenstruck\Dom $dom */

// INPUT
$input = $dom->findOrFail(Selector::field('email'))->ensure(Input::class);
$input->value();              // current value
$input->type();               // "text", "email", "password", etc.
$input->fill('new value');    // requires Session

// TEXTAREA
$textarea = $dom->findOrFail(Selector::field('bio'))->ensure(Textarea::class);
$textarea->value();
$textarea->fill('new text');  // requires Session

// CHECKBOX
$checkbox = $dom->findOrFail(Selector::field('terms'))->ensure(Checkbox::class);
$checkbox->isChecked();
$checkbox->check();           // requires Session
$checkbox->uncheck();         // requires Session

// RADIO
$radio = $dom->findOrFail(Selector::field('gender'))->ensure(Radio::class);
$radio->isSelected();
$radio->selected();           // the selected Radio node
$radio->selectedValue();
$radio->select();             // requires Session

// COMBOBOX (single select)
$select = $dom->findOrFail(Selector::field('country'))->ensure(Combobox::class);
$select->selectedOption();    // Option node
$select->selectedValue();
$select->selectedText();
$select->availableOptions();  // all Option nodes
$select->select('Canada');    // requires Session

// MULTISELECT
$multi = $dom->findOrFail(Selector::field('roles'))->ensure(Multiselect::class);
$multi->selectedOptions();    // array of Option nodes
$multi->selectedValues();
$multi->selectedTexts();
$multi->select(['Admin', 'Editor']); // requires Session
$multi->deselectAll();        // requires Session

// FILE
$file = $dom->findOrFail(Selector::field('photo'))->ensure(File::class);
$file->isMultiple();
$file->attach('/path/to/file.jpg'); // requires Session

// BUTTON
$button = $dom->findOrFail(Selector::button('Submit'))->ensure(Button::class);
$button->type();              // "submit", "button", "reset"
$button->value();

// COMMON FIELD METHODS (all fields inherit from Field)
$field->name();               // name attribute
$field->value();              // current value
$field->label();              // associated Label node or null
$field->isDisabled();
$field->form();               // parent Form node

// FORM
$form = $dom->findOrFail('form')->ensure(Form::class);
$form->fields();              // all field Nodes
$form->buttons();             // all button Nodes
$form->submitButtons();       // submit-type buttons only
$form->submitButton();        // first submit button
```

## Assertions

All assertion methods return `$this` for chaining:

```php
/** @var \Zenstruck\Dom $dom */

$dom->assert()
    // TEXT CONTENT
    ->contains('some text')                      // page contains text
    ->doesNotContain('some text')                // page does not contain text
    ->containsIn('h1', 'some text')              // element contains text
    ->doesNotContainIn('h1', 'some text')        // element does not contain text

    // ELEMENT PRESENCE
    ->hasElement('nav')                          // element exists
    ->doesNotHaveElement('nav')                  // element does not exist
    ->hasElementCount('ul li', 5)                // exact count

    // VISIBILITY
    ->elementIsVisible('#modal')                 // element is visible
    ->elementIsNotVisible('#modal')              // element is not visible

    // ATTRIBUTES
    ->attributeContains('body', 'class', 'dark') // attribute contains value
    ->attributeDoesNotContain('body', 'class', 'light')

    // FORM FIELDS
    ->fieldEquals('Username', 'kevin')           // field value equals
    ->fieldDoesNotEqual('Username', 'john')      // field value does not equal
    ->fieldChecked('Remember me')                // checkbox checked or radio selected
    ->fieldNotChecked('Remember me')             // checkbox not checked
    ->fieldSelected('Role', 'Admin')             // option is selected
    ->fieldNotSelected('Role', 'Guest')          // option is not selected
;
```

## Session Interface

The `Session` interface enables interactive behavior. When provided to the `Dom` constructor,
form elements can perform actions (clicking, filling, selecting):

```php
interface Session
{
    public function click(Node $node): void;
    public function select(Checkbox|Radio|Option $node): void;
    public function unselect(Checkbox|Multiselect $node): void;
    public function attach(File $node, array $filenames): void;
    public function fill(Input|Textarea $node, string $value): void;
}
```

> [!TIP]
> This interface is implemented by [zenstruck/browser](https://github.com/zenstruck/browser),
> allowing the same DOM API to drive real browser interactions.

## Known Limitations

> [!NOTE]
> **XPath case folding is ASCII-only.** The `translate()` function used for case-insensitive
> matching only handles A-Z. Non-ASCII characters are not case-normalized (XPath 1.0 limitation).

> [!NOTE]
> **`isVisible()` performs basic checks only.** It detects `hidden` attributes, `type="hidden"`
> inputs, and inline `display:none`/`visibility:hidden` styles. It does not evaluate CSS
> stylesheets or inherited styles.

## Testing

```bash
# unit tests
vendor/bin/phpunit

# functional browser tests (requires chromedriver/geckodriver)
vendor/bin/phpunit --testsuite Functional

# install browser drivers if missing
vendor/bin/bdi detect drivers
```