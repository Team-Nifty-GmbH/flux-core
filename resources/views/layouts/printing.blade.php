<!DOCTYPE html>
<HTML class="h-full text-sm" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <x-layouts.head.head :title="$title ?? null">
        <meta name="format-detection" content="telephone=no">
        {{ $head ?? '' }}
    </x-layouts.head.head>
    @if(! app('request')->input('preview'))
        <script src="https://unpkg.com/pagedjs/dist/paged.polyfill.js"></script>
        <script>
            class RepeatTableHeadersHandler extends Paged.Handler {
                constructor(chunker, polisher, caller) {
                    super(chunker, polisher, caller)
                    this.splitTablesRefs = []
                }

                afterPageLayout(pageElement, page, breakToken, chunker) {
                    this.chunker = chunker
                    this.splitTablesRefs = []

                    if (breakToken) {
                        const node = breakToken.node
                        const tables = this.findAllAncestors(node, "table")
                        if (node.tagName === "TABLE") tables.push(node)

                        if (tables.length > 0) {
                            this.splitTablesRefs = tables.map(t => t.dataset.ref)

                            let thead = node.tagName === "THEAD" ? node : this.findFirstAncestor(node, "thead")
                            if (thead) {
                                let lastTheadNode = thead.hasChildNodes() ? thead.lastChild : thead
                                breakToken.node = this.nodeAfter(lastTheadNode, chunker.source)
                            }

                            this.hideEmptyTables(pageElement, node)
                        }
                    }
                }

                hideEmptyTables(pageElement, breakTokenNode) {
                    this.splitTablesRefs.forEach(ref => {
                        let table = pageElement.querySelector("[data-ref='" + ref + "']")
                        if (table) {
                            let sourceBody = table.querySelector("tbody > tr")
                            if (!sourceBody || this.refEquals(sourceBody.firstElementChild, breakTokenNode)) {
                                table.style.visibility = "hidden"
                                table.style.position = "absolute"
                                let lineSpacer = table.nextSibling
                                if (lineSpacer) {
                                    lineSpacer.style.visibility = "hidden"
                                    lineSpacer.style.position = "absolute"
                                }
                            }
                        }
                    })
                }

                refEquals(a, b) {
                    return a && a.dataset && b && b.dataset && a.dataset.ref === b.dataset.ref
                }

                findFirstAncestor(element, selector) {
                    while (element.parentNode && element.parentNode.nodeType === 1) {
                        if (element.parentNode.matches(selector)) return element.parentNode
                        element = element.parentNode
                    }
                    return null
                }

                findAllAncestors(element, selector) {
                    const ancestors = []
                    while (element.parentNode && element.parentNode.nodeType === 1) {
                        if (element.parentNode.matches(selector)) ancestors.unshift(element.parentNode)
                        element = element.parentNode
                    }
                    return ancestors
                }

                layout(rendered, layout) {
                    this.splitTablesRefs.forEach(ref => {
                        const renderedTable = rendered.querySelector("[data-ref='" + ref + "']")
                        if (renderedTable) {
                            if (!renderedTable.getAttribute("repeated-headers")) {
                                const sourceTable = this.chunker.source.querySelector("[data-ref='" + ref + "']")
                                this.repeatColgroup(sourceTable, renderedTable)
                                this.repeatTHead(sourceTable, renderedTable)
                                renderedTable.setAttribute("repeated-headers", true)
                            }
                        }
                    })
                }

                repeatColgroup(sourceTable, renderedTable) {
                    let colgroup = sourceTable.querySelectorAll("colgroup")
                    let firstChild = renderedTable.firstChild
                    colgroup.forEach((colgroup) => {
                        let clonedColgroup = colgroup.cloneNode(true)
                        renderedTable.insertBefore(clonedColgroup, firstChild)
                    })
                }

                repeatTHead(sourceTable, renderedTable) {
                    let thead = sourceTable.querySelector("thead")
                    if (thead) {
                        let clonedThead = thead.cloneNode(true)
                        renderedTable.insertBefore(clonedThead, renderedTable.firstChild)
                    }
                }

                nodeAfter(node, limiter) {
                    if (limiter && node === limiter) return
                    let significantNode = this.nextSignificantNode(node)
                    if (significantNode) return significantNode
                    if (node.parentNode) {
                        while ((node = node.parentNode)) {
                            if (limiter && node === limiter) return
                            significantNode = this.nextSignificantNode(node)
                            if (significantNode) return significantNode
                        }
                    }
                }

                nextSignificantNode(sib) {
                    while ((sib = sib.nextSibling)) { if (!this.isIgnorable(sib)) return sib }
                    return null
                }

                isIgnorable(node) {
                    return (
                        (node.nodeType === 8)
                        || ((node.nodeType === 3) && this.isAllWhitespace(node))
                    )
                }

                isAllWhitespace(node) {
                    return !(/[^\t\n\r ]/.test(node.textContent))
                }
            }

            Paged.registerHandlers(RepeatTableHeadersHandler)
        </script>
    @endif
</head>
<body {{ $attributes }}>
    {{ $slot ?? '' }}
</body>
</html>
