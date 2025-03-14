class TallstackUISelect {
    constructor(id) {
        this.id = id;
        const el = this.getSelect();
        this.alpineComponent = el ? Alpine.$data(el) : null;
    }

    mergeRequestParams(newParams) {
        const currentParams = this.getRequestParams() || {};

        const mergeDeep = (target, source) => {
            for (const key in source) {
                if (source.hasOwnProperty(key)) {
                    if (
                        source[key] &&
                        typeof source[key] === 'object' &&
                        !Array.isArray(source[key]) &&
                        target[key] &&
                        typeof target[key] === 'object' &&
                        !Array.isArray(target[key])
                    ) {
                        target[key] = mergeDeep(
                            { ...target[key] },
                            source[key],
                        );
                    } else {
                        target[key] = source[key];
                    }
                }
            }

            return target;
        };

        this.setRequestParams(mergeDeep({ ...currentParams }, newParams));
    }

    setRequestParams(params) {
        const encoded = btoa(JSON.stringify(params));

        this.alpineComponent.$refs.params.innerHTML = `JSON.parse(atob('${encoded}'))`;
    }

    setRequestUrl(url) {
        this.alpineComponent.request.url = url;
    }

    getRequestUrl() {
        return this.alpineComponent.request.url;
    }

    getRequestParams() {
        return Alpine.evaluate(
            this.alpineComponent,
            this.alpineComponent.$refs.params.innerHTML,
        );
    }

    getSelect() {
        return document
            .getElementById(this.id)
            .querySelector('[x-data^="tallstackui_select"]');
    }
}

export default function (id) {
    return new TallstackUISelect(id);
}
