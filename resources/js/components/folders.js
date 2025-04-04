import { v4 as uuidv4 } from 'uuid';
import { destroy } from 'filepond';

export default function folders(
    getTreePromise,
    property = null,
    checked = [],
    multiSelect = false,
    nameAttribute = 'label',
    childrenAttribute = 'children',
    parentIdAttribute = 'parent_id',
    selectedCallback = null,
    checkedCallback = null,
    searchAttributes = null,
) {
    return {
        checked: checked,
        selected: null,
        openFolders: [],
        tree: [],
        search: null,
        searchAttributes: searchAttributes,
        property: property,
        getTreePromise: getTreePromise,
        multiSelect: multiSelect,
        nameAttribute: nameAttribute,
        childrenAttribute: childrenAttribute,
        parentIdAttribute: parentIdAttribute,
        selectedCallback: selectedCallback,
        checkedCallback: checkedCallback,

        async init() {
            await this.refresh();
            this.checkedCallback = this.checkedCallback?.bind(this);

            if (typeof this.property === 'string') {
                this.$watch(property, (newFolders) => {
                    this.tree = newFolders;
                });
            }
        },
        async refresh() {
            this.tree = [];
            try {
                this.tree = await getTreePromise;
            } catch (error) {
                console.error('Error fetching the tree structure:', error);
                this.tree = [];
            }
        },
        getSearchAttributes() {
            return this.searchAttributes || [this.nameAttribute];
        },
        toggleOpen(node, event) {
            if (event?.shiftKey) {
                if (this.openFolders.includes(node.id)) {
                    this.closeAllSubfolders(node);
                } else {
                    this.openAllSubfolders(node);
                }
            } else {
                if (this.openFolders.includes(node.id)) {
                    this.closeFolder(node);
                } else {
                    this.openFolder(node);
                }
            }
        },
        openFolder(node) {
            if (!this.openFolders.includes(node.id)) {
                this.openFolders.push(node.id);
            }
        },
        closeFolder(node) {
            this.openFolders = this.openFolders.filter((id) => id !== node.id);
        },
        openAllSubfolders(node) {
            const traverse = (currentNode) => {
                if (!this.openFolders.includes(currentNode.id)) {
                    this.openFolder(currentNode);
                }
                currentNode.children?.forEach((child) => traverse(child));
            };
            traverse(node);
        },
        closeAllSubfolders(node) {
            const traverse = (currentNode) => {
                this.closeFolder(currentNode);
                currentNode.children?.forEach((child) => traverse(child));
            };
            traverse(node);
        },
        isOpen(node) {
            return this.openFolders.includes(node.id);
        },
        isLeaf(node) {
            return !Array.isArray(node.children) || node.children.length === 0;
        },
        getNodeById(nodeId) {
            if (!nodeId) return null;

            const traverse = (nodes) => {
                for (let i = 0; i < nodes.length; i++) {
                    if (nodes[i].id === nodeId) {
                        return nodes[i];
                    }

                    if (nodes[i][this.childrenAttribute]) {
                        const found = traverse(
                            nodes[i][this.childrenAttribute],
                        );
                        if (found) return found;
                    }
                }
                return null;
            };

            return traverse(this.tree);
        },
        toggleCheck(node, isChecked) {
            const traverse = (currentNode, check) => {
                if (check) {
                    if (!this.checked.includes(currentNode.id)) {
                        this.check(currentNode);
                    }
                } else {
                    this.unCheck(currentNode);
                }
                this.$dispatch(
                    'folder-tree-check-toggle',
                    currentNode,
                    isChecked,
                );

                currentNode.children?.forEach((child) =>
                    traverse(child, check),
                );
            };
            traverse(node, isChecked);

            this.$dispatch('folder-tree-check-updated', this.checked);
        },
        unCheck(node) {
            this.checked = this.checked.filter((id) => id !== node.id);
            this.$dispatch('folder-tree-uncheck', node, this.checked);
        },
        check(node) {
            this.checked.push(node.id);
            this.$dispatch('folder-tree-check', node, this.checked);
        },
        isChecked(node) {
            if (typeof this.checkedCallback === 'function') {
                return this.checkedCallback(node);
            }

            if (this.isLeaf(node)) {
                return this.checked.includes(node.id);
            }

            if (!Array.isArray(node.children)) {
                node.children = [];
            }

            return (node.children || []).every((child) =>
                this.isChecked(child),
            );
        },
        isIndeterminate(node) {
            if (this.isLeaf(node)) {
                return false; // Leaf nodes can't be indeterminate
            }

            if (!Array.isArray(node.children)) {
                node.children = [];
            }
            const childStates = (node.children || []).map(
                (child) => this.isChecked(child) || this.isIndeterminate(child),
            );
            const allChecked = childStates.every((state) => state);
            const anyChecked = childStates.some((state) => state);

            return anyChecked && !allChecked;
        },
        toggleSelect(node) {
            this.selected?.id === node.id ? this.unselect() : this.select(node);
            this.$dispatch('folder-tree-select-toggle', node, this.selected);
        },
        select(node) {
            this.selected = node;
            if (this.selectedCallback) {
                this.selectedCallback(node, this.selected);
            }

            this.$dispatch('folder-tree-select', node);
        },
        unselect() {
            this.$dispatch('folder-tree-unselect', this.selected);
            this.selected = null;
        },
        removeNode(node) {
            const nodeId = typeof node === 'object' ? node.id : node;

            const traverseAndRemove = (nodes, parent = null) => {
                for (let i = 0; i < nodes.length; i++) {
                    if (nodes[i].id === nodeId) {
                        nodes.splice(i, 1);
                        return true; // Node found and removed
                    }

                    if (nodes[i][this.childrenAttribute]) {
                        const childNodes = nodes[i][this.childrenAttribute];
                        if (traverseAndRemove(childNodes, nodes[i])) {
                            // Remove the parent node if it has no more children
                            if (childNodes.length === 0) {
                                delete nodes[i][this.childrenAttribute];
                            }
                            return true;
                        }
                    }
                }
                return false; // Node not found
            };

            traverseAndRemove(this.tree);
            this.unselect();
        },
        searchNodes(data, search = null) {
            if (!Array.isArray(data) && typeof data !== 'object') {
                return [];
            }

            if (!search) {
                return Array.isArray(data) ? data : Object.values(data); // Convert object to array if necessary
            }

            const lowerSearch = search.toLowerCase();

            const traverse = (node) => {
                let filteredChildren = [];

                // Recursively check children if available
                if (Array.isArray(node[this.childrenAttribute])) {
                    filteredChildren = node[this.childrenAttribute]
                        .map(traverse)
                        .filter((child) => child !== null); // Remove non-matching children
                }

                // Check if the current node matches the search term (in the `nameAttribute`)
                const matches = this.getSearchAttributes().some((attribute) => {
                    return node[attribute].toLowerCase().includes(lowerSearch);
                });

                // If the node matches, include it along with ALL its children
                if (matches) {
                    return {
                        ...node,
                        [this.childrenAttribute]:
                            node[this.childrenAttribute] || [],
                    };
                }

                // If children match but the node itself does not, include the filtered children
                if (filteredChildren.length > 0) {
                    return {
                        ...node,
                        [this.childrenAttribute]: filteredChildren,
                    };
                }

                return null; // Exclude non-matching nodes
            };

            // Convert data to an array if it's an object, then filter
            return (Array.isArray(data) ? data : Object.values(data))
                .map(traverse)
                .filter((node) => node !== null); // Remove null (non-matching) nodes
        },
        addFolder(node = null, attributes = {}) {
            let id = attributes.id || uuidv4();
            let target = node ? node.children : this.tree;

            if (node) {
                this.openFolder(node);
            } else {
                // add name and children attribute if not in attributes
                if (!attributes.hasOwnProperty(this.nameAttribute)) {
                    attributes[this.nameAttribute] = 'New Folder';
                }
                if (!attributes.hasOwnProperty(this.childrenAttribute)) {
                    attributes[this.childrenAttribute] = [];
                }
            }

            attributes.id = id;
            attributes[this.parentIdAttribute] = node?.id ?? null;
            target.push(attributes);

            const newNode = target[target.length - 1];

            this.select(newNode);
            this.openFolder(target);
            this.$dispatch('folder-tree-folder-added', this.selected, target);
        },
        updateNode(attributes) {
            const traverseAndUpdate = (nodes) => {
                for (let i = 0; i < nodes.length; i++) {
                    if (nodes[i].id === attributes.id) {
                        const nodeCopy = { ...nodes[i] };
                        Object.assign(nodes[i], attributes);

                        if (
                            nodeCopy[this.parentIdAttribute] !==
                            attributes[this.parentIdAttribute]
                        ) {
                            let parentNode = this.getNodeById(
                                attributes[this.parentIdAttribute],
                            );
                            this.removeNode(nodes[i]);

                            if (!parentNode[this.childrenAttribute]) {
                                parentNode[this.childrenAttribute] = [];
                            }

                            parentNode[this.childrenAttribute].push(nodeCopy);
                        }

                        this.$dispatch(
                            'folder-tree-folder-updated',
                            nodes[i],
                            attributes,
                        );
                        return true; // Node found and updated
                    }

                    if (nodes[i][this.childrenAttribute]) {
                        if (
                            traverseAndUpdate(nodes[i][this.childrenAttribute])
                        ) {
                            return true;
                        }
                    }
                }
                return false; // Node not found
            };

            traverseAndUpdate(this.tree);
        },
        getNodePath(node, attribute = 'id') {
            node = node || this.selected;

            if (!node) {
                return null;
            }

            const findPath = (node, path = []) => {
                path.push(node[attribute]);

                for (const topNode of this.tree) {
                    const traverse = (currentNode, currentPath) => {
                        if (currentNode.id === node.id) {
                            return currentPath;
                        }

                        if (currentNode[this.childrenAttribute]) {
                            for (const child of currentNode[
                                this.childrenAttribute
                            ]) {
                                const result = traverse(child, [
                                    ...currentPath,
                                    currentNode[attribute],
                                ]);
                                if (result) {
                                    return result;
                                }
                            }
                        }

                        return null;
                    };

                    const pathResult = traverse(topNode, []);
                    if (pathResult) {
                        return pathResult.concat(node[attribute]);
                    }
                }

                return null;
            };

            return findPath(node);
        },
    };
}
