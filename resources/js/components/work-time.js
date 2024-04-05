export default function (){
    return {
        currentWorkTime: null, // $wire.entangle('workTime'),
        time: 0,
        open: false,
        activeWorkTimes: null, //$wire.entangle('activeWorkTimes'),
        trackable_type:  null, //$wire.entangle('workTime.trackable_type'),
        runningTimers: {},
        init() {
            this.activeWorkTimes.forEach((workTime) => {
                if(workTime.ended_at) {
                    return;
                }
                this.startTimer(workTime);
            });

            this.$watch('activeWorkTimes', (value) => {
                console.log('watch', value,this.runningTimers);
                this.activeWorkTimes.forEach((workTime) => {
                    if(workTime.ended_at) {
                        if(this.runningTimers[workTime.id]) {
                            clearInterval(this.runningTimers[workTime.id]);
                            delete this.runningTimers[workTime.id];
                            console.log('CLEAR', this.runningTimers);
                        }
                        return;
                    }
                    this.startTimer(workTime);
                });
            });

            this.$watch('trackable_type', () => {
                this.relatedSelected(this.trackable_type);
            });
        },
        relatedSelected(type) {
            let searchRoute = `  '\'' . route('search', '__model__') . '\'' `;
            $wire.workTime.trackable_id = null;
            searchRoute = searchRoute.replace('__model__', type);
            Alpine.$data(document.getElementById('trackable-id').querySelector('[x-data]')).asyncData.api = searchRoute;
        },
        recordSelected(data) {
            if (! data) {
                return;
            }

            data.contact_id ? $wire.workTime.contact_id = data.contact_id : null;
            data.description ? $wire.workTime.description = data.description : null;
            data.label ? $wire.workTime.name = data.label : null;
        },
        calculateTime(workTime) {
            let diff = (workTime.ended_at ? new Date(workTime.ended_at) : new Date(new Date().toUTCString())) - new Date(workTime.started_at);
            return diff - workTime.paused_time_ms;
        },
        startTimer(workTime) {
            if(this.runningTimers[workTime.id]) {
                return;
            }
            console.log('interval', 'START');
            const intervalId = setInterval(() => {
                this.time += 1000;
                document.querySelector(`#active-work-times [data-id='${workTime.id}']`).innerHTML = this.msTimeToString(this.calculateTime(workTime));
            }, 1000);
            this.runningTimers[workTime.id] = intervalId;
        },
        msTimeToString(time) {
            let seconds = Math.floor(time / 1000);
            let minutes = Math.floor(seconds / 60);
            seconds = seconds % 60;
            let hours = Math.floor(minutes / 60);
            minutes = minutes % 60;

            hours = hours.toString().padStart(2, '0');
            minutes = minutes.toString().padStart(2, '0');
            seconds = seconds.toString().padStart(2, '0');

            return `${hours}:${minutes}:${seconds}`;
        },
        stopWorkDay() {
            $wire.toggleWorkDay(false);
            //TODO: clear all timers
            this.time = 0;
        },
        async stopWorkTime(workTime) {
            // TODO: clear time interval for that case
            await $wire.stop(workTime.id);

        },
        async pauseWorkTime(workTime) {
            await $wire.pause(workTime.id);
        },
        async continueWorkTime(workTime) {
            await $wire.continue(workTime.id)
        }
    }
}
