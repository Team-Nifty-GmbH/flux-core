export default function () {
    return {
        init() {
            if (this.isSelectable() && ! this.model) {
                this.model = this.$el.attributes.getNamedItem('x-model')?.value;
            }

            if (! this.model) {
                this.selectable = false;
            }
        },
        select(event, level) {
            this.$dispatch('folder-tree-select', level);
        },
        levels: [],
        model: null,
        nameAttribute: 'name',
        selectable: true,
        isSelectable() {
            return this.selectable;
        },
        multiSelect: true,
        openFolders: [],
        folderIcon() {
            return `<svg x-html="openFolders.includes(level.id) ? folderOpenIcon() : folderClosedIcon()" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 256 256"></svg>`;
        },
        folderOpenIcon() {
            return `<path d="M245,110.64A16,16,0,0,0,232,104H216V88a16,16,0,0,0-16-16H130.67L102.94,51.2a16.14,16.14,0,0,0-9.6-3.2H40A16,16,0,0,0,24,64V208h0a8,8,0,0,0,8,8H211.1a8,8,0,0,0,7.59-5.47l28.49-85.47A16.05,16.05,0,0,0,245,110.64ZM93.34,64l27.73,20.8a16.12,16.12,0,0,0,9.6,3.2H200v16H69.77a16,16,0,0,0-15.18,10.94L40,158.7V64Zm112,136H43.1l26.67-80H232Z"></path>`;
        },
        folderClosedIcon() {
            return `<path d="M216,72H131.31L104,44.69A15.86,15.86,0,0,0,92.69,40H40A16,16,0,0,0,24,56V200.62A15.4,15.4,0,0,0,39.38,216H216.89A15.13,15.13,0,0,0,232,200.89V88A16,16,0,0,0,216,72ZM40,56H92.69l16,16H40ZM216,200H40V88H216Z"></path>`;
        },
        fileIcon() {
            return `<div class="w-5 h-5"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 256 256"><path d="M213.66,82.34l-56-56A8,8,0,0,0,152,24H56A16,16,0,0,0,40,40V216a16,16,0,0,0,16,16H200a16,16,0,0,0,16-16V88A8,8,0,0,0,213.66,82.34ZM160,51.31,188.69,80H160ZM200,216H56V40h88V88a8,8,0,0,0,8,8h48V216Z"></path></svg></div>`;
        },
        isFolder(obj) {
            return obj.hasOwnProperty('children') && obj.children.length > 0;
        },
        renderLevel(obj) {
            let html = '<div class="flex gap-1">';

            if (this.isFolder(obj)) {
                html +=  this.chevron();
            }

            if (this.isSelectable(obj)) {
                if (this.multiSelect) {
                    html += `<div class="flex justify-center h-5">
                        <input ${this.selectAttributes(obj)} type="checkbox" class="form-checkbox rounded transition ease-in-out duration-100
                        border-secondary-300 text-primary-600 focus:ring-primary-600 focus:border-primary-400
                        dark:border-secondary-500 dark:checked:border-secondary-600 dark:focus:ring-secondary-600
                        dark:focus:border-secondary-500 dark:bg-secondary-600 dark:text-secondary-600
                        dark:focus:ring-offset-secondary-800" x-model="${this.model}" x-bind:value="level.id">
                    </div>`;
                } else {
                    html += `<input ${this.selectAttributes(obj)} type="radio" class="form-radio rounded-full transition ease-in-out duration-100
                    border-secondary-300 text-primary-600 focus:ring-primary-600 focus:border-primary-400
                    dark:border-secondary-500 dark:checked:border-secondary-600 dark:focus:ring-secondary-600
                    dark:focus:border-secondary-500 dark:bg-secondary-600 dark:text-secondary-600
                    dark:focus:ring-offset-secondary-800" x-model="${this.model}" x-bind:value="level.id">`;
                }
            }

            let ref = 'l'+Math.random().toString(36).substring(7);

            html += this.itemTemplate(obj, ref);
            html += '</div>';

            if(obj.children) {
                html += this.listTemplate(ref);
            }

            return html;
        },
        itemTemplate(obj) {
            return `<div class="flex items-start gap-1 block rounded px-1 cursor-pointer"
                    ${this.itemAttributes()}
                    x-on:click="select($event, level)"
                    >
                        ${obj.hasOwnProperty('icon') ? '<div class="icon">' + obj.icon + '</div>' : this.isFolder(obj) ? '<div class="icon">' + this.folderIcon() + '</div>' : this.fileIcon()}
                        <span>${obj[this.nameAttribute]}</span>
                </div>`;
        },
        itemAttributes() {
            return '';
        },
        selectAttributes() {
            return '';
        },
        chevron(){
            return `<svg x-bind:class="{'rotate-90' : openFolders.includes(level.id)}" x-on:click="toggleLevel('', level)" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5 icon-cursor transition-transform">
                    <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                </svg>`;
        },
        listTemplate() {
            return `<ul x-bind:style="openFolders.includes(level.id) ? '' : 'display:none'" x-bind:class="openFolders.includes(level.id) ? 'opacity-100' : 'display:none'" class="pl-7 pb-1 transition-all flex gap-1">
                        <div class="w-6 flex items-center justify-center">
                            <div class="w-px h-full bg-gray-400"></div>
                        </div>
                        <div>
                            <template x-for='(level,i) in level.children' :key="level.id">
                                <li x-html="renderLevel(level)"></li>
                            </template>
                        </div>
                    </ul>`;
        },
        showLevel(el, obj) {
            this.openFolders.push(obj.id);
        },
        hideLevel(el, obj) {
            this.openFolders = this.openFolders.filter((id) => id !== obj.id);
        },
        toggleLevel(el, obj) {
            this.$dispatch('item-clicked', obj);
            if (! this.isFolder(obj)) {
                return;
            }

            if (this.openFolders.includes(obj.id)) {
                this.hideLevel(el, obj);
            } else {
                this.showLevel(el, obj);
            }
        },
    }
}
