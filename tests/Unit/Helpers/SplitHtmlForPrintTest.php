<?php

test('split_html_for_print returns empty array for null input', function (): void {
    expect(split_html_for_print(null))->toBe([]);
});

test('split_html_for_print returns empty array for blank input', function (): void {
    expect(split_html_for_print('   '))->toBe([]);
});

test('split_html_for_print keeps a single paragraph as one chunk', function (): void {
    $chunks = split_html_for_print('<p>Hello World</p>');

    expect($chunks)->toHaveCount(1)
        ->and($chunks[0])->toContain('Hello World');
});

test('split_html_for_print splits top level elements into separate chunks', function (): void {
    $chunks = split_html_for_print('<p>First</p><p>Second</p><div>Third</div>');

    expect($chunks)->toHaveCount(3)
        ->and($chunks[0])->toContain('First')
        ->and($chunks[1])->toContain('Second')
        ->and($chunks[2])->toContain('Third');
});

test('split_html_for_print preserves utf8 characters', function (): void {
    $chunks = split_html_for_print('<p>Größenänderung für Allgäuer Straße</p>');

    expect($chunks)->toHaveCount(1)
        ->and($chunks[0])->toContain('Größenänderung für Allgäuer Straße');
});

test('split_html_for_print keeps top level text nodes as chunks', function (): void {
    $chunks = split_html_for_print('Loose text<p>Paragraph</p>');

    expect($chunks)->toHaveCount(2)
        ->and($chunks[0])->toContain('Loose text')
        ->and($chunks[1])->toContain('Paragraph');
});

test('split_html_for_print splits unordered lists into one chunk per item', function (): void {
    $chunks = split_html_for_print('<ul><li>One</li><li>Two</li><li>Three</li></ul>');

    expect($chunks)->toHaveCount(3)
        ->and($chunks[0])->toContain('<ul')
        ->and($chunks[0])->toContain('One')
        ->and($chunks[1])->toContain('Two')
        ->and($chunks[2])->toContain('Three');
});

test('split_html_for_print removes vertical margins between list chunks', function (): void {
    $chunks = split_html_for_print('<ul><li>One</li><li>Two</li><li>Three</li></ul>');

    expect($chunks[0])->toContain('margin-bottom: 0')
        ->and($chunks[0])->not->toContain('margin-top: 0')
        ->and($chunks[1])->toContain('margin-top: 0')
        ->and($chunks[1])->toContain('margin-bottom: 0')
        ->and($chunks[2])->toContain('margin-top: 0')
        ->and($chunks[2])->not->toContain('margin-bottom: 0');
});

test('split_html_for_print continues ordered list numbering', function (): void {
    $chunks = split_html_for_print('<ol><li>One</li><li>Two</li><li>Three</li></ol>');

    expect($chunks)->toHaveCount(3)
        ->and($chunks[1])->toContain('start="2"')
        ->and($chunks[2])->toContain('start="3"');
});

test('split_html_for_print respects an existing start attribute on ordered lists', function (): void {
    $chunks = split_html_for_print('<ol start="5"><li>Five</li><li>Six</li></ol>');

    expect($chunks)->toHaveCount(2)
        ->and($chunks[0])->toContain('start="5"')
        ->and($chunks[1])->toContain('start="6"');
});

test('split_html_for_print keeps single item lists unsplit', function (): void {
    $chunks = split_html_for_print('<ul><li>Only</li></ul>');

    expect($chunks)->toHaveCount(1)
        ->and($chunks[0])->toBe('<ul><li>Only</li></ul>');
});

test('split_html_for_print keeps nested lists inside their item', function (): void {
    $chunks = split_html_for_print('<ul><li>Parent<ul><li>Child</li></ul></li><li>Second</li></ul>');

    expect($chunks)->toHaveCount(2)
        ->and($chunks[0])->toContain('Child')
        ->and($chunks[1])->toContain('Second');
});

test('split_html_for_print preserves attributes on split lists', function (): void {
    $chunks = split_html_for_print('<ul class="fancy"><li>One</li><li>Two</li></ul>');

    expect($chunks)->toHaveCount(2)
        ->and($chunks[0])->toContain('class="fancy"')
        ->and($chunks[1])->toContain('class="fancy"');
});

test('split_html_for_print concatenated chunks contain all original text', function (): void {
    $html = '<p>Intro</p><ul><li>A</li><li>B</li></ul><p>Outro</p>';

    $joined = implode('', split_html_for_print($html));

    expect(strip_tags($joined))->toBe('IntroABOutro');
});
