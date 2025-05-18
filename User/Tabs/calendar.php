<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../config.php';


$month = isset($_GET['month']) ? (int) $_GET['month'] : date('n');
$year  = isset($_GET['year'])  ? (int) $_GET['year']  : date('Y');

$prev_month = $month - 1; $prev_year = $year;
if ($prev_month < 1) { $prev_month = 12; $prev_year--; }
$next_month = $month + 1; $next_year = $year;
if ($next_month > 12) { $next_month = 1; $next_year++; }

$daysInMonth  = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$firstWeekday = (int) date('w', strtotime("$year-$month-01")); // Sunday=0…Saturday=6

$stmt = $conn->prepare("
  SELECT title, description, date
  FROM events
  WHERE MONTH(date)=? AND YEAR(date)=?
");
$stmt->bind_param("ii", $month, $year);
$stmt->execute();
$events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$eventsByDay = [];
foreach ($events as $e) {
    $d = (int) date('j', strtotime($e['date']));
    $eventsByDay[$d][] = $e;
}

function monthOptions($sel){
  for($m=1;$m<=12;$m++){
    $s = $m===$sel?' selected':'';
    echo "<option value=\"$m\"$s>" . date('F', strtotime("2000-$m-01")) . "</option>";
  }
}

function yearOptions($sel){
  for($y=2023;$y<=2027;$y++){
    $s = $y===$sel?' selected':'';
    echo "<option value=\"$y\"$s>$y</option>";
  }
}
?>
<style>

  .weekend-sun { background-color: #f8d7da; }
  .weekend-sat { background-color: #e2e3e5; }
  .calendar-wrapper { position: relative; }

</style>


<div class="d-flex justify-content-between align-items-center mb-2">
  <div class="d-flex">
    <select id="jumpMonth" class="form-select form-select-sm me-1"><?= monthOptions($month) ?></select>
    <select id="jumpYear"  class="form-select form-select-sm me-1"><?= yearOptions($year) ?></select>
    <button id="jumpBtn" class="btn btn-sm btn-outline-primary">Go</button>
  </div>
  <!-- <button id="addEventBtn" class="btn btn-sm btn-success" disabled data-bs-toggle="modal" data-bs-target="#addEventModal">
    + Add Event
  </button> --> 
  
</div>


<div class="d-flex justify-content-between align-items-center mb-3">
  <button class="btn btn-outline-secondary"
          onclick="loadCalendar(<?= $prev_month ?>, <?= $prev_year ?>)">
    &laquo; <?= date('F Y', strtotime("$prev_year-$prev_month-01")) ?>
  </button>

  <h2 class="mb-0"><?= date('F Y', strtotime("$year-$month-01")) ?></h2>

  <button class="btn btn-outline-secondary"
          onclick="loadCalendar(<?= $next_month ?>, <?= $next_year ?>)">
    <?= date('F Y', strtotime("$next_year-$next_month-01")) ?> &raquo;
  </button>
</div>

<div class="calendar-wrapper">
  <div class="calendar-spinner"></div>
  <div class="table-responsive">
    <table class="table table-bordered calendar-table">
      <thead class="table-light">
        <tr>
          <?php foreach (['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $wd): ?>
            <th class="text-center"><?= $wd ?></th>
          <?php endforeach; ?>
        </tr>
      </thead>
      <tbody>
        <?php
        $day  = 1; $cell = 0;
        echo '<tr>';

        for ($i = 0; $i < $firstWeekday; $i++, $cell++) {
          echo '<td></td>';
        }

        while ($day <= $daysInMonth) {
          $wd = ($firstWeekday + $day - 1) % 7;
          $cls = [];
          if ($wd === 0) $cls[] = 'weekend-sun';
          if ($wd === 6) $cls[] = 'weekend-sat';
          if ($year==date('Y')&&$month==date('n')&&$day==date('j')) $cls[]='today';
          $cls = $cls ? ' class="' . implode(' ', $cls) . '"' : '';
          echo "<td{$cls} data-day=\"{$day}\">";


            $cnt = count($eventsByDay[$day]??[]);
            $badge = $cnt ? "<span class=\"badge bg-info text-dark ms-1\">{$cnt}</span>" : '';
            echo "<div><strong>{$day}</strong>{$badge}</div>";


            if (!empty($eventsByDay[$day])) {
              foreach ($eventsByDay[$day] as $e) {
                $t = htmlspecialchars($e['title'], ENT_QUOTES);
                $d = htmlspecialchars($e['description'] ?? '', ENT_QUOTES);
                echo "<div class=\"event\" 
                           data-bs-toggle=\"modal\" 
                           data-bs-target=\"#eventModal\"
                           data-title=\"$t\" 
                           data-desc=\"$d\" 
                           title=\"$t\">$t</div>";
              }
            }
          echo '</td>';
          $day++; $cell++;
          if ($cell % 7 === 0) echo '</tr><tr>';
        }
        while ($cell % 7 !== 0) {
          echo '<td></td>'; $cell++;
        }
        echo '</tr>';
        ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Event Detail Modal -->
<div class="modal fade" id="eventModal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title">Event Detail</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
      <h6 id="modalEventTitle"></h6>
      <p id="modalEventDesc"></p>
    </div>
  </div></div>
</div>

<!-- Add Event Modal -->
<div class="modal fade" id="addEventModal" tabindex="-1">
  <div class="modal-dialog"><form id="addEventForm" class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title">Add Event</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
      <input type="hidden" name="date" id="newEventDate">
      <div class="mb-3">
        <label class="form-label">Title</label>
        <input name="title" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control"></textarea>
      </div>
    </div>
    <div class="modal-footer">
      <button type="submit" class="btn btn-primary">Save</button>
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
    </div>
  </form></div>
</div>

<script>
function loadCalendar(month, year) {
  const wrap = $('.calendar-wrapper'), spin = wrap.find('.calendar-spinner');
  spin.show();
  $('#main-content')
    .load('Tabs/calendar.php?month=' + month + '&year=' + year, function(){
      spin.hide();
    });
}

// month/year jump
$('#jumpBtn').on('click', () => {
  loadCalendar(+$('#jumpMonth').val(), +$('#jumpYear').val());
});

// day click → enable Add Event, store date
$('.calendar-table').on('click', 'td[data-day]', function(){
  $('.calendar-table td.selected').removeClass('selected'); // Remove previous
  $(this).addClass('selected'); // Add highlight
  const d = $(this).data('day');
  const formatted = `<?= $year ?>-<?= str_pad($month,2,'0',STR_PAD_LEFT) ?>-${String(d).padStart(2, '0')}`;
  $('#newEventDate').val(formatted);
  $('#addEventBtn').prop('disabled', false);
});


// submit new event (stub—replace URL & handling as needed)
$('#addEventForm').on('submit', function(e){
  e.preventDefault();
  $.post('Tabs/calendar_add_event.php', $(this).serialize(), () => {
    $('#addEventModal').modal('hide');
    loadCalendar(<?= $month ?>, <?= $year ?>);
  });
});

// populate event detail modal
$('#eventModal').on('show.bs.modal', function(e) {
  const btn = $(e.relatedTarget);
  $('#modalEventTitle').text(btn.data('title'));
  $('#modalEventDesc').text(btn.data('desc') || 'No description available.');
});
</script>

<style>
/* Calendar Table Styling */
.calendar-table td {
  height: 100px;
  min-height: 100px;
  width: 14.28%; /* Roughly 100% / 7 days */
  vertical-align: top;
  padding: 6px;
  cursor: pointer;
  transition: background-color 0.2s ease;
  position: relative;
}
.calendar-table td[data-day]:hover {
  box-shadow: inset 0 0 0 2px #0d6efd;
}
.calendar-table td:hover {
  background-color: #f0f8ff;
}

.calendar-table td.today {
  border: 2px solid #0d6efd;
  background-color: #e9f5ff;
}
.calendar-table td.selected {
  background-color: #cfe2ff;
  border: 2px solid #0d6efd;
}
.calendar-table td {
  transition: background-color 0.2s ease, border 0.2s ease;
}


.calendar-table .event {
  margin: 4px 0;
  padding: 2px 6px;
  background-color: #d1e7dd;
  border-radius: 4px;
  font-size: 0.875em;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  cursor: pointer;
  transition: all 0.2s ease;
}

.calendar-table .event:hover {
  background-color: #a7dbc5;
}

.badge {
  font-size: 0.7rem;
  vertical-align: top;
}

/* Highlight Selected Date */
.calendar-table td.selected {
  outline: 3px solid #198754;
}

/* Spinner */
.calendar-spinner {
  display: none;
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(255, 255, 255, 0.8) url('../../assets/spinner.gif') center center no-repeat;
  z-index: 10;
}

/* Modal Form */
#addEventModal textarea,
#addEventModal input {
  font-size: 0.95rem;
}

/* Disabled Add Button */
#addEventBtn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.day-header {
  margin-bottom: 4px;
}

.event-container {
  max-height: 60px;
  overflow-y: auto;
}
</style>
