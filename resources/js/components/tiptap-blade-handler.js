import { Extension } from '@tiptap/core';
import { Plugin, PluginKey } from '@tiptap/pm/state';
import { Decoration, DecorationSet } from '@tiptap/pm/view';

export const BladeConfig = Extension.create({
    name: 'bladeHandler',

    addOptions() {
        return {
            variables: [],
            onBladeCodeChange: null,
        };
    },

    addProseMirrorPlugins() {
        const variables = this.options.variables || [];

        const plugins = [
            new Plugin({
                key: new PluginKey('bladeHandler'),
                state: {
                    init() {
                        return DecorationSet.empty;
                    },
                    apply(tr, decorationSet) {
                        decorationSet = decorationSet.map(tr.mapping, tr.doc);

                        const bladeDecorations = [];
                        const bladeRegex = /\{\{.*?\}\}/g;

                        tr.doc.descendants((node, pos) => {
                            if (node.isText) {
                                let match;
                                while ((match = bladeRegex.exec(node.text)) !== null) {
                                    const from = pos + match.index;
                                    const to = from + match[0].length;

                                    bladeDecorations.push(
                                        Decoration.inline(from, to, {
                                            class: 'blade-code-highlight bg-blue-100 dark:bg-blue-900 rounded px-1',
                                            title: 'Blade Code'
                                        })
                                    );
                                }
                            }
                        });

                        return decorationSet.add(tr.doc, bladeDecorations);
                    },
                },
                props: {
                    decorations(state) {
                        return this.getState(state);
                    },
                    handleKeyDown(view, event) {
                        const { state } = view;
                        const { selection } = state;

                        if (event.key === '{') {
                            const before = state.doc.textBetween(
                                Math.max(0, selection.from - 1),
                                selection.from
                            );

                            if (before === '{') {
                                event.preventDefault();

                                const tr = state.tr;
                                tr.insertText('{ }}');
                                tr.setSelection(
                                    state.selection.constructor.near(
                                        tr.doc.resolve(selection.from + 2)
                                    )
                                );
                                view.dispatch(tr);
                                return true;
                            }
                        }

                        return false;
                    },
                },
            })
        ];

        if (variables.length > 0) {
            plugins.push(createAutocompletePlugin(variables));
        }

        return plugins;
    },

    addCommands() {
        return {
            insertVariable: (variableName) => ({ tr, state, dispatch }) => {
                const { selection } = state;
                const bladeCode = `{{ $${variableName}-> }}`;
                
                tr.insertText(bladeCode, selection.from, selection.to);
                
                // Position cursor between -> and }}
                const newPos = selection.from + bladeCode.length - 3;
                const resolvedPos = tr.doc.resolve(Math.min(newPos, tr.doc.content.size));
                tr.setSelection(state.selection.constructor.near(resolvedPos));
                
                if (dispatch) dispatch(tr);
                return true;
            },
        };
    },
});

let suggestionTooltip = null;

