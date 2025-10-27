import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';

document.addEventListener('DOMContentLoaded', function () {
  const calendarEl = document.getElementById('calendar');
  if (!calendarEl) {
    return;
  }

  const calendar = new Calendar(calendarEl, {
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
    initialDate: new Date(),
    initialView: 'timeGridWeek',
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,timeGridWeek,timeGridDay'
    }
  // });
  ,
events: async function (info, successCallback, failureCallback) {
      try {
        const params = new URLSearchParams();
        const filter = calendarEl.dataset.filter || 'created';
        params.append('filter', filter);

        if (calendarEl.dataset.authorId) {
          params.append('author', calendarEl.dataset.authorId);
        }

        const
         response = await fetch(`/post/calendar/events?${params.toString()}`);

        if (!response.ok) {
          throw new Error('Failed to load events');
        }

        const events = await response.json();

        successCallback(events.map(event => ({
          ...event,
          backgroundColor: event.backgroundColor || (event.extendedProps?.isUpdated ? '#f0ad4e' : '#198754'),
          borderColor: event.borderColor || (event.extendedProps?.isUpdated ? '#f0ad4e' : '#198754')
        })));
      } catch (error) {
        console.error('Calendar events error:', error);
        failureCallback(error);
      }
    },
    eventClick(info) {
      if (!info.event.url) {
        info.jsEvent.preventDefault();
        return;
      }

      info.jsEvent.preventDefault();
      window.location.href = info.event.url;
    },
    eventDidMount: function (info) {
      const bg = info.event.backgroundColor || (info.event.extendedProps?.isUpdated ? '#f0ad4e' : '#198754');
      const border = info.event.borderColor || bg;

      info.el.style.backgroundColor = bg;
      info.el.style.borderColor = border;
      info.el.style.color = '#fff';
      info.el.style.borderRadius = '4px';
    }
  });
  calendar.render();
});
// $("#calendar").fullCalendar({
//     defaultDate: moment(),
//     defaultView: 'agendaWeek',
//     header: {
//         left: 'prev,next today',
//         center: 'title',
//         right: 'month,agendaWeek,agendaDay'
//     },
//     viewRender: function (view, element) {
//         //The title isn't rendered until after this callback, so we need to use a timeout.
//         window.setTimeout(function(){
//             $("#calendar").find('.fc-toolbar > div > h2').empty().append(
//                 "<div>"+view.start.format('MMM Do [to]')+"</div>"+
//                 "<div>"+view.end.format('MMM Do')+"</div>"
//             );
//         },0);
//     },
// });