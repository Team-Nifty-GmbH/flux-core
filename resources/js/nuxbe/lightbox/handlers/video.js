import { lightbox } from '../../lightbox.js';

const VIDEO_EXTS = new Set(['mp4', 'webm', 'mov', 'm4v']);

lightbox.register({
    matches: ({ ext, mime }) =>
        (mime && mime.startsWith('video/')) || VIDEO_EXTS.has(ext),
    render: ({ url, container }) => {
        const video = document.createElement('video');
        video.src = url;
        video.controls = true;
        video.autoplay = true;
        video.className = 'max-w-full max-h-full';
        container.appendChild(video);

        return () => {
            video.pause();
            video.src = '';
            video.load();
        };
    },
});
