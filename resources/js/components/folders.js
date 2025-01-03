import {v4 as uuidv4} from 'uuid';

export default function folders(
    getTreePromise,
    property = null,
    checked = [],
    multiSelect = false,
    nameAttribute = 'label',
    childrenAttribute = 'children',
    selectedCallback = null,
    checkedCallback = null
) {
    return {
        checked: checked,
        selected: null,
        openFolders: [],
        tree: [],
        property: property,
        getTreePromise: getTreePromise,
        multiSelect: multiSelect,
        nameAttribute: nameAttribute,
        childrenAttribute: childrenAttribute,
        selectedCallback: selectedCallback,
        checkedCallback: checkedCallback,
        async init() {
            await this.refresh();

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
            this.openFolders = this.openFolders.filter(id => id !== node.id);
        },
        openAllSubfolders(node) {
            const traverse = (currentNode) => {
                if (!this.openFolders.includes(currentNode.id)) {
                    this.openFolder(currentNode);
                }
                currentNode.children?.forEach(child => traverse(child));
            };
            traverse(node);
        },
        closeAllSubfolders(node) {
            const traverse = (currentNode) => {
                this.closeFolder(currentNode);
                currentNode.children?.forEach(child => traverse(child));
            };
            traverse(node);
        },
        isOpen(node) {
            return this.openFolders.includes(node.id);
        },
        isLeaf(node) {
            return !node.children || node.children.length === 0;
        },
        toggleCheck(node, isChecked) {
            const traverse = (currentNode, check) => {
                if (check) {
                    if (!this.checked.includes(currentNode.id)) {
                        this.checked.push(currentNode.id);
                    }
                } else {
                    this.checked = this.checked.filter(id => id !== currentNode.id);
                }
                currentNode.children?.forEach(child => traverse(child, check));
            };
            traverse(node, isChecked);

            this.updateParentStates(node);
        },
        isChecked(node) {
            if (this.isLeaf(node)) {
                return this.checked.includes(node.id);
            }

            return (node.children || []).every(child => this.isChecked(child));
        },
        isIndeterminate(node) {
            if (this.isLeaf(node)) {
                return false; // Leaf nodes can't be indeterminate
            }

            const childStates = (node.children || []).map(child => this.isChecked(child) || this.isIndeterminate(child));
            const allChecked = childStates.every(state => state);
            const anyChecked = childStates.some(state => state);
            return anyChecked && !allChecked;
        },
        updateParentStates(node) {
            if (!node.parent) {
                return;
            }

            const parent = node.parent;
            const childIds = parent.children.map(child => child.id);
            const selectedChildren = childIds.filter(id => this.checked.includes(id));

            if (selectedChildren.length === 0) {
                this.checked = this.checked.filter(id => id !== parent.id);
            } else if (selectedChildren.length === childIds.length) {
                if (!this.checked.includes(parent.id)) {
                    this.checked.push(parent.id);
                }
            }

            this.updateParentStates(parent);
        },
        toggleSelect(node) {
            this.selected?.id === node.id ? this.unselect() : this.select(node);
        },
        select(node) {
            this.$dispatch('folder-tree-select', node, this.selected);

            this.selected = node;
            if (this.selectedCallback) {
                this.selectedCallback(node, this.selected);
            }
        },
        unselect() {
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
        addFolder(node = null, attributes = {}) {
            let id = uuidv4();
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
            attributes.parent_id = node?.id ?? null;
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
                        Object.assign(nodes[i], attributes);

                        this.$dispatch('folder-tree-folder-updated', nodes[i], attributes);
                        return true; // Node found and updated
                    }

                    if (nodes[i][this.childrenAttribute]) {
                        if (traverseAndUpdate(nodes[i][this.childrenAttribute])) {
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

            if (! node) {
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
                            for (const child of currentNode[this.childrenAttribute]) {
                                const result = traverse(child, [...currentPath, currentNode[attribute]]);
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
        }
    };
}
