<?php

error_reporting(E_ALL);
ini_set('display_errors', 0);

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

set_exception_handler(function ($e) {
  error_log("Uncaught exception: " . $e->getMessage());
  header('Content-Type: application/json');
  echo json_encode(['status' => 'error', 'message' => 'Server error.']);
  exit;
});

require_once '../../config.php';
require_once '../Models/scheduleModel.php';

function json_response(array $data)
{
  header('Content-Type: application/json');
  echo json_encode($data);
  exit;
}

if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'get_schedules') {
  if (empty($_SESSION['user_id'])) {
    json_response([]);
  }
  $stmt = $conn->prepare("SELECT section, day, time, status FROM schedules");
  $stmt->execute();
  $schedules = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  json_response($schedules);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (empty($_SESSION['user_id'])) {
    json_response(['status' => 'error', 'message' => 'Not logged in.']);
  }
  if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    json_response(['status' => 'error', 'message' => 'Invalid CSRF token.']);
  }

  $user_id = $_SESSION['user_id'];
  $grade = trim($_POST['grade'] ?? '');
  $section = trim($_POST['section'] ?? '');
  $subject = trim($_POST['subject'] ?? '');
  $day = trim($_POST['day'] ?? '');
  $time = trim($_POST['time'] ?? '');

  if (in_array('', [$grade, $section, $subject, $day, $time], true)) {
    json_response(['status' => 'error', 'message' => 'All fields are required.']);
  }

  $chk = $conn->prepare("SELECT 1 FROM schedules WHERE section=? AND day=? AND time=? AND status IN ('Pending','Approved')");
  $chk->bind_param('sss', $section, $day, $time);
  $chk->execute();
  $chk->store_result();
  if ($chk->num_rows > 0) {
    json_response(['status' => 'error', 'message' => 'This schedule conflicts with an existing Pending/Approved entry.']);
  }

  $model = new ScheduleModel($conn);
  if ($model->addSchedule($user_id, $grade, $subject, $section, $day, $time)) {
    json_response(['status' => 'success', 'message' => 'Schedule added.']);
  } else {
    error_log("Schedule insert failed: " . $conn->error);
    json_response(['status' => 'error', 'message' => 'Database error.']);
  }
}

$csrf_token = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Add New Schedule</title>

</head>

