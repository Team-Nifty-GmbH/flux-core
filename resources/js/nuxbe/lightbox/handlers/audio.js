import { lightbox } from '../../lightbox.js';

const AUDIO_EXTS = new Set(['mp3', 'wav', 'ogg', 'm4a', 'flac']);

lightbox.register({
    matches: ({ ext, mime }) =>
        (mime && mime.startsWith('audio/')) || AUDIO_EXTS.has(ext),
    render: ({ url, container }) => {
        const audio = document.createElement('audio');
        audio.src = url;
        audio.controls = true;
        audio.autoplay = true;
        container.appendChild(audio);
        return () => {
            audio.pause();
            audio.src = '';
        };
    },
});
