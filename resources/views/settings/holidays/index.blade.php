@extends('layouts.app')

@section('title', 'Holiday Marking')
@section('page-title', 'Holiday Marking')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('settings.index') }}">Settings</a></li>
    <li class="breadcrumb-item active">Holiday Marking</li>
@endsection

@push('styles')
<style>
    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        overflow: hidden;
        background: var(--card-bg);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    }
    .calendar-header-cell {
        background: rgba(99, 102, 241, 0.08);
        color: var(--primary);
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.05em;
        text-align: center;
        padding: 12px 6px;
        border-bottom: 2px solid rgba(99, 102, 241, 0.2);
    }
    .calendar-day-cell {
        min-height: 100px;
        padding: 8px;
        border-right: 1px solid var(--border-color);
        border-bottom: 1px solid var(--border-color);
        position: relative;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .calendar-day-cell:nth-child(7n) {
        border-right: none;
    }
    .calendar-day-cell:hover {
        background: rgba(99, 102, 241, 0.03);
        transform: scale(1.02);
        z-index: 10;
        box-shadow: 0 8px 30px rgba(99, 102, 241, 0.1);
    }
    .calendar-day-cell.weekly-off {
        background: rgba(239, 68, 68, 0.03);
    }
    .calendar-day-cell.weekly-off .day-number {
        color: var(--danger);
    }
    .calendar-day-cell.other-month {
        background: var(--body-bg);
        opacity: 0.4;
        cursor: not-allowed;
        pointer-events: none;
    }
    .day-number {
        font-weight: 700;
        font-size: 0.95rem;
        color: var(--text-primary);
    }
    .weekly-off-badge {
        font-size: 0.7rem;
        padding: 1px 4px;
        border-radius: 4px;
        background: rgba(239, 68, 68, 0.1);
        color: var(--danger);
        font-weight: 600;
        align-self: flex-start;
    }
    .holiday-badge {
        font-size: 0.75rem;
        padding: 4px 6px;
        border-radius: 6px;
        font-weight: 600;
        margin-top: 4px;
        white-space: normal;
        word-wrap: break-word;
        text-align: left;
        line-height: 1.2;
    }
    .holiday-national {
        background-color: rgba(16, 185, 129, 0.12);
        color: #065f46;
        border-left: 3px solid #10b981;
    }
    .holiday-company {
        background-color: rgba(99, 102, 241, 0.12);
        color: #3730a3;
        border-left: 3px solid #6366f1;
    }
    .holiday-optional {
        background-color: rgba(245, 158, 11, 0.12);
        color: #92400e;
        border-left: 3px solid #f59e0b;
    }
    .calendar-nav-btn {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        color: var(--text-secondary);
        transition: all 0.2s ease;
    }
    .calendar-nav-btn:hover {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }
    .calendar-legend-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
    }
</style>
@endpush

@section('content')
<div class="row g-4">
    <!-- Left Navigation Sidebar for Settings -->
    <div class="col-12 col-md-3">
        <div class="card">
            @include('settings.sidebar')
        </div>
    </div>

    <!-- Right Calendar Content -->
    <div class="col-12 col-md-9">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                <div class="d-flex align-items-center gap-3">
                    <button class="calendar-nav-btn" onclick="navigateMonth(-1)"><i class="bi bi-chevron-left"></i></button>
                    <h4 class="mb-0 fw-bold text-gradient" id="calendar-title" style="min-width: 150px; text-align: center;"></h4>
                    <button class="calendar-nav-btn" onclick="navigateMonth(1)"><i class="bi bi-chevron-right"></i></button>
                    <button class="btn btn-outline-primary btn-sm px-3" style="border-radius: 8px;" onclick="goToToday()">Today</button>
                </div>
                <div class="d-flex flex-wrap gap-3 align-items-center justify-content-end">
                    <div class="d-flex align-items-center gap-1 fs-7">
                        <span class="calendar-legend-dot bg-success"></span> <span class="text-muted">National</span>
                    </div>
                    <div class="d-flex align-items-center gap-1 fs-7">
                        <span class="calendar-legend-dot bg-primary"></span> <span class="text-muted">Company</span>
                    </div>
                    <div class="d-flex align-items-center gap-1 fs-7">
                        <span class="calendar-legend-dot bg-warning"></span> <span class="text-muted">Optional</span>
                    </div>
                </div>
            </div>

            <div class="card-body p-3">
                <div class="calendar-grid" id="calendar-grid">
                    <!-- Loaded dynamically via JS -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mark/Manage Holiday Modal -->