<body>
  <div class="whole-container">
    <div class="left">
      <div class="form-container">
        <h2>Add New Schedule</h2>
        <form id="schedule-form">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES) ?>">
          <label for="grade">Grade</label>
          <select id="grade" name="grade" required>
            <option value="">Select Grade</option>
            <?php for ($g = 1; $g <= 6; $g++): ?>
              <option value="<?= $g ?>">Grade <?= $g ?></option>
            <?php endfor; ?>
          </select>
          <label for="section">Section</label>
          <input type="text" id="section" name="section" readonly placeholder="Select Section" required>
          <label for="subject">Subject</label>
          <input type="text" id="subject" name="subject" readonly placeholder="Select Subject" required>
          <label for="day">Day</label>
          <input type="text" id="day" name="day" readonly placeholder="Select Day" required>
          <label for="time">Time</label>
          <input type="text" id="time" name="time" readonly placeholder="Select Time" required>
          <button type="submit" class="btn-submit">Add Schedule</button>
          <div id="error-message"></div>
        </form>
      </div>
    </div>
    <div class="right">
      <h3>Select an Option</h3>
      <div id="options-container">
        <p>Click a field on the left to begin.</p>
      </div>
    </div>
  </div>

  <script>
    (function () {
      const gradeSections = { 1: ['A', 'B', 'C'], 2: ['A', 'B', 'C'], 3: ['A', 'B', 'C'], 4: ['A', 'B', 'C'], 5: ['A', 'B', 'C'], 6: ['A', 'B', 'C'] };
      const choices = {
        subject: ['Araling Panlipunan', 'Computer', 'English', 'Filipino', 'GMRC', 'Math', 'MAPEH', 'Science', 'TLE', 'Mother Tongue'],
        day: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
        time: ['7:00 am - 8:00 am', '8:00 am - 9:00 am', '9:00 am - 10:00 am', '10:00 am - 11:00 am', '11:00 am - 12:00 pm', '12:00 pm - 1:00 pm', '1:00 pm - 2:00 pm', '2:00 pm - 3:00 pm', '3:00 pm - 4:00 pm', '4:00 pm - 5:00 pm', '5:00 pm - 6:00 pm']
      };
      const fields = ['grade', 'section', 'subject', 'day', 'time'];
      let schedules = [], selected = { grade: '', section: '', subject: '', day: '', time: '' };

      // 1) Load existing schedules (explicit URL)
      fetch('Tabs/add_schedule.php?action=get_schedules')
        .then(r => {
          if (!r.ok) throw new Error(r.statusText);
          return r.json();
        })
        .then(data => {
          if (Array.isArray(data)) schedules = data;
          refreshOptions();
        })
        .catch(e => console.error('Load error:', e));

      // 2) Show options for a field
      function showOptions(f) {
        let list = f === 'section'
          ? (selected.grade ? gradeSections[selected.grade].map(l => selected.grade + ' - ' + l) : [])
          : (f === 'grade' ? [] : choices[f] || []);

        const html = list.map(v => {
          const disabled = schedules.some(s =>
            (s.status === 'Approved' || s.status === 'Pending') &&
            s.section === (f === 'section' ? v : selected.section) &&
            s.day === (f === 'day' ? v : selected.day) &&
            s.time === (f === 'time' ? v : selected.time)
          );
          return `<div class="option-item${disabled ? ' disabled' : ''}${selected[f] === v ? ' selected' : ''}"
                   data-field="${f}" data-val="${v}">${v}</div>`;
        }).join('');
        document.getElementById('options-container').innerHTML = `<div class="options-grid">${html}</div>`;
      }

      function refreshOptions() {
        fields.filter(f => !selected[f]).forEach(showOptions);
      }

      // 3) Event listeners for grade change and option clicks
      document.getElementById('grade').addEventListener('change', e => {
        selected.grade = e.target.value;
        ['section', 'subject', 'day', 'time'].forEach(k => {
          selected[k] = '';
          document.getElementById(k).value = '';
        });
        refreshOptions();
      });
      fields.slice(1).forEach(f =>
        document.getElementById(f).addEventListener('click', () => showOptions(f))
      );
      document.addEventListener('click', e => {
        const tgt = e.target.closest('.option-item');
        if (!tgt || tgt.classList.contains('disabled')) return;
        const f = tgt.dataset.field, v = tgt.dataset.val;
        selected[f] = v;
        document.getElementById(f).value = v;
        document.querySelectorAll(`.option-item[data-field="${f}"]`)
          .forEach(el => el.classList.remove('selected'));
        tgt.classList.add('selected');
        refreshOptions();
      });

      // 4) Form submit with proper fetch chaining
      document.getElementById('schedule-form').addEventListener('submit', e => {
        e.preventDefault();
        const btn = e.target.querySelector('.btn-submit');
        btn.disabled = true;
        btn.textContent = 'Adding...';
        document.getElementById('error-message').textContent = '';

        const data = new URLSearchParams();
        data.set('csrf_token', document.querySelector('[name=csrf_token]').value);
        fields.forEach(f => data.set(f, selected[f]));

        // POST to add_schedule.php
        fetch('Tabs/add_schedule.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: data
        })
          .then(r => {
            if (!r.ok) throw new Error(r.status);
            return r.json();
          })
          .then(res => {
            if (res.status === 'success') {
              alert(res.message);
              btn.textContent = 'Add Schedule';
              btn.disabled = false;
              document.getElementById('schedule-form').reset();
              selected = { grade: '', section: '', subject: '', day: '', time: '' };

              return fetch('Tabs/add_schedule.php?action=get_schedules');
            } else {

              document.getElementById('error-message').textContent = res.message;
              btn.textContent = 'Add Schedule';
              btn.disabled = false;

              throw new Error('Handled error');
            }
          })
          .then(r => r.json())
          .then(data => {
            schedules = data;
            refreshOptions();
          })
          .catch(err => {
            if (err.message !== 'Handled error') {
              console.error('Submit error:', err);
              document.getElementById('error-message').textContent = 'An unexpected error occurred.';
              btn.textContent = 'Add Schedule';
              btn.disabled = false;
            }
          });
      });

    })();
  </script>

</body>

</html>


<style>


  .whole-container {
    display: flex;
    gap: 20px;
    padding: 20px;
    min-height: 100vh;
  }

  .left,
  .right {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    padding: 20px;
  }

  .left {
    width: 30%;
  }

  .right {
    width: 70%;
  }

  .form-container {
    text-align: center;
  }

  form {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  label {
    font-weight: bold;
    text-align: left;
  }

  input,
  select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    transition: border-color 0.3s, box-shadow 0.3s;
  }

  input:focus,
  select:focus {
    border-color: #1ABC9C;
    box-shadow: 0 0 5px rgba(26, 188, 156, 0.5);
    outline: none;
  }

  .btn-submit {
    padding: 12px;
    background-color: #1ABC9C;
    color: #fff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
    transition: background-color 0.3s;
  }

  .btn-submit:disabled {
    background-color: #ccc;
    cursor: default;
  }

  .btn-submit:hover:enabled {
    background-color: #16A085;
  }

  #error-message {
    color: red;
    font-weight: bold;
    min-height: 1.2em;
  }

  .options-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
  }

  .option-item {
    flex: 1 1 calc(33% - 10px);
    padding: 10px;
    background: #f4f4f4;
    border-radius: 10px;
    text-align: center;
    cursor: pointer;
    transition: background-color 0.3s, color 0.3s;
  }

  .option-item:hover {
    background: #d1e7dd;
  }

  .option-item.disabled {
    background: #ccc;
    color: #888;
    pointer-events: none;
  }

  .option-item.selected {
    background: #1ABC9C;
    color: #fff;
  }


  @media (max-width: 768px) {
    .whole-container {
      flex-direction: column;
    }

    .left,
    .right {
      width: 100%;
    }
  }
</style>