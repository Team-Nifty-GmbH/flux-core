<div
    x-data="{
        open: false,
        cleanup: null,
        openFromEvent(event) {
            const { url, mime, title } = event.detail;
            this.runCleanup();
            const handler = window.$nuxbe?.lightbox?.resolve(url, mime);
            if (! handler) {
                console.warn('[nuxbe lightbox] no handler resolved for', { url, mime });
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
            this.open = true;
            document.body.style.overflow = 'hidden';
        },
        close() {
            this.open = false;
            this.runCleanup();
            this.$refs.content.replaceChildren();
            document.body.style.overflow = '';
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
    x-on:keydown.escape.window="if (open) close()"
    x-show="open"
    x-cloak
    x-on:click.self="close"
    class="fixed inset-0 z-[60] flex items-center justify-center bg-black/80 p-4"
    role="dialog"
    aria-modal="true"
>
    <button
        type="button"
        x-on:click="close"
        class="absolute top-4 right-4 flex h-10 w-10 items-center justify-center rounded-full bg-black/60 text-white hover:bg-black/80"
        aria-label="{{ __('Close') }}"
    >
        <span class="text-2xl leading-none">&times;</span>
    </button>
    <div
        x-ref="content"
        x-on:click.stop
        class="flex max-h-full max-w-full items-center justify-center"
    ></div>
</div>
