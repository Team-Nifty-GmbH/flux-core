import ApexCharts from 'apexcharts';
import colors from 'tailwindcss/colors';

window.colors = colors;
window.ApexCharts = ApexCharts;

function apexCharts($wire) {
    return {
        chart: null,
        chartType: null,
        livewireOptions: {},
        height: null,
        widgetOptions: [],
        async loadWidgetOptions() {
            this.widgetOptions = await $wire.getWidgetOptions();
        },
        init() {
            this.$el.setAttribute('apex_chart', '');

            if (this.$el.querySelector('.chart').clientHeight === 0) {
                return;
            }

            this.height = this.$el.querySelector('.chart').clientHeight;
            document.addEventListener('livewire:navigating', () => {
                this.chart.destroy();
            });

            this.mapLivewireData($wire.options);
            this.chartType = this.livewireOptions.chart.type;
            this.chart = new ApexCharts(
                this.$el.querySelector('.chart'),
                this.options,
            );

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

            $wire.$watch('options', () => {
                this.updateData();
            });
        },
        get dataLabelsFormatter() {
            if (
                $wire.__instance.originalEffects.js?.hasOwnProperty(
                    'dataLabelsFormatter',
                )
            ) {
                return new Function(
                    'val',
                    'opts',
                    $wire.__instance.originalEffects.js.dataLabelsFormatter,
                );
            }

            return null;
        },
        get xAxisFormatter() {
            if (
                $wire.__instance.originalEffects.js?.hasOwnProperty(
                    'xAxisFormatter',
                )
            ) {
                return new Function(
                    'val',
                    $wire.__instance.originalEffects.js.xAxisFormatter,
                );
            }
        },
        get yAxisFormatter() {
            if (
                $wire.__instance.originalEffects.js?.hasOwnProperty(
                    'yAxisFormatter',
                )
            ) {
                return new Function(
                    'val',
                    $wire.__instance.originalEffects.js.yAxisFormatter,
                );
            }

            return null;
        },
        get toolTipFormatter() {
            if (
                $wire.__instance.originalEffects.js?.hasOwnProperty(
                    'toolTipFormatter',
                )
            ) {
                return new Function(
                    'val',
                    $wire.__instance.originalEffects.js.toolTipFormatter,
                );
            }

            return null;
        },
        get plotOptionsTotalFormatter() {
            if (
                $wire.__instance.originalEffects.js?.hasOwnProperty(
                    'plotOptionsTotalFormatter',
                )
            ) {
                return new Function(
                    'w',
                    $wire.__instance.originalEffects.js
                        .plotOptionsTotalFormatter,
                );
            }

            return null;
        },
        updateData() {
            if (this.chart === null) {
                return;
            }

            this.mapLivewireData($wire.options);
            this.chart.updateOptions(this.options);
        },
        toKebabCase(str) {
            return str
                .replace(/([a-z])([A-Z])/g, '$1-$2') // Insert dash between lowercase and uppercase
                .toLowerCase();
        },

        // Alternative: Extract only the data you actually need
        extractUsefulEventData(eventName, args) {
            switch (eventName) {
                case 'legendClick':
                    return {
                        seriesIndex: args[1],
                        seriesName:
                            args[2]?.globals?.seriesNames?.[args[1]] || null,
                        isHidden:
                            args[2]?.globals?.collapsedSeriesIndices?.includes(
                                args[1],
                            ) || false,
                    };

                case 'markerClick':
                    return {
                        seriesIndex: args[2]?.seriesIndex,
                        dataPointIndex: args[2]?.dataPointIndex,
                        value:
                            args[2]?.config?.series?.[args[2]?.seriesIndex]
                                ?.data?.[args[2]?.dataPointIndex] || null,
                    };

                case 'dataPointSelection':
                    return {
                        seriesIndex: args[2]?.seriesIndex,
                        dataPointIndex: args[2]?.dataPointIndex,
                        selectedDataPoints: args[2]?.selectedDataPoints || [],
                    };

                default:
                    // For other events, try to extract basic info safely
                    return {
                        eventType: eventName,
                        timestamp: Date.now(),
                    };
            }
        },

        smartForwardEvent(eventName, ...args) {
            const kebabEventName = this.toKebabCase(eventName);

            // Use the safer extraction method
            const payload = this.extractUsefulEventData(eventName, args);
            this.$dispatch(`apex-${kebabEventName}`, {
                type: eventName,
                payload: payload,
            });
        },

        generateEvents() {
            const events = {};
            const apexEvents = [
                'beforeMount',
                'mounted',
                'updated',
                'click',
                'legendClick',
                'markerClick',
                'selection',
                'dataPointSelection',
                'beforeZoom',
                'beforeResetZoom',
                'zoomed',
                'scrolled',
                'brushScrolled',
            ];

            apexEvents.forEach((eventName) => {
                events[eventName] = (...args) => {
                    this.smartForwardEvent(eventName, ...args);
                };
            });

            return events;
        },
        mapLivewireData(options) {
            options = JSON.parse(JSON.stringify(options));
            options.series = options.series || [];
            options.labels = options.labels || [];

            options.chart.type = this.chartType || options.chart.type;

            options.series = options.series?.map((series) => {
                if (typeof series !== 'object') {
                    return typeof series === 'string'
                        ? parseFloat(series)
                        : series;
                }

                series.sum = series.data?.reduce(
                    (a, b) => parseFloat(a) + parseFloat(b),
                    0,
                );

                if (!series.hasOwnProperty('color')) {
                    return series;
                }

                if (!series.color.startsWith('#')) {
                    const colorString = series.color.split('-');
                    const color = colorString[0] || 'blue';
                    const weight = colorString[1] || 500;
                    series.color = window.colors[color][weight];
                    series.colorName = color;
                }

                return series;
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

                Object.keys(source).forEach((key) => {
                    const targetValue = target[key];
                    const sourceValue = source[key];

                    if (key === 'data' || targetValue === undefined) {
                        // If the key doesn't exist in target or key is 'data', replace it
                        target[key] = sourceValue;
                    } else if (
                        Array.isArray(targetValue) &&
                        Array.isArray(sourceValue)
                    ) {
                        target[key] = targetValue.concat(sourceValue);
                    } else if (isObject(targetValue) && isObject(sourceValue)) {
                        target[key] = this.mergeDeep(
                            Object.assign({}, targetValue),
                            sourceValue,
                        );
                    } else {
                        target[key] = sourceValue;
                    }
                });
            }

            return target;
        },
        get options() {
            let options = this.mergeDeep(
                {},
                this.defaultOptions,
                this.livewireOptions,
            );
            options.chart.type = this.chartType || options?.chart?.type;

            return options;
        },
        get defaultOptions() {
            const isDark = document.documentElement.classList.contains('dark');

            return {
                noData: {
                    text: undefined,
                    align: 'center',
                    verticalAlign: 'middle',
                    offsetX: 0,
                    offsetY: 0,
                    style: {
                        color: isDark ? '#9ca3af' : '#6b7280',
                        fontSize: '14px',
                        fontFamily: undefined,
                    },
                },
                legend: {
                    position: 'bottom',
                    horizontalAlign: 'center',
                    fontSize: '12px',
                    labels: {
                        colors: isDark ? '#9ca3af' : '#6b7280',
                    },
                    markers: {
                        size: 4,
                        shape: 'circle',
                    },
                    itemMargin: {
                        horizontal: 8,
                        vertical: 4,
                    },
                },
                chart: {
                    redrawOnParentResize: true,
                    type: null,
                    height: this.height,
                    fontFamily: 'inherit',
                    toolbar: {
                        show: true,
                        tools: {
                            download: true,
                            selection: false,
                            zoom: false,
                            zoomin: false,
                            zoomout: false,
                            pan: false,
                            reset: false,
                        },
                    },
                    events: this.generateEvents(),
                },
                stroke: {
                    curve: 'smooth',
                    width: 2,
                },
                grid: {
                    borderColor: isDark ? '#374151' : '#e5e7eb',
                    strokeDashArray: 3,
                },
                colors: [
                    colors.sky[400],
                    colors.emerald[400],
                    colors.amber[400],
                    colors.rose[400],
                    colors.violet[400],
                    colors.cyan[400],
                    colors.orange[400],
                    colors.indigo[400],
                    colors.teal[400],
                    colors.pink[400],
                ],
                fill: {
                    opacity: 1,
                },
                dataLabels: {
                    formatter:
                        this.dataLabelsFormatter ??
                        function (val) {
                            return val;
                        },
                },
                xaxis: {
                    labels: {
                        style: {
                            colors: isDark ? '#9ca3af' : '#6b7280',
                            fontSize: '12px',
                        },
                        formatter:
                            this.xAxisFormatter ??
                            function (val) {
                                return val;
                            },
                    },
                    axisBorder: {
                        show: false,
                    },
                    axisTicks: {
                        show: false,
                    },
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: isDark ? '#9ca3af' : '#6b7280',
                            fontSize: '12px',
                        },
                        formatter:
                            this.yAxisFormatter ??
                            function (val) {
                                return val;
                            },
                    },
                },
                tooltip: {
                    theme: isDark ? 'dark' : 'light',
                    y: {
                        formatter:
                            this.toolTipFormatter ??
                            function (val) {
                                return val;
                            },
                    },
                },
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        borderRadiusApplication: 'end',
                        columnWidth: '60%',
                    },
                    pie: {
                        donut: {
                            labels: {
                                show: true,
                                value: {
                                    formatter:
                                        this.dataLabelsFormatter ??
                                        function (val) {
                                            return val;
                                        },
                                },
                                total: {
                                    show: true,
                                    formatter:
                                        this.plotOptionsTotalFormatter ??
                                        function (w) {
                                            return w.globals.seriesTotals.reduce(
                                                (a, b) => {
                                                    return a + b;
                                                },
                                                0,
                                            );
                                        },
                                },
                            },
                        },
                    },
                    radialBar: {
                        dataLabels: {
                            total: {
                                show: true,
                                label: 'Total',
                                formatter:
                                    this.plotOptionsTotalFormatter ??
                                    function (w) {
                                        return w.globals.seriesTotals.reduce(
                                            (a, b) => {
                                                return a + b;
                                            },
                                            0,
                                        );
                                    },
                            },
                        },
                    },
                },
            };
        },
    };
}

window.apexCharts = apexCharts;
export default apexCharts;
