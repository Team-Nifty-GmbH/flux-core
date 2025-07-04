import folders from './components/folders';
import setupEditor from './components/tiptap';
import editorFontSizeColorHandler from './components/tiptap-font-size-color-handler.js';
import workTime from './components/work-time.js';
import calendar from './components/calendar.js';
import dashboard from './components/dashboard';
import signature from './components/signature-pad.js';
import addressMap from './components/address-map';
import filePond from './components/file-pond';
import templateOutlet from './components/template-outlet';
import sort from '@alpinejs/sort';
import navigationSpinner from './components/navigation-spinner.js';
import wireNavigation from './components/wire-navigation.js';
import comments from './components/comments.js';
import selectComponent from './components/tallstackui/select.js';
import toastComponent from './components/tallstackui/toast.js';

import { Calendar } from '@fullcalendar/core';
import allLocales from '@fullcalendar/core/locales-all';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import interactionPlugin from '@fullcalendar/interaction';
import dayjs from 'dayjs';
import utc from 'dayjs/plugin/utc';

dayjs.extend(utc);

window.dayjs = dayjs;
window.calendar = calendar;
window.setupEditor = setupEditor;
window.editorFontSizeColorHandler = editorFontSizeColorHandler;
window.workTime = workTime;
window.dashboard = dashboard;
window.addressMap = addressMap;
window.signature = signature;
window.filePond = filePond;
window.$tallstackuiSelect = selectComponent;
window.$tallstackuiToast = toastComponent;

window.Calendar = Calendar;
window.dayGridPlugin = dayGridPlugin;
window.timeGridPlugin = timeGridPlugin;
window.listPlugin = listPlugin;
window.interactionPlugin = interactionPlugin;
window.allLocales = allLocales;

navigationSpinner();

window.addEventListener('alpine:init', () => {
    window.Alpine.plugin(sort);
});

Alpine.directive('currency', (el, { expression }, { evaluate }) => {
    const data = evaluate(expression);

    el.innerText = formatters.money(data.value, data.currency);
});

Alpine.directive('percentage', (el, { expression }, { evaluate }) => {
    el.innerText = formatters.percentage(evaluate(expression));
});

Alpine.directive('template-outlet', templateOutlet);
Alpine.data('folder_tree', folders);
Alpine.data('comments', comments);

document.addEventListener('livewire:navigated', wireNavigation, { once: true });

document.addEventListener('livewire:init', () => {
    wireNavigation();

    Livewire.hook('request', ({ fail }) => {
        fail(({ status, preventDefault }) => {
            if (status === 419) {
                window.location.reload();

                preventDefault();
            }
        });
    });
});

Livewire.directive('flux-confirm', ({ el, directive, component }) => {
    let type = directive.modifiers.includes('type')
        ? directive.modifiers[directive.modifiers.indexOf('type') + 1]
        : 'info';

    if (!['success', 'error', 'warning', 'info'].includes(type)) {
        type = 'info';
    }

    let promptAppend = directive.modifiers.includes('prompt')
        ? '<div>\n' +
          '    <div class="relative mt-1 rounded-md shadow-sm">\n' +
          '    <div class="focus:ring-primary-600 focus-within:focus:ring-primary-600 focus-within:ring-primary-600 dark:focus-within:ring-primary-600 flex rounded-md ring-1 transition focus-within:ring-2 dark:ring-dark-600 dark:text-dark-300 text-gray-600 ring-gray-300 dark:bg-dark-800 bg-white">\n' +
          '        <input id="prompt-value" class="dark:placeholder-dark-400 w-full rounded-md border-0 bg-transparent py-1.5 ring-0 placeholder:text-gray-400 focus:outline-hidden focus:ring-transparent sm:text-sm sm:leading-6">\n' +
          '    </div>\n' +
          '    </div>\n' +
          '</div>'
        : directive.modifiers.includes('id')
          ? directive.modifiers[directive.modifiers.indexOf('id') + 1]
          : null;

    // Convert sanitized linebreaks ("\n") to real line breaks...
    let message = directive.expression.replaceAll('\\n', '\n').split('|');
    let title = message.shift();
    let description =
        '<div>' +
        message[0] +
        '</div>' +
        (promptAppend ? '<div>' + promptAppend + '</div>' : '');
    let cancelLabel = message[1] ?? 'Cancel';
    let confirmLabel = message[2] ?? 'Confirm';

    if (title === '') title = 'Are you sure?';

    el.__livewire_confirm = (action) => {
        $interaction()
            .wireable(component.id)
            [type](title, description)
            .confirm(confirmLabel, () => {
                action();
            })
            .cancel(cancelLabel)
            .send();
    };
});

window.$promptValue = (id) => {
    const el = document.getElementById(id ? id : 'prompt-value');

    if (el.type === 'checkbox') {
        return el.checked;
    }

    return el.value;
};
