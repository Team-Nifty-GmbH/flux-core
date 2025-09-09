<?php

namespace FluxErp\Traits\Livewire\PrintLayout;

use FluxErp\Actions\PrintLayoutSnippet\CreatePrintLayoutSnippet;

trait SnippetHandler
{
    // used to add temporary snippets on user submit (user added new snippet on front-end - not immediately reflected on back-end)
    // snippets are stored temporarily on front-end until user submits
    // previous state is preserved if user decides to cancel the operation
    /**
     * @param array $rootElement (by reference) - related to header, footer or first_page_header
     *              which may contain temporaryMedia files related to it
     * @param array $temporarySnippets - array of snippets related to header, footer or first_page_header
     *                                  saved temporarily on front-end
     * @param int $layoutId - id of the PrintLayout model
     * @return void
     */

    public function addSnippets(array &$rootElement, array $temporarySnippets, int $layoutId): void
    {
        foreach ($temporarySnippets as $temporarySnippet) {
            $snippet = CreatePrintLayoutSnippet::make(
                [
                    'print_layout_id' => $layoutId,
                    'content' => $temporarySnippet['content'],
                ]
            )->checkPermission()
                ->validate()
                ->execute();

            unset($temporarySnippet['content']);
            $temporarySnippet['id'] = $snippet->id;
            $rootElement['snippets'][] = $temporarySnippet;
        }
    }

    public function syncSnippets()
    {

    }
}
