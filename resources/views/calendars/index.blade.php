<div class="container py-3">
    <link rel="stylesheet" href="{{ asset('calendar/style.css') }}">
    <div id="calendar"></div>

    {{-- moment.js (tu archivo) --}}
    <script src="{{ asset('calendar/moment.min.js') }}"></script>

    {{-- JS adaptado (reemplazo de codigo.js) --}}
    <script>
        @php
            // Puedes servir el JS como archivo. Para hacerlo rápido aquí lo incrusto.
        @endphp

        // ======== codigo-adaptado.js (basado en tu codigo.js) ========
        !function() {
            var today = moment();

            // Helper para mapear etiquetas a color (Desayuno/Azul, Almuerzo/Naranja, Cena/Verde, General/Amarillo)
            const LABEL_COLOR = {
                'Desayuno': 'blue',
                'Almuerzo': 'orange',
                'Cena': 'green',
                'General': 'yellow'
            };

            function Calendar(selector) {
                this.el = document.querySelector(selector);
                this.current = moment().date(1);
                this.events = []; // se llena tras fetch
                this.draw();
                var current = document.querySelector('.today');
                if (current) {
                    var self = this;
                    window.setTimeout(function() { self.openDay(current); }, 400);
                }
            }

            Calendar.prototype.draw = function() {
                this.drawHeader();
                this.drawMonth();   // ahora drawMonth hace fetch antes de pintar
                this.drawLegend();
            }

            Calendar.prototype.drawHeader = function() {
                var self = this;
                if(!this.header) {
                    this.header = createElement('div', 'header');
                    this.header.className = 'header';
                    this.title = createElement('h1');

                    var right = createElement('div', 'right');
                    right.addEventListener('click', function() { self.nextMonth(); });

                    var left = createElement('div', 'left');
                    left.addEventListener('click', function() { self.prevMonth(); });

                    this.header.appendChild(this.title);
                    this.header.appendChild(right);
                    this.header.appendChild(left);
                    this.el.appendChild(this.header);
                }
                this.title.innerHTML = this.current.format('MMMM YYYY');
            }

            Calendar.prototype.drawMonth = async function() {
                var self = this;

                // 1) Pedimos al backend los eventos del mes visible
                const year = this.current.year();
                const month= this.current.month() + 1; // moment 0-based

                try {
                    const url = `{{ route('calendars.index') }}`.replace('/calendars','') + `/api/calendar/month?year=${year}&month=${month}`;
                    const res = await fetch(url, { credentials: 'same-origin' });
                    const json = await res.json();

                    // 2) Convertimos JSON a estructura esperada por el render
                    //    this.events = [{date: moment('YYYY-MM-DD'), entries:[{calendar: 'Desayuno', color:'blue', eventName:'...'}]}]
                    this.events = [];
                    (json.events || []).forEach(e => {
                        const d = moment(e.date, 'YYYY-MM-DD');
                        (e.items || []).forEach(item => {
                            this.events.push({
                                date: d.clone(),
                                calendar: item.label,
                                color: LABEL_COLOR[item.label] || item.color || 'yellow',
                                eventName: item.summary || item.label
                            });
                        });
                    });
                } catch (err) {
                    console.error('Error cargando el mes:', err);
                    this.events = [];
                }

                // 3) Renderiza el mes como en tu original (quitamos la asignación aleatoria)
                if(this.month) {
                    this.oldMonth = this.month;
                    this.oldMonth.className = 'month out ' + (self.next ? 'next' : 'prev');
                    this.oldMonth.addEventListener('animationend', function() {
                        self.oldMonth.parentNode.removeChild(self.oldMonth);
                        self.month = createElement('div', 'month');
                        self.backFill();
                        self.currentMonth();
                        self.fowardFill();
                        self.el.appendChild(self.month);
                        window.setTimeout(function() {
                            self.month.className = 'month in ' + (self.next ? 'next' : 'prev');
                        }, 16);
                    }, { once: true });
                } else {
                    this.month = createElement('div', 'month');
                    this.el.appendChild(this.month);
                    this.backFill();
                    this.currentMonth();
                    this.fowardFill();
                    this.month.className = 'month new';
                }
            }

            Calendar.prototype.backFill = function() {
                var clone = this.current.clone();
                var dayOfWeek = clone.day();
                if(!dayOfWeek) { return; }

                clone.subtract('days', dayOfWeek+1);
                for(var i = dayOfWeek; i > 0 ; i--) {
                    this.drawDay(clone.add('days', 1));
                }
            }

            Calendar.prototype.fowardFill = function() {
                var clone = this.current.clone().add('months', 1).subtract('days', 1);
                var dayOfWeek = clone.day();
                if(dayOfWeek === 6) { return; }

                for(var i = dayOfWeek; i < 6 ; i++) {
                    this.drawDay(clone.add('days', 1));
                }
            }

            Calendar.prototype.currentMonth = function() {
                var clone = this.current.clone();
                while(clone.month() === this.current.month()) {
                    this.drawDay(clone);
                    clone.add('days', 1);
                }
            }

            Calendar.prototype.getWeek = function(day) {
                if(!this.week || day.day() === 0) {
                    this.week = createElement('div', 'week');
                    this.month.appendChild(this.week);
                }
            }

            Calendar.prototype.drawDay = function(day) {
                var self = this;
                this.getWeek(day);

                var outer = createElement('div', this.getDayClass(day));
                outer.addEventListener('click', function() { self.openDay(this); });

                var name = createElement('div', 'day-name', day.format('ddd'));
                var number = createElement('div', 'day-number', day.format('DD'));
                var events = createElement('div', 'day-events');
                this.drawEvents(day, events);

                outer.appendChild(name);
                outer.appendChild(number);
                outer.appendChild(events);
                this.week.appendChild(outer);
            }

            Calendar.prototype.drawEvents = function(day, element) {
                if(day.month() === this.current.month()) {
                    var todaysEvents = this.events.reduce(function(memo, ev) {
                        if(ev.date.isSame(day, 'day')) memo.push(ev);
                        return memo;
                    }, []);

                    // Dibuja puntos de color por cada item (D/A/C/General)
                    todaysEvents.forEach(function(ev) {
                        var evSpan = createElement('span', ev.color);
                        element.appendChild(evSpan);
                    });
                }
            }

            Calendar.prototype.getDayClass = function(day) {
                var classes = ['day'];
                if(day.month() !== this.current.month()) {
                    classes.push('other');
                } else if (today.isSame(day, 'day')) {
                    classes.push('today');
                }
                return classes.join(' ');
            }

            Calendar.prototype.openDay = function(el) {
                var details, arrow;
                var dayNumber = +el.querySelector('.day-number').textContent;
                var day = this.current.clone().date(dayNumber);
                var currentOpened = document.querySelector('.details');

                if(currentOpened && currentOpened.parentNode === el.parentNode) {
                    details = currentOpened;
                    arrow = document.querySelector('.arrow');
                } else {
                    if(currentOpened) {
                        currentOpened.className = 'details out';
                        currentOpened.addEventListener('animationend', function() {
                            currentOpened.parentNode.removeChild(currentOpened);
                        }, { once: true });
                    }
                    details = createElement('div', 'details in');
                    arrow = createElement('div', 'arrow');
                    details.appendChild(arrow);
                    el.parentNode.appendChild(details);
                }

                var todaysEvents = this.events.filter(ev => ev.date.isSame(day, 'day'));
                this.renderEvents(todaysEvents, details);

                arrow.style.left = (el.offsetLeft - el.parentNode.offsetLeft + 27) + 'px';
            }

            Calendar.prototype.renderEvents = function(events, ele) {
                var currentWrapper = ele.querySelector('.events');
                var wrapper = createElement('div', 'events in' + (currentWrapper ? ' new' : ''));

                events.forEach(function(ev) {
                    var div = createElement('div', 'event');
                    var square = createElement('div', 'event-category ' + ev.color);
                    var span = createElement('span', '', ev.calendar + (ev.eventName ? ' — ' + ev.eventName : ''));
                    div.appendChild(square);
                    div.appendChild(span);
                    wrapper.appendChild(div);
                });

                if(!events.length) {
                    var div = createElement('div', 'event empty');
                    var span = createElement('span', '', 'Sin eventos');
                    div.appendChild(span);
                    wrapper.appendChild(div);
                }

                if(currentWrapper) {
                    currentWrapper.className = 'events out';
                    currentWrapper.addEventListener('animationend', function() {
                        currentWrapper.parentNode.removeChild(currentWrapper);
                        ele.appendChild(wrapper);
                    }, { once: true });
                } else {
                    ele.appendChild(wrapper);
                }
            }

            Calendar.prototype.drawLegend = function() {
                // Fijamos la leyenda con los 4 posibles (coinciden con tu CSS)
                var legend = createElement('div', 'legend');
                ['Desayuno|blue','Almuerzo|orange','Cena|green','General|yellow']
                    .forEach(function(e) {
                        var parts = e.split('|');
                        var entry = createElement('span', 'entry ' +  parts[1], parts[0]);
                        legend.appendChild(entry);
                    });
                this.el.appendChild(legend);
            }

            Calendar.prototype.nextMonth = function() {
                this.current.add('months', 1);
                this.next = true;
                this.draw();
            }

            Calendar.prototype.prevMonth = function() {
                this.current.subtract('months', 1);
                this.next = false;
                this.draw();
            }

            window.Calendar = Calendar;

            function createElement(tagName, className, innerText) {
                var ele = document.createElement(tagName);
                if(className) ele.className = className;
                if(innerText) ele.textContent = innerText;
                return ele;
            }
        }();

        // Inicializa
        (function() {
            new Calendar('#calendar');
        })();
        // ======== fin codigo-adaptado.js ========
    </script>
</div>
