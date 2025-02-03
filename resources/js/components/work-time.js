export default function ($wire, route) {
    return {
        currentWorkTime: $wire.entangle('workTime'),
        time: 0,
        open: false,
        activeWorkTimes: $wire.entangle('activeWorkTimes'),
        trackable_type: $wire.entangle('workTime.trackable_type'),
        runningTimers: {},
        destroy() {
            const keys = Object.keys(this.runningTimers);
            keys.forEach((key) => {
                clearInterval(this.runningTimers[key]);
            });
        },
        // when using init - with lazy load - can trigger init function several times
        // hence rename to arbitrary name - and pass it to the x-init.once
        load() {
            this.activeWorkTimes.forEach((workTime) => {
                if (workTime.ended_at) {
                    return;
                }
                this.startTimer(workTime);
            });

            this.time = this.activeWorkTimes.reduce((acc, workTime) => {
                return this.calculateTime(workTime) + acc;
            }, 0);


            this.$watch('activeWorkTimes', (value) => {
                this.activeWorkTimes.forEach((workTime) => {
                    if (workTime.ended_at) {
                        if (this.runningTimers[workTime.id]) {
                            // only covers pause case
                            clearInterval(this.runningTimers[workTime.id]);
                            delete this.runningTimers[workTime.id];
                        }
                        return;
                    }
                    // covers start and continue case
                    this.startTimer(workTime);
                });
            });

            this.$watch('trackable_type', () => {
                this.relatedSelected(this.trackable_type);
            });
        },
        relatedSelected(type) {
            let searchRoute = route;
            $wire.workTime.trackable_id = null;
            searchRoute = searchRoute + '/' + type;
            Alpine.$data(document.getElementById('trackable-id').querySelector('[x-data]')).asyncData.api = searchRoute;
        },
        recordSelected(data) {
            if (!data) {
                return;
            }

            $wire.recordSelected(data);
        },
        calculateTime(workTime) {
            const startedAt = new Date(workTime.started_at);
            const endedAt = workTime.ended_at ? new Date(workTime.ended_at) : new Date();
            let diff = endedAt - startedAt;
            return diff - workTime.paused_time_ms;
        },
        startTimer(workTime) {
            // called on init add new edit and continue
            if (this.runningTimers[workTime.id]) {
                return;
            }
            this.runningTimers[workTime.id] = setInterval(() => {
                // the greatest work time id should calculate time sum of all active work times
                const greatestId = Object.keys(this.runningTimers)
                    .map(e => Number.parseInt(e))
                    .sort((a, b) => a - b).pop();
                if (greatestId === workTime.id) {
                    this.time = this.activeWorkTimes.reduce((acc, workTime) => {
                        return this.calculateTime(workTime) + acc;
                    }, 0);
                }
                document.querySelector(`#active-work-times [data-id='${workTime.id}']`).innerHTML = this.msTimeToString(this.calculateTime(workTime));
            }, 1000);
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
            // clear all the intervals and reset the time to 0
            const keys = Object.keys(this.runningTimers);
            keys.forEach((key) => {
                clearInterval(this.runningTimers[key]);
            });
            this.runningTimers = {};
            $wire.toggleWorkDay(false);
            this.time = 0;
        },
        async stopWorkTime(workTime) {
            // clear appropriate timer - watcher will not have access to workTime
            // since it is removed from an array
            if (this.runningTimers[workTime.id]) {
                clearInterval(this.runningTimers[workTime.id]);
                delete this.runningTimers[workTime.id];
            }
            await $wire.stop(workTime.id);
            // in case all active times are stopped recalculate time
            if (Object.keys(this.runningTimers).length === 0) {
                this.time = this.activeWorkTimes.reduce((acc, workTime) => {
                    return this.calculateTime(workTime) + acc;
                }, 0);
            }
        },
        async pauseWorkTime(workTime) {
            // timer clean up happens in watcher
            await $wire.pause(workTime.id);
        },
        async continueWorkTime(workTime) {
            // timer start takes place in watcher
            await $wire.continue(workTime.id);
        }
    }
}
