import {livewire_hot_reload} from 'virtual:livewire-hot-reload'

import _ from 'lodash';
import axios from 'axios';

window._ = _;

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

livewire_hot_reload();

customElements.define(
    "embedded-webview",
    class extends HTMLElement {
        constructor() {
            super();
            this.attachShadow({ mode: "open" });
             axios.get(this.getAttribute("src")).then((response) => {
                 this.shadowRoot.innerHTML = response.data;
            });
        }
    }
);
