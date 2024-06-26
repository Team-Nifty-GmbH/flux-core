import { GridStack } from 'gridstack';
import { v4 as uuidv4 } from 'uuid';

export default function($wire) {
    return {
        editGrid: false,
        emptyLayout: false,
        grid: null,
        availableWidgets: null,
        init() {
            this.reInit().disable();
        },
        editGridMode(mode) {
            if (this.grid === null) {
                return;
            }
            if (mode) {
                this.grid.enable();
            }
            this.editGrid = mode;
        },
        // cannot save if widgets-list is presented in the grid
        get openGridItems() {
            return $wire.widgets.filter((w) => w.component_name === 'widgets.widget-list').length === 0;
        },
        async cancelDashboard() {
            this.editGridMode(false);
            // load data from db - to $wire.widgets
            await $wire.cancelDashboard();
            // refresh previous state
            await $wire.$refresh();
            // stop grid
            this.reInit().disable();
        },
        async syncGridOnNewItem() {
            const snapshot = $wire.widgets;
            const onScreen = this.grid.getGridItems();
            const newSnapshot = [];
            // update x,y coordinates and type of widget if selected
            onScreen.forEach((item) => {
                const widget = snapshot.find(
                    (w) => w.id.toString() === item.gridstackNode.id.toString()
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
                        component_name: item.gridstackNode.component_name
                    });
                }
            });
            // sync property
            await $wire.syncWidgets(newSnapshot);
        },
        async syncGridOnDelete() {
            const snapshot = $wire.widgets;
            const onScreen = this.grid.getGridItems();
            const newSnapshot = [];
            onScreen.forEach((item) => {
                const widget = snapshot.find(
                    (w) => w.id.toString() === item.gridstackNode.id.toString()
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
            await $wire.syncWidgets(newSnapshot);
        },
        async save() {
            const snapshot = $wire.widgets;
            const onScreen = this.grid.getGridItems();
            const newSnapshot = [];
            // update x,y coordinates on save
            onScreen.forEach((item) => {
                const widget = snapshot.find(
                    (w) => w.id.toString() === item.gridstackNode.id.toString()
                );
                if (widget !== undefined) {
                    widget.height = item.gridstackNode.h;
                    widget.width = item.gridstackNode.w;
                    widget.order_column = item.gridstackNode.x;
                    widget.order_row = item.gridstackNode.y;
                    newSnapshot.push(widget);
                }
            });
            // sync properties
            await  $wire.syncWidgets(newSnapshot);
            // save to db
            await $wire.saveDashboard();

            this.editGridMode(false);
            this.grid.disable();
        },
        async addPlaceHolder() {
            if (this.availableWidgets === null) {
                this.availableWidgets = await $wire.availableWidgets;
            }
            const id = uuidv4();
            const placeholder = this.grid.addWidget({
                id,
                h: 1,
                w: 2
            });

            placeholder.gridstackNode.order_column =
                placeholder.gridstackNode.x;
            placeholder.gridstackNode.order_row = placeholder.gridstackNode.y;
            placeholder.gridstackNode.component_name = 'widgets.widget-list';

            // sync position of each grid element with the server
            await this.syncGridOnNewItem();

            // reload component
            await $wire.$refresh();

            // re-init grid-stack
            this.reInit();
        },
        async selectWidget(key, id) {
            const el = this.grid
                .getGridItems()
                .find(
                    (item) =>
                        item.gridstackNode.id.toString() === id.toString()
                );
            if (el !== undefined) {
                const selectedWidget = this.availableWidgets[key];

                el.gridstackNode.class = selectedWidget.class;
                el.gridstackNode.component_name = selectedWidget.component_name;

                // sync position of each grid element with the server
                await this.syncGridOnNewItem();

                // reload component
                await $wire.$refresh();

                // re-init grid-stack
                this.reInit();
            }
        },
        reInit() {
            // init grid
            this.grid = GridStack.init({
                margin: 4,
                columnHeight: 200,
                alwaysShowResizeHandle: true,
                columnOpts: {
                    breakpointForWindow: true,
                    breakpoints: [
                        { w: 1100, c: 1 },
                        { w: 2000000, c: 6 }
                    ]
                }
            });
            return this.grid;
        },
        async removeWidget(id) {
            const el = this.grid
                .getGridItems()
                .find(
                    (item) =>
                        item.gridstackNode.id.toString() === id.toString()
                );
            if (el !== undefined) {
                // remove from grid - keep in snapshot
                el.style.display = 'none';
                await this.grid.compact();

                await this.syncGridOnDelete();
                //  reload component
                await $wire.$refresh();
                // init grid
                this.reInit();
            }
        }
    };
}
