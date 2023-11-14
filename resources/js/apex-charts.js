import ApexCharts from 'apexcharts';
import colors from 'tailwindcss/colors'

window.colors = colors;
window.ApexCharts = ApexCharts;

document.addEventListener('alpine:init', () => {
    window.Alpine.data('apex_chart',
        () => ({
        chart: null,
        chartType: null,
        init() {
            this.$el.setAttribute('apex_chart', '');
            document.addEventListener('livewire:navigating', () => {
                this.chart.destroy();
            });
            this.$wire.getOptions().then((options) => {
                this.mapLivewireData(options);
                this.chartType = this.livewireOptions.chart.type;
                this.chart = new ApexCharts(this.$el.querySelector('.chart'), this.options);

                this.chart.render();

                this.$watch('chartType', () => {
                    this.options.chart.type = this.chartType;
                    if (this.chartType === 'area') {
                        this.options.fill.opacity = 0.9;
                    } else {
                        this.options.fill.opacity = 1;
                    }
                    this.chart.updateOptions(this.options);
                });
            });
        },
        updateData() {
            this.$wire.getOptions().then((options) => {
                this.mapLivewireData(options);
                this.chart.updateOptions(this.options);
            });
        },
        mapLivewireData(options) {
            options.series = options.series || [];
            options.chart.type = this.chartType || options.chart.type;

            options.series?.map((series) => {
                if (typeof series !== 'object') {
                    return series;
                }
                series.sum = series.data?.reduce((a, b) => a + b, 0);

                if (! series.hasOwnProperty('color')) {
                    return series;
                }

                const colorString = series.color.split('-');
                const color = colorString[0] || 'blue';
                const weight = colorString[1] || 500;
                series.color = window.colors[color][weight];
                series.colorName = color;
            });

            this.livewireOptions = options;
        },
        mergeDeep(target, ...sources) {
            const isObject = (obj) => obj && typeof obj === 'object';

            if (!isObject(target)) {
                throw new Error('Target must be an object');
            }

            for (const source of sources) {
                if (!isObject(source)) {
                    continue; // Skip non-object sources
                }

                Object.keys(source).forEach(key => {
                    const targetValue = target[key];
                    const sourceValue = source[key];

                    if (key === 'data' || targetValue === undefined) {
                        // If the key doesn't exist in target or key is 'data', replace it
                        target[key] = sourceValue;
                    } else if (Array.isArray(targetValue) && Array.isArray(sourceValue)) {
                        target[key] = targetValue.concat(sourceValue);
                    } else if (isObject(targetValue) && isObject(sourceValue)) {
                        target[key] = this.mergeDeep(Object.assign({}, targetValue), sourceValue);
                    } else {
                        target[key] = sourceValue;
                    }
                });
            }

            return target;
        },
        get options() {
            let options = this.mergeDeep({}, this.defaultOptions, this.livewireOptions);
            options.chart.type = this.chartType || options?.chart?.type;

            return options;
        },
        livewireOptions: {},
        defaultOptions: {
            noData: {
                text: undefined,
                align: 'center',
                verticalAlign: 'middle',
                offsetX: 0,
                offsetY: 0,
                style: {
                    color: undefined,
                    fontSize: '14px',
                    fontFamily: undefined
                }
            },
            chart: {
                type: null,
            },
            yaxis: {
                labels: {
                    formatter: function(val) {
                        return window.formatters.money(val);
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return window.formatters.money(val);
                    }
                }
            }
        },
    }))
});
