export default function pillbox(property, lazy = 0) {
    return {
        search: '',
        items: [],
        open: false,
        loading: false,
        highlighted: -1,
        abortController: null,

        get values() {
            return (
                property
                    .split('.')
                    .reduce((carry, key) => carry?.[key], this.$wire) ?? []
            );
        },

        add(value) {
            value = (value ?? '').trim();

            if (!value) {
                return;
            }

            const email = value.match(/<([^>]*)>/);
            if (email && email[1]) {
                value = email[1];
            }

            if (!this.values.includes(value)) {
                this.values.push(value);
            }

            this.search = '';
            this.close();
        },

        remove(value) {
            this.values.splice(this.values.indexOf(value), 1);
        },

        close() {
            this.open = false;
            this.highlighted = -1;
            this.abortController?.abort();
            this.loading = false;
        },

        onKeydown($event) {
            if ($event.key === 'Escape') {
                this.close();

                return;
            }

            if ($event.key === 'ArrowDown') {
                $event.preventDefault();
                this.highlighted = Math.min(
                    this.highlighted + 1,
                    this.items.length - 1,
                );

                return;
            }

            if ($event.key === 'ArrowUp') {
                $event.preventDefault();
                this.highlighted = Math.max(this.highlighted - 1, -1);

                return;
            }

            if ($event.key === 'Enter' || $event.key === ',') {
                $event.preventDefault();

                if (this.open && this.highlighted >= 0) {
                    this.add(this.items[this.highlighted]?.value);
                } else {
                    // read the raw input value - the debounced model may
                    // lag behind on a fast enter
                    this.add($event.target.value);
                }

                $event.target.value = '';
            }
        },

        onInput() {
            if (!this.$root.dataset.request) {
                return;
            }

            if (this.search.length < lazy) {
                this.close();

                return;
            }

            this.fetchItems();
        },

        fetchItems() {
            this.abortController?.abort();
            this.abortController = new AbortController();
            this.loading = true;
            this.open = true;

            const request = JSON.parse(this.$root.dataset.request);
            const url = new URL(request.url, window.location.origin);
            const init = {
                signal: this.abortController.signal,
                method: (request.method ?? 'get').toUpperCase(),
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document
                        .querySelector('meta[name=csrf-token]')
                        ?.getAttribute('content'),
                },
            };

            if (init.method === 'POST') {
                init.headers['Content-Type'] = 'application/json';
                init.body = JSON.stringify({
                    search: this.search,
                    ...(request.params ?? {}),
                });
            } else {
                url.searchParams.set('search', this.search);
            }

            fetch(url, init)
                .then((response) =>
                    response.ok ? response.json() : Promise.reject(response),
                )
                .then((data) => {
                    this.items = (
                        Array.isArray(data) ? data : (data?.data ?? [])
                    ).filter((item) => item?.value);
                    this.highlighted = this.items.length > 0 ? 0 : -1;
                    this.loading = false;
                })
                .catch((reason) => {
                    if (reason?.name === 'AbortError') {
                        return;
                    }

                    this.items = [];
                    this.loading = false;
                });
        },
    };
}
