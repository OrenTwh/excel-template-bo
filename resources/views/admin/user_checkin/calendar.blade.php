<div class="card">
    <div class="card-inner">
        <div class="row">
            <div class="col-md-12 col-lg-12">
                <h5 class="card-title mb-4">{{ __( 'template.calendar' ) }}</h5>
                <div id="calendar" class="mb-3">
                    <!-- Calendar will render here -->
                </div>
                <section id="checkin-details" class="hidden">
                    <h6>{{ __( 'checkin_reward.checkin_details' ) }} <span id="checkin_date"></span></h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>{{ __( 'checkin_reward.username' ) }}</th>
                                    <th>{{ __( 'checkin_reward.phone_number' ) }}</th>
                                    <th>{{ __( 'checkin_reward.total_checkin' ) }}</th>
                                </tr>
                            </thead>
                            <tbody id="checkin-details-body">
                                <!-- Static content will be injected here -->
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.4/main.min.css" rel="stylesheet">
<script src="
https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js
"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');

    // Initialize FullCalendar
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        selectable: true,
        events: [],
        dateClick: function (info) {
            const dateKey = info.dateStr; // e.g., '2025-01-10'
            const checkins = sampleCheckins[dateKey] || []; // Get check-ins for the selected date
            displayCheckinDetails(checkins); // Pass the relevant check-ins to the display function
        },
        datesSet: function () {
            $.ajax({
                url: '{{ route("admin.user_checkin.allUserCheckinCalendars") }}',
                type: 'POST',
                data: {
                    '_token': '{{ csrf_token() }}',
                },
                success: function (response) {
                    const groupedData = response.data;

                    // Convert grouped data to events
                    const events = Object.keys(groupedData).map(date => ({
                        title: `${groupedData[date].length} Check-ins`,
                        start: date,
                        extendedProps: { checkins: groupedData[date] }, // Attach check-ins as custom data
                    }));
                    sampleCheckins = response.data
                    calendar.removeAllEvents(); // Clear existing events
                    calendar.addEventSource(events); // Add new events
                },
                error: function () {
                    console.error('Failed to fetch calendar data');
                },
            });
        },
    });

    // Static check-in data for selected dates
    let sampleCheckins = {};

    // Render the calendar
    calendar.render();

    // Function to display check-in details
    function displayCheckinDetails(checkins) {
        const checkinDetailsSection = document.getElementById('checkin-details');
        const checkinDetailsBody = document.getElementById('checkin-details-body');

        // Clear previous content
        checkinDetailsBody.innerHTML = '';

        if (checkins && checkins.length > 0) {
            checkins.forEach(checkin => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${checkin.username}</td>
                    <td>${checkin.phone_number}</td>
                    <td>${checkin.total_checkin}</td>
                `;
                checkinDetailsBody.appendChild(row);
            });
            checkinDetailsSection.classList.remove('hidden');
        } else {
            checkinDetailsBody.innerHTML = `<tr><td colspan="3">No data available</td></tr>`;
            checkinDetailsSection.classList.remove('hidden');
        }
    }
});
</script>

