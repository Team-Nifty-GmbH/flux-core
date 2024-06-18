import {GridStack} from "gridstack";

export default function (){
    return {
        grid:null,
        init(){
            this.grid = GridStack.init({
                margin:1,
                columnOpts :{
                    breakpointForWindow: true,
                    breakpoints: [{w:1100,c:1},{w:2000000,c:6}]
                }
            });
        }
    }
}

