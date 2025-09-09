<?php

namespace FluxErp\Traits\Livewire\PrintLayout;

use FluxErp\Actions\PrintLayoutSnippet\CreatePrintLayoutSnippet;
use FluxErp\Actions\PrintLayoutSnippet\DeletePrintLayoutSnippet;
use FluxErp\Actions\PrintLayoutSnippet\UpdatePrintLayoutSnippet;

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

    // used to sync snippets between front-end and back-end on user submit (user edited/deleted the snippet on front-end)
    // editing/deleting snippets on frond-end will not be immediately reflected on back-end
    // in order to preserve previous state if user decides to cancel the operation
    /**
     * @param array $rootElement - related to header, footer or first_page_header
     *                                  containing latest media snapshot from front-end
     * @param array $dbSnapshot - snapshot of media related to header, footer or first_page_header
     *                            before user started editing it on front-end
     * @return void
     */
    public function syncSnippets(array &$rootElement, array $dbSnapshot): void
    {
        $diff =  array_diff(array_column($dbSnapshot,'id'),array_column($rootElement['snippets'] ?? [],'id'));

        // delete snippets that are in dbSnapshot but not in rootElement['snippets']
        if($diff) {
            foreach ($diff as $snippetId) {
                DeletePrintLayoutSnippet::make([
                    'id' => $snippetId,
                ])->checkPermission()
                    ->validate()
                    ->execute();
            }
        }

        // update remaining snippets
        if($rootElement['snippets']) {
            foreach ($rootElement['snippets'] as &$snippet) {
            UpdatePrintLayoutSnippet::make([
                'id' => $snippet['id'],
                'content' => $snippet['content'],
            ])
                ->checkPermission()
                ->validate()
                ->execute();

            unset($snippet['content']);
            }
            // clean up - to ensure in place changes are reflected outside the method
            unset($snippet);
        }

    }
}
