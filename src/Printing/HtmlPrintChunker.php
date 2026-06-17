<?php

namespace FluxErp\Printing;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;

class HtmlPrintChunker
{
    /**
     * Split rendered HTML into top-level chunks so the PDF renderer can
     * insert page breaks between them - dompdf cannot break a single
     * table row across pages.
     *
     * @return array<int, string>
     */
    public function chunk(?string $html): array
    {
        $html = trim($html ?? '');

        if ($html === '') {
            return [];
        }

        $root = $this->parse($html);

        if (is_null($root)) {
            return [$html];
        }

        $chunks = [];
        foreach ($root->childNodes as $node) {
            $chunks = array_merge($chunks, $this->chunkNode($node));
        }

        return $chunks;
    }

    protected function renderNode(DOMNode $node): string
    {
        return (string) $node->ownerDocument->saveHTML($node);
    }

    protected function parse(string $html): ?DOMElement
    {
        $dom = new DOMDocument();
        $previousErrorSetting = libxml_use_internal_errors(true);
        $loaded = $dom->loadHTML(
            '<?xml encoding="utf-8"?><div>' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previousErrorSetting);

        return $loaded ? $dom->documentElement : null;
    }

    /**
     * @return array<int, string>
     */
    protected function chunkNode(DOMNode $node): array
    {
        if ($node instanceof DOMText) {
            return trim($node->textContent) === '' ? [] : [$this->renderNode($node)];
        }

        if (! $node instanceof DOMElement) {
            return [];
        }

        if (in_array(strtolower($node->nodeName), ['ul', 'ol'], true)) {
            return $this->splitList($node);
        }

        return [$this->renderNode($node)];
    }

    /**
     * Split a list into one list element per item, keeping ordered list
     * numbering via the start attribute.
     *
     * @return array<int, string>
     */
    protected function splitList(DOMElement $list): array
    {
        $items = $this->listItems($list);

        if (count($items) < 2) {
            return [$this->renderNode($list)];
        }

        $isOrdered = strtolower($list->nodeName) === 'ol';
        $number = max((int) ($list->getAttribute('start') ?: 1), 1);
        $lastIndex = count($items) - 1;

        $chunks = [];
        foreach ($items as $index => $item) {
            /** @var DOMElement $wrapper */
            $wrapper = $list->cloneNode();
            $wrapper->appendChild($item->cloneNode(true));

            if ($isOrdered) {
                // Continue from an explicit <li value> so author-defined
                // numbering survives the split instead of being flattened.
                if (($value = $item->getAttribute('value')) !== '') {
                    $number = (int) $value;
                }

                $wrapper->setAttribute('start', (string) $number);
                $number++;
            }

            if ($style = $this->chunkStyle($list, $index, $lastIndex)) {
                $wrapper->setAttribute('style', $style);
            }

            $chunks[] = $this->renderNode($wrapper);
        }

        return $chunks;
    }

    /**
     * @return array<int, DOMElement>
     */
    protected function listItems(DOMElement $list): array
    {
        $items = [];
        foreach ($list->childNodes as $child) {
            if ($child instanceof DOMElement && strtolower($child->nodeName) === 'li') {
                $items[] = $child;
            }
        }

        return $items;
    }

    /**
     * Keep the original list style and remove the vertical margins between
     * the resulting chunks, so they still read as one list.
     */
    protected function chunkStyle(DOMElement $list, int $index, int $lastIndex): ?string
    {
        $styles = array_filter([
            rtrim($list->getAttribute('style'), '; '),
            $index > 0 ? 'margin-top: 0' : null,
            $index < $lastIndex ? 'margin-bottom: 0' : null,
        ]);

        return $styles ? implode('; ', $styles) : null;
    }
}
