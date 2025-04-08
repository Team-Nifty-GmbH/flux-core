const calendar = () => {
    return {
        calendarItem: {},
        height: 0,
        parseDateTime(event, locale, property) {
            const dateTime = new Date(event.start);
            let config = null;
            if (event.is_all_day === true) {
                config = {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                };
            } else {
                config = {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit',
                };
            }

            return dateTime.toLocaleString(locale, config);
        },
        inviteStatus(calendarEvent, status) {
            calendarEvent.status = status;
            if (this.calendarItem.resourceEditable === false) {
                this.calendarClick(
                    this.calendars.find((c) => c.resourceEditable === true),
                );
            }

            const existingEvent = this.calendar.getEventById(
                calendarEvent.calendar_event.id,
            );

            if (
                (status === 'accepted' || status === 'maybe') &&
                !existingEvent
            ) {
                this.calendar.addEvent(
                    calendarEvent.calendar_event,
                    this.calendar.getEventSourceById(this.calendarId),
                );
            } else if (status === 'declined' && existingEvent) {
                existingEvent.remove();
            }

            this.$wire.inviteStatus(calendarEvent.id, status, this.calendarId);
        },
        calendarClick(calendar) {
            this.calendarId = calendar.id;
            this.calendarItem = calendar;
        },
        getFolderTree() {
            return Alpine.$data(
                this.$wire.$el.querySelector('[x-data^="folder_tree"]'),
            );
        },
        async saveCalendar() {
            const success = await this.$wire.saveCalendar();

            if (!success) {
                return false;
            }

            this.$wire.calendarObject.parentId ??= 'my-calendars';

            if (this.$wire.calendarObject.isNew) {
                // Add new calendar
                if (!this.$wire.calendarObject.is_group) {
                    // Add calendar as event source
                    this.calendar.addEventSource(this.$wire.calendarObject);
                } else {
                    this.$wire.calendarObject.children = [];
                }

                this.getFolderTree().addFolder(
                    this.getFolderTree().getNodeById(
                        this.$wire.calendarObject.parentId,
                    ),
                    this.$wire.calendarObject,
                );
            } else {
                this.getFolderTree().updateNode(this.$wire.calendarObject);
            }

            return true;
        },
        deleteCalendar() {
            this.calendar.getEventSourceById(this.calendarItem.id)?.remove();

            this.getFolderTree().removeNode(this.calendarItem.id);

            $modalClose('calendar-modal');
        },
        saveEvent() {
            this.$wire.saveEvent(this.$wire.calendarEvent).then((event) => {
                if (event === false) {
                    return false;
                }

                if (event instanceof Array) {
                    event
                        .map((item) => String(item.id).split('|')[0])
                        .map(this.mapDatesToUtc)
                        .filter(
                            (value, index, self) =>
                                self.indexOf(value) === index,
                        )
                        .forEach((id) => {
                            this.calendar
                                .getEvents()
                                .filter(
                                    (filter) =>
                                        filter.id.split('|')[0] === String(id),
                                )
                                .forEach((e) => e.remove());
                        });

                    event.forEach((e) => {
                        this.calendar.addEvent(
                            e,
                            this.calendar.getEventSourceById(e.calendar_id),
                        );
                    });
                } else {
                    this.calendar.getEventById(event.id)?.remove();
                    this.calendar.addEvent(
                        this.mapDatesToUtc(event),
                        this.calendar.getEventSourceById(event.calendar_id),
                    );
                }

                $modalClose('calendar-event-modal');
            });
        },
        setDateTime(type, event) {
            const date =
                event.target.parentNode.parentNode.parentNode.parentNode.parentNode.querySelector(
                    'input[type="date"]',
                ).value;
            let time =
                event.target.parentNode.parentNode.parentNode.parentNode.parentNode.querySelector(
                    'input[type="time"]',
                ).value;

            if (this.$wire.event.allDay) {
                time = '00:00:00';
            }

            let dateTime = dayjs(date + ' ' + time);

            if (type === 'start') {
                this.$wire.event.start = dateTime.format(); // Use the default ISO 8601 format
            } else {
                this.$wire.event.end = dateTime.format(); // Use the default ISO 8601 format
            }
        },
        mapDatesToUtc(event) {
            event.start = dayjs(event.start).utc(true).format();
            event.end = dayjs(event.end).utc(true).format();
            event.repeat_end = event.repeat_end
                ? dayjs(event.repeat_end)
                    .utc(true)
                    .format()
                : null;

            return event;
        },
        deleteEvent(event) {
            if (event === false) {
                return false;
            }

            switch (true) {
                case event.repetition === null:
                    this.calendar.getEventById(event.id)?.remove();
                    break;
                case event.confirmOption === 'this':
                    this.calendar
                        .getEventById(event.id + '|' + event.repetition)
                        ?.remove();
                    break;
                case event.confirmOption === 'future':
                case event.confirmOption === 'all':
                    this.calendar
                        .getEvents()
                        .filter((e) => {
                            const split = e.id.split('|');
                            if (event.confirmOption === 'future') {
                                return (
                                    split[0] === String(event.id) &&
                                    split[1] >= event.repetition
                                );
                            } else {
                                return split[0] === String(event.id);
                            }
                        })
                        .forEach((e) => e.remove());
                    break;
            }

            $modalClose('calendar-event-modal');
        },
        calendar: null,
        config: {},
        calendarId: null,
        calendars: [],
        invites: [],
        calendarEvent: {},
        dispatchCalendarEvents(eventName, params) {
            const eventNameKebap = eventName
                .replace(/([a-z0-9]|(?=[A-Z]))([A-Z])/g, '$1-$2')
                .toLowerCase();
            params = { ...params, ...{ trigger: eventNameKebap } };
            this.$wire.dispatch(`calendar-${eventNameKebap}`, params);
        },
        getCalendarEventSources() {
            this.traverseCalendars(this.calendars, (calendar) => {
                // Set `calendarId` for the first encountered calendar
                if (
                    this.calendarItem &&
                    Object.keys(this.calendarItem).length === 0
                ) {
                    this.calendarItem = calendar;
                }

                // Assign the `events` function
                calendar.events = async (info) => {
                    calendar.isLoading = true;
                    try {
                        return (await this.$wire.getEvents(info, calendar)).map(this.mapDatesToUtc);
                    } finally {
                        calendar.isLoading = false;
                    }
                };
            });

            return this.calendars;
        },
        toggleEventSource(calendar) {
            const calendarEventSource = this.calendar.getEventSourceById(
                calendar.id,
            );
            if (calendarEventSource) {
                this.hideEventSource(calendar, false);
            } else {
                this.showEventSource(calendar, false);
            }

            this.dispatchCalendarEvents(
                'toggleEventSource',
                this.calendar
                    .getEventSources()
                    .map((source) => source.internalEventSource),
            );
        },
        changedHeight() {
            this.height =
                this.$el.parentNode.offsetHeight - this.$el.offsetTop - 2;
        },
        showEventSource(calendar) {
            this.calendar.addEventSource(calendar);
        },
        hideEventSource(calendar) {
            this.calendar.getEventSourceById(calendar.id)?.remove();
        },
        init() {
            this.$wire.getCalendars().then((calendars) => {
                this.calendars = calendars;
            });
            this.$wire.getConfig().then((config) => {
                this.config = config;
                this.initCalendar();
            });
            this.$wire.getInvites().then((invites) => {
                this.invites = invites;
            });
        },
        flattenCalendars(calendars, parentPath = '') {
            let result = [];

            for (const calendar of calendars) {
                // Create a copy of the calendar object to avoid modifying the original
                const calendarCopy = { ...calendar };

                // Create the path for this calendar
                const currentPath = parentPath
                    ? `${parentPath} -> ${calendar.name}`
                    : calendar.name;

                // Add the path to the calendar copy
                calendarCopy.displayName = currentPath;

                // Add the modified calendar to the result
                result.push(calendarCopy);

                // If this calendar has children, recursively flatten them with the updated path
                if (
                    calendar.children &&
                    Array.isArray(calendar.children) &&
                    calendar.children.length > 0
                ) {
                    result = result.concat(
                        this.flattenCalendars(calendar.children, currentPath),
                    );
                }
            }

            return result;
        },
        initCalendar() {
            let calendarEl = this.$el.querySelector('[calendar]');

            let defaultConfig = {
                plugins: [
                    dayGridPlugin,
                    timeGridPlugin,
                    listPlugin,
                    interactionPlugin,
                ],
                initialView: 'dayGridMonth',
                slotDuration: '00:15:00',
                initialDate: new Date(),
                editable: true,
                selectable: true,
                selectMirror: true,
                dayMaxEvents: true,
                eventSources: [],
                select: (selectionInfo) => {
                    this.dispatchCalendarEvents('select', selectionInfo);
                },
                unselect: (jsEvent, view) => {
                    this.dispatchCalendarEvents('unselect', { jsEvent, view });
                },
                dateClick: (dateClickInfo) => {
                    dateClickInfo.view.dateEnv.timeZone =
                        Intl.DateTimeFormat().resolvedOptions().timeZone;
                    this.dispatchCalendarEvents('dateClick', dateClickInfo);
                },
                viewDidMount: (viewDidMountInfo) => {
                    this.dispatchCalendarEvents(
                        'viewDidMount',
                        viewDidMountInfo,
                    );
                },
                eventDidMount: (eventDidMountInfo) => {
                    this.dispatchCalendarEvents(
                        'eventDidMount',
                        eventDidMountInfo,
                    );
                },
                eventClick: (eventClickInfo) => {
                    this.dispatchCalendarEvents('eventClick', eventClickInfo);
                },
                eventMouseEnter: (eventMouseEnterInfo) => {
                    this.dispatchCalendarEvents(
                        'eventMouseEnter',
                        eventMouseEnterInfo,
                    );
                },
                eventMouseLeave: (eventMouseLeaveInfo) => {
                    this.dispatchCalendarEvents(
                        'eventMouseLeave',
                        eventMouseLeaveInfo,
                    );
                },
                eventDragStart: (eventDragStartInfo) => {
                    this.dispatchCalendarEvents(
                        'eventDragStart',
                        eventDragStartInfo,
                    );
                },
                eventDragStop: (eventDragStopInfo) => {
                    this.dispatchCalendarEvents(
                        'eventDragStop',
                        eventDragStopInfo,
                    );
                },
                eventDrop: (eventDropInfo) => {
                    this.dispatchCalendarEvents('eventDrop', eventDropInfo);
                },
                eventResizeStart: (eventResizeStartInfo) => {
                    this.dispatchCalendarEvents(
                        'eventResizeStart',
                        eventResizeStartInfo,
                    );
                },
                eventResizeStop: (eventResizeStopInfo) => {
                    this.dispatchCalendarEvents(
                        'eventResizeStop',
                        eventResizeStopInfo,
                    );
                },
                eventResize: (eventResizeInfo) => {
                    this.dispatchCalendarEvents('eventResize', eventResizeInfo);
                },
                drop: (dropInfo) => {
                    this.dispatchCalendarEvents('drop', dropInfo);
                },
                eventReceive: (eventReceiveInfo) => {
                    this.dispatchCalendarEvents(
                        'eventReceive',
                        eventReceiveInfo,
                    );
                },
                eventLeave: (eventLeaveInfo) => {
                    this.dispatchCalendarEvents('eventLeave', eventLeaveInfo);
                },
                eventAdd: (eventAddInfo) => {
                    this.dispatchCalendarEvents('eventAdd', eventAddInfo);
                },
                eventChange: (eventChangeInfo) => {
                    this.dispatchCalendarEvents('eventChange', eventChangeInfo);
                },
                eventRemove: (eventRemoveInfo) => {
                    this.dispatchCalendarEvents('eventRemove', eventRemoveInfo);
                },
                eventsSet: (eventsSetInfo) => {
                    this.dispatchCalendarEvents('eventsSet', eventsSetInfo);
                },
                eventContent(info) {
                    let eventContent = document.createElement('div');
                    eventContent.className =
                        'flex gap-1 justify-between px-1 w-full';

                    // Left side container for badge and title
                    let leftContent = document.createElement('div');
                    leftContent.className =
                        'flex gap-1 items-center min-w-0 flex-1';

                    // Color badge/indicator
                    if (!info.event.allDay) {
                        let calendarBadge = document.createElement('div');
                        calendarBadge.className =
                            'size-3 rounded-full flex-shrink-0';
                        calendarBadge.style.backgroundColor =
                            info.backgroundColor;
                        leftContent.appendChild(calendarBadge);
                    }

                    // Title container with better overflow handling
                    let titleContainer = document.createElement('span');
                    titleContainer.className = 'truncate min-w-0 flex-1';
                    titleContainer.innerHTML = info.event.title;
                    leftContent.appendChild(titleContainer);

                    eventContent.appendChild(leftContent);

                    // Right side container for time and status badges
                    let rightContent = document.createElement('div');
                    rightContent.className =
                        'flex items-center gap-1 flex-shrink-0';

                    // Add status badges if they exist
                    if (info.event.extendedProps.appendTitle) {
                        let statusBadges = document.createElement('div');
                        statusBadges.className = 'flex-shrink-0 mr-1';
                        statusBadges.innerHTML =
                            info.event.extendedProps.appendTitle;
                        rightContent.appendChild(statusBadges);
                    }

                    // Add time if not all day event
                    if (!info.event.allDay && info.timeText) {
                        let timeNode = document.createElement('div');
                        timeNode.className =
                            'flex-shrink-0 whitespace-nowrap text-xs';
                        timeNode.innerHTML = info.timeText;
                        rightContent.appendChild(timeNode);
                    }

                    eventContent.appendChild(rightContent);

                    return { html: eventContent.outerHTML };
                },
            };

            const { activeCalendars, ...filteredConfig } = this.config;

            this.calendar = new Calendar(calendarEl, {
                ...defaultConfig,
                ...filteredConfig,
            });

            this.traverseCalendars(
                this.getCalendarEventSources(),
                (calendar) => {
                    if (
                        this.config.activeCalendars &&
                        !this.config.activeCalendars.includes(
                            String(calendar.id),
                        )
                    ) {
                        return; // Skip inactive calendars
                    }

                    this.showEventSource(calendar);
                },
            );

            this.changedHeight();
            this.calendar.render();
            this.$dispatch('calendar-initialized', this.calendar);
        },
        traverseCalendars(calendars, callback) {
            calendars.forEach((calendar) => {
                callback(calendar);

                if (Array.isArray(calendar.children)) {
                    this.traverseCalendars(calendar.children, callback);
                }
            });
        },
    };
};

export default calendar;
