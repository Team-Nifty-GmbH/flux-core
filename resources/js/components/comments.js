export default function comments() {
    return {
        comments: [],
        stickyComments: [],
        initialized: false,

        async init() {
            Promise.all([
                this.$wire.loadComments(),
                this.$wire.loadStickyComments(),
            ]).then(([commentsResponse, stickyCommentsResponse]) => {
                this.comments = commentsResponse.data;
                this.stickyComments = stickyCommentsResponse;
                this.initialized = true;
            });
        },

        loadMore() {
            if (!this.initialized) {
                return;
            }

            this.$wire.loadMoreComments().then((response) => {
                this.comments.push(...response.data); // Append new comments
            });
        },

        toggleSticky(node) {
            this.$wire.toggleSticky(node.id);
            node.is_sticky = !node.is_sticky;

            // add it to stickyComments if its sticky now
            if (node.is_sticky) {
                this.stickyComments.push(node);
            } else {
                const stickyNodeIndex = this.stickyComments.findIndex(
                    (item) => item.id === node.id,
                );
                if (stickyNodeIndex !== -1) {
                    this.stickyComments.splice(stickyNodeIndex, 1);
                }
            }
        },

        async saveComment(content, files, sticky, internal, parentNode = null) {
            const editor = Alpine.$data(
                content.querySelector("[x-data]"),
            ).editor();

            let child = await this.$wire.saveComment(
                {
                    comment: editor.getHTML(),
                    is_sticky: sticky.checked,
                    is_internal: internal,
                    parent_id: parentNode !== null ? parentNode.id : null,
                },
                files,
            );

            if (child === null) {
                return false;
            }

            if (parentNode !== null) {
                if (!parentNode.hasOwnProperty("children")) {
                    parentNode.children = [];
                }

                parentNode.children.unshift(child);
            } else {
                this.comments.unshift(child);
            }

            editor.commands.setContent("", false);

            sticky.checked = false;
            this.$refs.comments
                .querySelectorAll(".comment-input")
                .forEach(function (el) {
                    el.remove();
                });

            return true;
        },
        removeNode(node) {
            const nodeId = typeof node === "object" ? node.id : node;

            const traverseAndRemove = (nodes, parent = null) => {
                for (let i = 0; i < nodes.length; i++) {
                    if (nodes[i].id === nodeId) {
                        nodes.splice(i, 1);
                        return true; // Node found and removed
                    }

                    if (nodes[i]["children"]) {
                        const childNodes = nodes[i]["children"];
                        if (traverseAndRemove(childNodes, nodes[i])) {
                            // Remove the parent node if it has no more children
                            if (childNodes.length === 0) {
                                delete nodes[i]["children"];
                            }
                            return true;
                        }
                    }
                }
                return false; // Node not found
            };

            traverseAndRemove(this.comments);

            const stickyNodeIndex = this.stickyComments.findIndex(
                (item) => item.id === nodeId,
            );
            if (stickyNodeIndex !== -1) {
                this.stickyComments.splice(stickyNodeIndex, 1);
            }
        },
    };
}
