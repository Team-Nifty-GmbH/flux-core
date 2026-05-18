<div
    x-data="{
        open: false,
        cleanup: null,
        downloadUrl: null,
        downloadName: null,
        previousBodyOverflow: '',
        openFromEvent(event) {
            const { url, mime, title } = event.detail;
            this.runCleanup();
            const handler = window.$nuxbe?.lightbox?.resolve(url, mime);
            if (!handler) {
                console.warn('[nuxbe lightbox] no handler resolved for', {
                    url,
                    mime,
                });
                return;
            }
            this.$refs.content.replaceChildren();
            try {
                const result = handler.render({
                    url,
                    mime,
                    title,
                    container: this.$refs.content,
                    close: () => this.close(),
                });
                this.cleanup = typeof result === 'function' ? result : null;
            } catch (error) {
                console.warn('[nuxbe lightbox] handler error', error);
                this.cleanup = null;
                this.$refs.content.replaceChildren();
                return;
            }
            this.downloadUrl = url;
            this.downloadName = title || '';
            this.open = true;
            this.previousBodyOverflow = document.body.style.overflow;
            document.body.style.overflow = 'hidden';
        },
        close() {
            this.open = false;
            this.runCleanup();
            this.$refs.content.replaceChildren();
            this.downloadUrl = null;
            this.downloadName = null;
            document.body.style.overflow = this.previousBodyOverflow;
            this.previousBodyOverflow = '';
        },
        runCleanup() {
            if (typeof this.cleanup === 'function') {
                try {
                    this.cleanup();
                } catch (error) {
                    console.warn('[nuxbe lightbox] cleanup error', error);
                }
            }
            this.cleanup = null;
        },
    }"
    x-on:nuxbe:lightbox:open.window="openFromEvent($event)"
    x-on:keydown.escape.window="if (open) close();"
    x-show="open"
    x-transition.opacity.duration.200ms
    x-cloak
    x-on:click.self="close"
    class="fixed inset-0 z-[60] flex items-center justify-center bg-black/80 p-4"
>
    <div
        x-show="downloadName"
        x-on:click.stop
        class="absolute top-4 right-16 left-4 max-w-[calc(100%-5rem)] truncate rounded-full bg-black/60 px-4 py-2 text-sm text-white"
        x-text="downloadName"
    ></div>
    <button
        type="button"
        x-on:click="close"
        class="absolute top-4 right-4 flex h-10 w-10 items-center justify-center rounded-full bg-black/60 text-white hover:bg-black/80"
    >
        <span class="text-2xl leading-none">&times;</span>
    </button>
    <div
        x-ref="content"
        x-on:click.stop
        class="flex max-h-full max-w-full items-center justify-center"
    ></div>
    <a
        x-show="downloadUrl"
        x-bind:href="downloadUrl"
        x-bind:download="downloadName"
        x-on:click.stop
        target="_blank"
        rel="noopener"
        class="absolute right-4 bottom-4 inline-flex items-center gap-2 rounded-full bg-black/60 px-4 py-2 text-sm text-white hover:bg-black/80"
    >
        <span>&darr;</span>
        <span>{{ __('Download') }}</span>
    </a>
</div>
