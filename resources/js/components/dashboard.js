import { GridStack } from 'gridstack';
import { v4 as uuidv4 } from 'uuid';

export default function () {
    return {
        editGrid: false,
        emptyLayout: false,
        grid: null,
        isLoading: false,
        destroy() {
            // destroy grid - on page leave - since livewire caches the component
            if (this.grid !== null) {
                this.grid.destroy(false);
                this.grid = null;
            }
        },
        editGridMode(mode) {
            if (this.grid === null) {
                return;
            }
            if (mode) {
                this.reInit();
            }
            this.editGrid = mode;
        },
        async syncGridOnNewItem() {
            const snapshot = Array.from(await this.$wire.widgets);
            const onScreen = this.grid.getGridItems();
            const newSnapshot = [];
            // update x,y coordinates and type of widget if selected
            onScreen.forEach((item) => {
                const widget = snapshot.find(
                    (w) => w.id.toString() === item.gridstackNode.id.toString(),
                );
                if (widget !== undefined) {
                    widget.height = item.gridstackNode.h;
                    widget.width = item.gridstackNode.w;
                    widget.order_column = item.gridstackNode.x;
                    widget.order_row = item.gridstackNode.y;
                    // in case something is selected from the list
                    if (item.gridstackNode.class !== undefined) {
                        widget.class = item.gridstackNode.class;
                    }
                    // in case something is selected from the list
                    if (item.gridstackNode.component_name !== undefined) {
                        widget.component_name =
                            item.gridstackNode.component_name;
                    }
                    newSnapshot.push(widget);
                } else {
                    // new widget on the screen
                    newSnapshot.push({
                        id: item.gridstackNode.id,
                        height: item.gridstackNode.h,
                        width: item.gridstackNode.w,
                        order_column: item.gridstackNode.x,
                        order_row: item.gridstackNode.y,
                        component_name: item.gridstackNode.component_name,
                    });
                }
            });
            // sync property
            await this.$wire.syncWidgets(newSnapshot);
        },
        async syncGridOnDelete() {
            const snapshot = Array.from(this.$wire.widgets);
            const onScreen = this.grid.getGridItems();
            const newSnapshot = [];
            onScreen.forEach((item) => {
                const widget = snapshot.find(
                    (w) => w.id.toString() === item.gridstackNode.id.toString(),
                );
                // remove from snapshot if not on the screen and recalculate x,y coordinates
                if (item.style.display !== 'none' && widget !== undefined) {
                    widget.height = item.gridstackNode.h;
                    widget.width = item.gridstackNode.w;
                    widget.order_column = item.gridstackNode.x;
                    widget.order_row = item.gridstackNode.y;
                    newSnapshot.push(widget);
                }
            });
            // sync property
            await this.$wire.syncWidgets(newSnapshot);
        },
        async save() {
            this.isLoading = true;
            const snapshot = Array.from(await this.$wire.widgets);
            const onScreen = this.grid.getGridItems();
            const newSnapshot = [];
            // update x,y coordinates on save
            onScreen.forEach((item) => {
                const widget = snapshot.find(
                    (w) => w.id.toString() === item.gridstackNode.id.toString(),
                );
                if (widget !== undefined) {
                    widget.height = item.gridstackNode.h;
                    widget.width = item.gridstackNode.w;
                    widget.order_column = item.gridstackNode.x;
                    widget.order_row = item.gridstackNode.y;
                    newSnapshot.push(widget);
                }
            });
            // sync and save to db
            await this.$wire.saveWidgets(newSnapshot);
            // stop edit mode
            this.editGridMode(false);
            // refresh id
            await this.$wire.$refresh();
            // stop grid
            this.reInit().disable();
        },
        async selectWidget(key) {
            this.isLoading = true;
            if (this.$wire.availableWidgets === null) {
                this.$wire.availableWidgets = await this.$wire.availableWidgets;
            }
            const id = uuidv4();
            const selectedWidget = this.$wire.availableWidgets[key];
            const placeholder = this.grid.addWidget({
                id,
                h: selectedWidget.defaultHeight,
                w: selectedWidget.defaultWidth,
            });
            placeholder.gridstackNode.order_column =
                placeholder.gridstackNode.x;
            placeholder.gridstackNode.order_row = placeholder.gridstackNode.y;
            placeholder.gridstackNode.component_name = key;

            // sync position of each grid element with the server
            await this.syncGridOnNewItem();
            // reload component
            await this.$wire.resetWidgets();

            // re-init grid-stack
            this.reInit();
        },
        reInit() {
            // check if grid is loading
            if (this.isLoading) {
                this.isLoading = false;
            }
            // clear previous grid state
            if (this.grid !== null) {
                this.grid.destroy(false);
            }

            // init grid
            this.grid = GridStack.init({
                margin: 10,
                cellHeight: 250,
                alwaysShowResizeHandle: true,
                columnOpts: {
                    breakpointForWindow: true,
                    breakpoints: [
                        { w: 1100, c: 1 },
                        { w: 2000000, c: 6 },
                    ],
                },
            });
            return this.grid;
        },
        async removeWidget(id) {
            this.isLoading = true;
            const el = this.grid
                .getGridItems()
                .find(
                    (item) =>
                        item.gridstackNode.id.toString() === id.toString(),
                );
            if (el !== undefined) {
                // remove from grid - keep in snapshot
                el.style.display = 'none';
                await this.grid.compact();

                await this.syncGridOnDelete();
                //  reload component
                await this.$wire.$refresh();
                // init grid
                this.reInit();
            }
            if (this.isLoading) {
                this.isLoading = false;
            }
        },
    };
}