function createAutocompletePlugin(variables) {

        return new Plugin({
            key: new PluginKey('bladeAutocomplete'),
            state: {
                init() {
                    return {
                        active: false,
                        query: '',
                        suggestions: [],
                        selectedIndex: 0,
                    };
                },
                apply(tr, pluginState) {
                    const meta = tr.getMeta(this);
                    if (meta) {
                        return { ...pluginState, ...meta };
                    }

                    if (!tr.docChanged) return pluginState;

                    const { selection } = tr;
                    const textBefore = tr.doc.textBetween(
                        Math.max(0, selection.from - 100),
                        selection.from
                    );

                    const bladeMatch = textBefore.match(/\{\{\s*\$(\w+)(?:->([\w>-]+))?->\s*(\w*)$/);

                    if (bladeMatch) {
                        const [, variableName, chain, partial] = bladeMatch;

                        // Find the variable with matching name
                        const variable = variables.find(v => v.variableName.toLowerCase() === variableName.toLowerCase());
                        
                        if (variable) {
                            let suggestions = [];

                            if (chain) {
                                suggestions = getChainedSuggestions(variable, chain, partial);
                            } else {
                                suggestions = [
                                    ...variable.attributes,
                                    ...variable.methods
                                ].filter(item =>
                                    partial === '' || item.name.toLowerCase().startsWith(partial.toLowerCase())
                                );
                            }

                            return {
                                active: suggestions.length > 0 && (partial !== '' || textBefore.endsWith('->')),
                                query: partial,
                                suggestions: suggestions.slice(0, 10),
                                selectedIndex: 0,
                            };
                        }
                    }

                    const basicBladeMatch = textBefore.match(/\{\{\s*\$(\w+)->\s*$/);
                    if (basicBladeMatch) {
                        const [, variableName] = basicBladeMatch;

                        // Find the variable with matching name
                        const variable = variables.find(v => v.variableName.toLowerCase() === variableName.toLowerCase());
                        
                        if (variable) {
                            const suggestions = [
                                ...variable.attributes,
                                ...variable.methods
                            ];

                            return {
                                active: suggestions.length > 0,
                                query: '',
                                suggestions: suggestions.slice(0, 10),
                                selectedIndex: 0,
                            };
                        }
                    }

                    return {
                        active: false,
                        query: '',
                        suggestions: [],
                        selectedIndex: 0,
                    };
                },
            },
            props: {
                handleKeyDown(view, event) {
                    const pluginState = this.getState(view.state);
                    if (!pluginState.active) return false;

                    switch (event.key) {
                        case 'ArrowDown':
                            event.preventDefault();
                            const nextIndex = Math.min(
                                pluginState.selectedIndex + 1,
                                pluginState.suggestions.length - 1
                            );
                            const nextTr = view.state.tr.setMeta(this, {
                                ...pluginState,
                                selectedIndex: nextIndex
                            });
                            view.dispatch(nextTr);
                            updateSuggestionTooltip(view, pluginState.suggestions, nextIndex);
                            return true;

                        case 'ArrowUp':
                            event.preventDefault();
                            const prevIndex = Math.max(pluginState.selectedIndex - 1, 0);
                            const prevTr = view.state.tr.setMeta(this, {
                                ...pluginState,
                                selectedIndex: prevIndex
                            });
                            view.dispatch(prevTr);
                            updateSuggestionTooltip(view, pluginState.suggestions, prevIndex);
                            return true;

                        case 'Tab':
                            event.preventDefault();
                            if (pluginState.suggestions.length > 0) {
                                const suggestion = pluginState.suggestions[pluginState.selectedIndex];
                                insertSuggestion(view, suggestion, pluginState.query);
                            }
                            return true;

                        case 'Enter':
                            if (pluginState.suggestions.length > 0) {
                                event.preventDefault();
                                const suggestion = pluginState.suggestions[pluginState.selectedIndex];
                                insertSuggestion(view, suggestion, pluginState.query);
                                return true;
                            }
                            return false;

                        case 'Escape':
                            event.preventDefault();
                            if (suggestionTooltip) {
                                suggestionTooltip.destroy();
                                suggestionTooltip = null;
                            }
                            return true;
                    }

                    return false;
                },
            },
            view() {
                return {
                    update: (view, prevState) => {
                        const pluginState = this.key.getState(view.state);
                        const prevPluginState = this.key.getState(prevState);

                        if (pluginState.active && !prevPluginState.active) {
                            showSuggestionTooltip(view, pluginState.suggestions, pluginState.selectedIndex);
                        } else if (!pluginState.active && prevPluginState.active) {
                            hideSuggestionTooltip();
                        } else if (pluginState.active &&
                                   (pluginState.selectedIndex !== prevPluginState.selectedIndex ||
                                    pluginState.suggestions !== prevPluginState.suggestions)) {
                            updateSuggestionTooltip(view, pluginState.suggestions, pluginState.selectedIndex);
                        }
                    },
                    destroy: () => {
                        hideSuggestionTooltip();
                    }
                };
            }
        });
    }

function getChainedSuggestions(variable, chain, partial) {
    return [
        ...variable.attributes,
        ...variable.methods
    ].filter(item =>
        item.name.toLowerCase().startsWith(partial.toLowerCase())
    );
}

function showSuggestionTooltip(view, suggestions, selectedIndex) {
    if (suggestions.length === 0) return;

    const { selection } = view.state;
    const coords = view.coordsAtPos(selection.from);

    if (suggestionTooltip) {
        suggestionTooltip.destroy();
    }

    const tooltipElement = createTooltipElement(suggestions, selectedIndex);

    suggestionTooltip = window.tippy(document.body, {
        content: tooltipElement,
        showOnCreate: true,
        interactive: false,
        trigger: 'manual',
        placement: 'bottom-start',
        getReferenceClientRect: () => ({
            width: 0,
            height: 0,
            top: coords.top + 20,
            left: coords.left,
            bottom: coords.bottom,
            right: coords.right,
        }),
    });
}

function updateSuggestionTooltip(view, suggestions, selectedIndex) {
    if (!suggestionTooltip || suggestions.length === 0) return;

    const tooltipElement = createTooltipElement(suggestions, selectedIndex);
    suggestionTooltip.setContent(tooltipElement);
}

function hideSuggestionTooltip() {
    if (suggestionTooltip) {
        suggestionTooltip.destroy();
        suggestionTooltip = null;
    }
}

function createTooltipElement(suggestions, selectedIndex) {
    const container = document.createElement('div');
    container.className = 'bg-white dark:bg-secondary-800 border border-secondary-300 dark:border-secondary-600 rounded-md shadow-lg max-w-xs';

    suggestions.forEach((suggestion, index) => {
        const item = document.createElement('div');
        item.className = `px-3 py-2 text-sm cursor-pointer ${
            index === selectedIndex
                ? 'bg-primary-100 dark:bg-primary-900 text-primary-900 dark:text-primary-100'
                : 'hover:bg-secondary-100 dark:hover:bg-secondary-700 text-secondary-900 dark:text-secondary-100'
        }`;

        const name = document.createElement('div');
        name.className = 'font-medium';
        name.textContent = (suggestion.displayName || suggestion.name) + (suggestion.type === 'method' ? '()' : '');

        const description = document.createElement('div');
        description.className = 'text-xs text-secondary-500 dark:text-secondary-400';
        description.textContent = suggestion.description || '';

        item.appendChild(name);
        if (description.textContent) {
            item.appendChild(description);
        }

        container.appendChild(item);
    });

    return container;
}

function insertSuggestion(view, suggestion, query) {
    const { selection } = view.state;
    const tr = view.state.tr;

    const from = selection.from - query.length;
    const insertText = suggestion.name + (suggestion.type === 'method' ? '()' : '');

    if (query.length > 0) {
        tr.delete(from, selection.from);
    }
    tr.insertText(insertText, from);

    if (suggestion.type === 'method') {
        const newPos = from + suggestion.name.length + 1;
        const resolvedPos = tr.doc.resolve(Math.min(newPos, tr.doc.content.size));
        tr.setSelection(view.state.selection.constructor.near(resolvedPos));
    } else {
        const newPos = from + insertText.length;
        const resolvedPos = tr.doc.resolve(Math.min(newPos, tr.doc.content.size));
        tr.setSelection(view.state.selection.constructor.near(resolvedPos));
    }

    view.dispatch(tr);

    hideSuggestionTooltip();

    view.focus();
}

