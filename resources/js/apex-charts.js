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
            document.addEventListener('livewire:navigating', () => {
                this.chart.destroy();
            });
        },
        renderChart() {
            this.chart = new ApexCharts(this.$el.querySelector('.chart'), this.options);
            this.chart.render();
        },
        getLivewireData() {
            this.$wire.getOptions().then((options) => {
                this.options = options;

                this.options.series = this.options.series.map((series, index) => {
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
                this.renderChart();
            });
        },
        options: {
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
