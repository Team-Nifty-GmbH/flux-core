import { lightbox } from '../../lightbox.js';

const IMAGE_EXTS = new Set([
    'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'avif', 'bmp',
]);

lightbox.register({
    matches: ({ ext, mime }) =>
        (mime && mime.startsWith('image/')) || IMAGE_EXTS.has(ext),
    render: ({ url, title, container }) => {
        const img = document.createElement('img');
        img.src = url;
        if (title) {
            img.alt = title;
        }
        img.className = 'max-w-full max-h-full object-contain';
        container.appendChild(img);
    },
});
