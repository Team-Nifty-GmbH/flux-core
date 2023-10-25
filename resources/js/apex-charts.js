import ApexCharts from 'apexcharts';
import colors from 'tailwindcss/colors'

window.colors = colors;
window.ApexCharts = ApexCharts;

document.addEventListener('alpine:init', () => {
    window.Alpine.data('apex_chart',
        () => ({
        chart: null,
        init()
        {
            this.getLivewireData();
            this.$el.setAttribute('apex_chart', '');
            document.addEventListener('livewire:navigating', () => {
                this.chart.destroy();
            });
            this.$watch('options.chart.type', () => {
                this.chart.updateOptions(this.options);
            });
        },
        renderChart() {
            this.chart = new ApexCharts(this.$el.querySelector('.chart'), this.options);
            this.chart.render();
        },
        getLivewireData() {
            this.$wire.getOptions().then((options) => {

                options.series = options.series.map((series, index) => {
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


                    return series;
                });

                this.options = Object.assign(this.options, options);

                this.renderChart();
            });
        },
        updateData() {
            this.$wire.getOptions().then((options) => {
                options.series = options.series.map((series) => {
                    if (typeof series !== 'object') {
                        return series;
                    }

                    series.sum = series.data.reduce((a, b) => a + b, 0);

                    if (! series.hasOwnProperty('color')) {
                        return series;
                    }

                    const colorString = series.color.split('-');
                    const color = colorString[0] || 'blue';
                    const weight = colorString[1] || 500;
                    series.color = window.colors[color][weight];
                    series.colorName = color;

                    return series;
                });
                this.options = Object.assign(this.options, options);
                //
                // this.chart.updateOptions(this.options)
                // this.options.series = options.series;
            });
        },
        options: {
            chart: {
                type: 'bar',
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