<div class="modal fade" id="holidayModal" tabindex="-1" aria-labelledby="holidayModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <form id="holidayForm" method="POST" action="{{ route('settings.holidays.store') }}">
                @csrf
                <div class="modal-header border-0 bg-light p-3 px-4">
                    <h5 class="modal-title fw-bold text-gradient" id="holidayModalLabel">Mark Holiday</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="date" id="holidayDateInput">
                    
                    <!-- Form Selected Date Label -->
                    <div class="mb-4">
                        <span class="text-muted text-uppercase tracking-wider fs-7 fw-bold">Date Selected</span>
                        <div class="fs-4 fw-extrabold text-primary" id="selectedDateLabel"></div>
                    </div>

                    <!-- Existing Holiday Info -->
                    <div id="holidayDetailsSection" class="d-none mb-3">
                        <div class="p-3 border border-danger-subtle rounded-3 bg-danger-subtle bg-opacity-10 d-flex align-items-center justify-content-between gap-3">
                            <div>
                                <span class="text-muted fs-7">Existing Holiday</span>
                                <h6 class="fw-bold mb-1" id="existingHolidayName"></h6>
                                <span class="badge text-uppercase" id="existingHolidayTypeBadge"></span>
                            </div>
                            <button type="button" class="btn btn-danger btn-sm px-3" id="btnDeleteHoliday" style="border-radius: 8px;">
                                <i class="bi bi-trash me-1"></i> Delete
                            </button>
                        </div>
                    </div>

                    <!-- New/Edit Holiday Fields -->
                    <div id="holidayFormFields">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary">Holiday Title</label>
                            <input type="text" name="name" id="holidayNameInput" class="form-control form-control-lg border-2" placeholder="e.g. Independence Day" style="border-radius: 10px;" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary">Type of Holiday</label>
                            <select name="type" id="holidayTypeInput" class="form-select form-select-lg border-2" style="border-radius: 10px;">
                                <option value="national">National Holiday</option>
                                <option value="company">Company Holiday</option>
                                <option value="optional">Optional Holiday</option>
                            </select>
                        </div>
                        <div class="form-check mt-3 mb-2" id="recurrenceCheckboxContainer">
                            <input class="form-check-input" type="checkbox" name="repeat_yearly_nth_day" value="1" id="repeatYearlyNthDay">
                            <label class="form-check-label fw-semibold text-secondary" for="repeatYearlyNthDay" id="recurrenceLabel">
                                Repeat for all months of the year
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 px-4 bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal" style="border-radius: 8px;">Close</button>
                    <button type="submit" class="btn btn-primary px-4" id="btnSaveHoliday" style="border-radius: 8px;">Save Holiday</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Hidden Delete Form -->
