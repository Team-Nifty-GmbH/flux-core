import { lightbox } from '../../lightbox.js';

lightbox.setFallback({
    matches: () => true,
    render: ({ url, title, container }) => {
        const wrapper = document.createElement('div');
        wrapper.className =
            'flex flex-col items-center gap-4 rounded-lg bg-white p-8 text-center text-gray-900 dark:bg-gray-900 dark:text-gray-100';

        const message = document.createElement('p');
        message.textContent =
            window.translations?.['File cannot be previewed'] ??
            'File cannot be previewed';

        const link = document.createElement('a');
        link.href = url;
        link.target = '_blank';
        link.rel = 'noopener';
        link.textContent =
            (title ? title + ' — ' : '') +
            (window.translations?.['Download'] ?? 'Download');
        link.className = 'text-primary-600 underline';

        wrapper.appendChild(message);
        wrapper.appendChild(link);
        container.appendChild(wrapper);
    },
});
