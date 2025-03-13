import ApexCharts from "apexcharts";
import colors from "tailwindcss/colors";

window.colors = colors;
window.ApexCharts = ApexCharts;

export default function ($wire) {
    return {
        chart: null,
        chartType: null,
        livewireOptions: {},
        height: null,
        init() {
            this.$el.setAttribute("apex_chart", "");

            if (this.$el.querySelector(".chart").clientHeight === 0) {
                return;
            }

            this.height = this.$el.querySelector(".chart").clientHeight;
            document.addEventListener("livewire:navigating", () => {
                this.chart.destroy();
            });

            this.mapLivewireData($wire.options);
            this.chartType = this.livewireOptions.chart.type;
            this.chart = new ApexCharts(
                this.$el.querySelector(".chart"),
                this.options,
            );

            this.chart.render();

            this.$watch("chartType", () => {
                this.options.chart.type = this.chartType;
                if (this.chartType === "area") {
                    this.options.fill.opacity = 0.9;
                } else {
                    this.options.fill.opacity = 1;
                }
                this.chart.updateOptions(this.options);
            });
        },
        get dataLabelsFormatter() {
            if (
                $wire.__instance.originalEffects.js?.hasOwnProperty(
                    "dataLabelsFormatter",
                )
            ) {
                return new Function(
                    "val",
                    "opts",
                    $wire.__instance.originalEffects.js.dataLabelsFormatter,
                );
            }

            return null;
        },
        get xAxisFormatter() {
            if (
                $wire.__instance.originalEffects.js?.hasOwnProperty(
                    "xAxisFormatter",
                )
            ) {
                return new Function(
                    "val",
                    $wire.__instance.originalEffects.js.xAxisFormatter,
                );
            }
        },
        get yAxisFormatter() {
            if (
                $wire.__instance.originalEffects.js?.hasOwnProperty(
                    "yAxisFormatter",
                )
            ) {
                return new Function(
                    "val",
                    $wire.__instance.originalEffects.js.yAxisFormatter,
                );
            }

            return null;
        },
        get toolTipFormatter() {
            if (
                $wire.__instance.originalEffects.js?.hasOwnProperty(
                    "toolTipFormatter",
                )
            ) {
                return new Function(
                    "val",
                    $wire.__instance.originalEffects.js.toolTipFormatter,
                );
            }

            return null;
        },
        get plotOptionsTotalFormatter() {
            if (
                $wire.__instance.originalEffects.js?.hasOwnProperty(
                    "plotOptionsTotalFormatter",
                )
            ) {
                return new Function(
                    "w",
                    $wire.__instance.originalEffects.js.plotOptionsTotalFormatter,
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
        mapLivewireData(options) {
            options = JSON.parse(JSON.stringify(options));
            options.series = options.series || [];
            options.labels = options.labels || [];

            options.chart.type = this.chartType || options.chart.type;

            options.series = options.series?.map((series) => {
                if (typeof series !== "object") {
                    return typeof series === "string"
                        ? parseFloat(series)
                        : series;
                }

                series.sum = series.data?.reduce(
                    (a, b) => parseFloat(a) + parseFloat(b),
                    0,
                );

                if (!series.hasOwnProperty("color")) {
                    return series;
                }

                if (!series.color.startsWith("#")) {
                    const colorString = series.color.split("-");
                    const color = colorString[0] || "blue";
                    const weight = colorString[1] || 500;
                    series.color = window.colors[color][weight];
                    series.colorName = color;
                }

                return series;
            });

            this.livewireOptions = options;
        },
        mergeDeep(target, ...sources) {
            const isObject = (obj) => obj && typeof obj === "object";

            if (!isObject(target)) {
                throw new Error("Target must be an object");
            }

            for (const source of sources) {
                if (!isObject(source)) {
                    continue; // Skip non-object sources
                }

                Object.keys(source).forEach((key) => {
                    const targetValue = target[key];
                    const sourceValue = source[key];

                    if (key === "data" || targetValue === undefined) {
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
            return {
                noData: {
                    text: undefined,
                    align: "center",
                    verticalAlign: "middle",
                    offsetX: 0,
                    offsetY: 0,
                    style: {
                        color: undefined,
                        fontSize: "14px",
                        fontFamily: undefined,
                    },
                },
                legend: {
                    position: "top",
                    horizontalAlign: "left",
                },
                chart: {
                    redrawOnParentResize: true,
                    type: null,
                    height: this.height,
                    fontFamily: "inherit",
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
                        formatter:
                            this.xAxisFormatter ??
                            function (val) {
                                return val;
                            },
                    },
                },
                yaxis: {
                    labels: {
                        formatter:
                            this.yAxisFormatter ??
                            function (val) {
                                return val;
                            },
                    },
                },
                tooltip: {
                    y: {
                        formatter:
                            this.toolTipFormatter ??
                            function (val) {
                                return val;
                            },
                    },
                },
                plotOptions: {
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
                },
            };
        },
    };
}
