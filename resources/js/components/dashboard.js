import {GridStack} from "gridstack";
import { v4 as uuidv4 } from 'uuid';

export default function ($wire){
    return {
        editGrid: true,
        emptyLayout:false,
        grid:null,
        availableWidgets:null,
        init(){
            this.grid = GridStack.init({
                margin:1,
                columnHeight: 200,
                alwaysShowResizeHandle:true,
                columnOpts :{
                    breakpointForWindow: true,
                    breakpoints: [{w:1100,c:1},{w:2000000,c:6}]
                }
            });
        },
        async syncGrid(){
            const snapshot = $wire.widgets;
            const onScreen = this.grid.getGridItems();
            const newSnapshot = [];
            onScreen.forEach((item) => {
                const widget = snapshot.find((w) => w.id.toString() === item.gridstackNode.id.toString());
                if(widget !== undefined){
                    widget.height = item.gridstackNode.h;
                    widget.width = item.gridstackNode.w;
                    widget.order_column = item.gridstackNode.x;
                    widget.order_row = item.gridstackNode.y;
                    if(item.gridstackNode.class !== undefined){
                        widget.class = item.gridstackNode.class;
                    }
                    if(item.gridstackNode.component_name !== undefined){
                        widget.component_name = item.gridstackNode.component_name;
                    }
                    newSnapshot.push(widget);
                } else {
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
            await $wire.syncWidgets(newSnapshot);

        },
        async addPlaceHolder(){
           if(this.availableWidgets === null){
                this.availableWidgets = await $wire.availableWidgets;
           }
           const id = uuidv4();
           const placeholder =  this.grid.addWidget({
               id,
               h:1,
               w:2,
           });

           placeholder.gridstackNode.order_column = placeholder.gridstackNode.x;
           placeholder.gridstackNode.order_row = placeholder.gridstackNode.y;
           placeholder.gridstackNode.component_name = 'widgets.widget-list';

           await this.syncGrid();

           await $wire.$refresh();

           this.reInit();

        },
        async selectWidget(key,id){
            const el = this.grid.getGridItems()
                .find((item) => item.gridstackNode.id.toString() === id.toString());
            if(el !== undefined){
               const selectedWidget = this.availableWidgets[key];

               el.gridstackNode.class = selectedWidget.class;
               el.gridstackNode.component_name =selectedWidget.component_name;


                // sync position of each grid element with the server
                await this.syncGrid();

                // reload page
                await $wire.$refresh();

                // re-init grid-stack
                this.reInit()
            }
        },
        reInit(){
            this.grid = GridStack.init({
                margin: 1,
                columnHeight: 200,
                alwaysShowResizeHandle:true,
                columnOpts: {
                    breakpointForWindow: true,
                    breakpoints: [{w: 1100, c: 1}, {w: 2000000, c: 6}]
                }
            });
        },
        removeWidget(id){
            console.log(id);
        },
    }
}