<form id="deleteHolidayForm" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
    // Config retrieved from PHP
    const weekStartDay = @json($weekStartDay);
    const weekOffDays = @json($weekOffDays);
    const holidays = @json($holidays);

    // Track active month
    let currentDate = new Date();
    let currentMonth = currentDate.getMonth();
    let currentYear = currentDate.getFullYear();

    const monthNames = [
        "January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"
    ];

    const dayKeys = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
    const dayLabelsMap = {
        'sun': 'Sun',
        'mon': 'Mon',
        'tue': 'Tue',
        'wed': 'Wed',
        'thu': 'Thu',
        'fri': 'Fri',
        'sat': 'Sat'
    };

    // Calculate ordered days of week headers
    let startDayIndex = dayKeys.indexOf(weekStartDay);
    if (startDayIndex === -1) startDayIndex = 0;
    
    const orderedDayKeys = [];
    for (let i = 0; i < 7; i++) {
        orderedDayKeys.push(dayKeys[(startDayIndex + i) % 7]);
    }

    // Modal elements
    const holidayModal = new bootstrap.Modal(document.getElementById('holidayModal'));
    const selectedDateLabel = document.getElementById('selectedDateLabel');
    const holidayDateInput = document.getElementById('holidayDateInput');
    const holidayDetailsSection = document.getElementById('holidayDetailsSection');
    const existingHolidayName = document.getElementById('existingHolidayName');
    const existingHolidayTypeBadge = document.getElementById('existingHolidayTypeBadge');
    const holidayFormFields = document.getElementById('holidayFormFields');
    const holidayNameInput = document.getElementById('holidayNameInput');
    const holidayTypeInput = document.getElementById('holidayTypeInput');
    const btnSaveHoliday = document.getElementById('btnSaveHoliday');
    const btnDeleteHoliday = document.getElementById('btnDeleteHoliday');
    const deleteHolidayForm = document.getElementById('deleteHolidayForm');

    function renderCalendar() {
        const grid = document.getElementById('calendar-grid');
        const title = document.getElementById('calendar-title');
        
        // Update Title
        title.innerText = `${monthNames[currentMonth]} ${currentYear}`;
        grid.innerHTML = '';

        // Render Day Headers
        orderedDayKeys.forEach(day => {
            const cell = document.createElement('div');
            cell.className = 'calendar-header-cell';
            cell.innerText = dayLabelsMap[day];
            grid.appendChild(cell);
        });

        // Find date parameters
        const firstDayOfMonth = new Date(currentYear, currentMonth, 1);
        const lastDayOfMonth = new Date(currentYear, currentMonth + 1, 0);
        const daysInMonth = lastDayOfMonth.getDate();
        
        // Day of week of the first day (0 = Sun, 1 = Mon...)
        const startDayOfWeek = firstDayOfMonth.getDay();
        
        // Calculate offset (empty cells needed before 1st of month)
        const emptyOffsetCells = (startDayOfWeek - startDayIndex + 7) % 7;

        // Render Empty cells for offset
        for (let i = 0; i < emptyOffsetCells; i++) {
            const cell = document.createElement('div');
            cell.className = 'calendar-day-cell other-month';
            grid.appendChild(cell);
        }

        // Render days of the month
        for (let day = 1; day <= daysInMonth; day++) {
            const cell = document.createElement('div');
            const dateStr = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            
            // Check day of week for weekly off check
            const targetDate = new Date(currentYear, currentMonth, day);
            const targetDayOfWeek = targetDate.getDay();
            const dayKey = dayKeys[targetDayOfWeek];
            
            const isWeeklyOff = weekOffDays.includes(dayKey);

            cell.className = 'calendar-day-cell';
            if (isWeeklyOff) {
                cell.classList.add('weekly-off');
            }

            // Day number container
            const headerContainer = document.createElement('div');
            headerContainer.className = 'd-flex justify-content-between align-items-center w-100';
            
            const dayNumSpan = document.createElement('span');
            dayNumSpan.className = 'day-number';
            dayNumSpan.innerText = day;
            headerContainer.appendChild(dayNumSpan);

            if (isWeeklyOff) {
                const offBadge = document.createElement('span');
                offBadge.className = 'weekly-off-badge';
                offBadge.innerText = 'Off';
                headerContainer.appendChild(offBadge);
            }

            cell.appendChild(headerContainer);

            // Check if there is an existing holiday on this date
            const foundHoliday = holidays.find(h => {
                // handles date comparison (DB date can be string Y-m-d)
                const hDate = new Date(h.date);
                return hDate.getFullYear() === currentYear && 
                       hDate.getMonth() === currentMonth && 
                       hDate.getDate() === day;
            });

            if (foundHoliday) {
                const badge = document.createElement('div');
                badge.className = `holiday-badge holiday-${foundHoliday.type}`;
                badge.innerHTML = `<i class="bi bi-pin-angle-fill me-1"></i> ${foundHoliday.name}`;
                cell.appendChild(badge);
            }

            // Click action
            cell.addEventListener('click', () => {
                openHolidayModal(dateStr, targetDate, foundHoliday);
            });

            grid.appendChild(cell);
        }
    }

    function openHolidayModal(dateStr, jsDate, holidayObj) {
        holidayDateInput.value = dateStr;
        
        // Format readable date label
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        selectedDateLabel.innerText = jsDate.toLocaleDateString('en-US', options);

        if (holidayObj) {
            // Holiday exists: Mode detail/delete
            document.getElementById('holidayModalLabel').innerText = 'Manage Holiday';
            holidayDetailsSection.classList.remove('d-none');
            existingHolidayName.innerText = holidayObj.name;
            
            // Set type badge color
            existingHolidayTypeBadge.innerText = holidayObj.type + ' Holiday';
            existingHolidayTypeBadge.className = 'badge';
            if (holidayObj.type === 'national') existingHolidayTypeBadge.classList.add('bg-success');
            else if (holidayObj.type === 'company') existingHolidayTypeBadge.classList.add('bg-primary');
            else existingHolidayTypeBadge.classList.add('bg-warning', 'text-dark');
            
            // Hide creation inputs, hide save button
            holidayFormFields.classList.add('d-none');
            btnSaveHoliday.classList.add('d-none');
            
            // Setup delete action
            btnDeleteHoliday.onclick = () => {
                if (confirm(`Are you sure you want to remove the holiday "${holidayObj.name}"?`)) {
                    deleteHolidayForm.action = `/settings/holidays/${holidayObj.id}`;
                    deleteHolidayForm.submit();
                }
            };
        } else {
            // Holiday does not exist: Mode create
            document.getElementById('holidayModalLabel').innerText = 'Mark Holiday';
            holidayDetailsSection.classList.add('d-none');
            
            // Show fields and save button
            holidayFormFields.classList.remove('d-none');
            btnSaveHoliday.classList.remove('d-none');
            holidayNameInput.value = '';
            holidayTypeInput.value = 'national';

            // Show recurrence option on create and customize the label
            const recurrenceContainer = document.getElementById('recurrenceCheckboxContainer');
            const recurrenceLabel = document.getElementById('recurrenceLabel');
            
            // Calculate nth weekday name (e.g., 2nd Saturday)
            const dayNum = jsDate.getDate();
            const nth = Math.ceil(dayNum / 7);
            const nthWords = ["first", "second", "third", "fourth", "fifth"];
            const nthWord = nthWords[nth - 1] || `${nth}th`;
            const weekdayName = jsDate.toLocaleDateString('en-US', { weekday: 'long' });
            
            recurrenceLabel.innerText = `Repeat every ${nthWord} ${weekdayName} of the year ${jsDate.getFullYear()}`;
            recurrenceContainer.classList.remove('d-none');
            document.getElementById('repeatYearlyNthDay').checked = false;
        }

        holidayModal.show();
    }

    function navigateMonth(direction) {
        currentMonth += direction;
        if (currentMonth < 0) {
            currentMonth = 11;
            currentYear -= 1;
        } else if (currentMonth > 11) {
            currentMonth = 0;
            currentYear += 1;
        }
        renderCalendar();
    }

    function goToToday() {
        currentDate = new Date();
        currentMonth = currentDate.getMonth();
        currentYear = currentDate.getFullYear();
        renderCalendar();
    }

    // Initialize calendar on page load
    document.addEventListener('DOMContentLoaded', () => {
        renderCalendar();
    });
</script>
@endpush
