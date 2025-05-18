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

// Configuration
const SHIFT_CAPACITIES = [
    'Opening (8 am - 5 pm)' => 7,
    'Mid (10 am - 7 pm)' => 6,
    'Closing (12 pm - 9 pm)' => 7
];
const MAX_SHIFTS_PER_DAY = 1; // Change to 2-3 if allowing multiple shifts
const SHIFT_OVERLAPS = [
    'Opening (8 am - 5 pm)' => ['Mid (10 am - 7 pm)'],
    'Mid (10 am - 7 pm)' => ['Opening (8 am - 5 pm)', 'Closing (12 pm - 9 pm)']
];

function json_response(array $data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// CSRF Protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Get Schedules
if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'get_schedules') {
    if (empty($_SESSION['user_id'])) {
        json_response([]);
    }
    $stmt = $conn->prepare("SELECT shift, day, status FROM schedules");
    $stmt->execute();
    json_response($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
}

// Add Schedule
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Authentication Check
    if (empty($_SESSION['user_id'])) {
        json_response(['status' => 'error', 'message' => 'Not logged in.']);
    }

    // CSRF Validation
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        json_response(['status' => 'error', 'message' => 'Invalid CSRF token.']);
    }

    // Input Validation
    $user_id = $_SESSION['user_id'];
    $shift = trim($_POST['shift'] ?? '');
    $day = trim($_POST['day'] ?? '');

    if (empty($shift) || empty($day)) {
        json_response(['status' => 'error', 'message' => 'All fields are required.']);
    }

    // Shift Existence Check
    if (!isset(SHIFT_CAPACITIES[$shift])) {
        json_response(['status' => 'error', 'message' => 'Invalid shift selected.']);
    }

    // 1. Check for duplicate request
    $userCheck = $conn->prepare("SELECT 1 FROM schedules WHERE user_id = ? AND shift = ? AND day = ?");
    $userCheck->bind_param('iss', $user_id, $shift, $day);
    $userCheck->execute();
    if ($userCheck->get_result()->num_rows > 0) {
        json_response(['status' => 'error', 'message' => 'You already requested this shift.']);
    }

    // 2. Check shift capacity
    $capacityCheck = $conn->prepare("SELECT COUNT(*) as count FROM schedules WHERE shift = ? AND day = ? AND status = 'Approved'");
    $capacityCheck->bind_param('ss', $shift, $day);
    $capacityCheck->execute();
    $approvedCount = $capacityCheck->get_result()->fetch_assoc()['count'];
    
    if ($approvedCount >= SHIFT_CAPACITIES[$shift]) {
        json_response([
            'status' => 'error',
            'message' => "Shift full. {$approvedCount}/".SHIFT_CAPACITIES[$shift]." slots taken"
        ]);
    }

    // 3. Check daily limit
    $workloadCheck = $conn->prepare("
        SELECT COUNT(*) as count, GROUP_CONCAT(shift) as shifts 
        FROM schedules 
        WHERE user_id = ? AND day = ? AND status = 'Approved'
    ");
    $workloadCheck->bind_param('is', $user_id, $day);
    $workloadCheck->execute();
    $result = $workloadCheck->get_result()->fetch_assoc();
    
    if ($result['count'] >= MAX_SHIFTS_PER_DAY) {
        json_response([
            'status' => 'error',
            'message' => "Max ".MAX_SHIFTS_PER_DAY." shift(s) per day. You have: {$result['shifts']}"
        ]);
    }

    // 4. Check shift overlaps
    if (isset(SHIFT_OVERLAPS[$shift])) {
        $overlapCheck = $conn->prepare("
            SELECT 1 FROM schedules 
            WHERE user_id = ? AND day = ? 
            AND shift IN (".str_repeat('?,', count(SHIFT_OVERLAPS[$shift]) - 1)."?)
            AND status = 'Approved'
        ");
        $types = str_repeat('s', count(SHIFT_OVERLAPS[$shift]));
        $overlapCheck->bind_param('is'.$types, $user_id, $day, ...SHIFT_OVERLAPS[$shift]);
        $overlapCheck->execute();
        
        if ($overlapCheck->get_result()->num_rows > 0) {
            json_response([
                'status' => 'error',
                'message' => 'This shift overlaps with your existing shifts'
            ]);
        }
    }

    // Save to database
    $model = new ScheduleModel($conn);
    if ($model->addSchedule($user_id, $shift, $day)) {
        json_response(['status' => 'success', 'message' => 'Schedule request added.']);
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
  <style>
    .whole-container {
      display: flex;
      gap: 20px;
      padding: 20px;
      min-height: 100vh;
    }

    .left, .right {
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      padding: 20px;
    }

    .left { width: 30%; }
    .right { width: 70%; }

    .form-container { text-align: center; }
    
    form {
      display: flex;
      flex-direction: column;
      gap: 16px;
      text-align: left;
    }

    label {
      font-weight: 600;
      font-size: 14px;
      margin-bottom: 4px;
      display: block;
      color: #333;
    }

    input[type="text"] {
      width: 100%;
      padding: 10px 12px;
      font-size: 14px;
      border: 1px solid #ccc;
      border-radius: 8px;
      background-color: #fafafa;
      transition: border 0.3s, box-shadow 0.3s;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
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

    .btn-submit:hover:enabled { background-color: #16A085; }
    .btn-submit:disabled { background-color: #ccc; cursor: default; }

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
      transition: all 0.3s;
    }

    .option-item:hover { background: #d1e7dd; }
    .option-item.disabled { background: #ccc; color: #888; pointer-events: none; }
    .option-item.selected { background: #1ABC9C; color: #fff; }
    .option-item.selected::after { content: "✔"; margin-left: 8px; font-weight: bold; }

    .capacity-info {
      display: block;
      font-size: 0.75em;
      margin-top: 5px;
      color: #666;
    }
    .option-item.selected .capacity-info { color: #e0e0e0; }

    @media (max-width: 768px) {
      .whole-container { flex-direction: column; }
      .left, .right { width: 100%; }
      label { font-size: 13px; }
      input[type="text"] { font-size: 13px; padding: 8px 10px; }
    }
  </style>
</head>

<body>
  <div class="whole-container">
    <div class="left">
      <div class="form-container">
        <h2>Add New Schedule</h2>
        <form id="schedule-form">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES) ?>">
          <label for="shift">Shift</label>
          <input type="text" id="shift" name="shift" readonly placeholder="Select Shift" required>
          <label for="day">Day</label>
          <input type="text" id="day" name="day" readonly placeholder="Select Day" required>
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
      const choices = {
        shift: ['Opening (8 am - 5 pm)', 'Mid (10 am - 7 pm)', 'Closing (12 pm - 9 pm)'],
        day: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
      };

      const fields = ['shift', 'day'];
      let selected = { shift: '', day: ''};
      let schedules = [];

      function showOptions(field) {
        const list = choices[field] || [];
        const html = list.map(val => {
          // Check if this option should be disabled
          let disabled = false;
          let capacityInfo = '';
          
          if (field === 'shift' && selected.day) {
            // Calculate capacity for this shift+day combination
            const shiftSchedules = schedules.filter(s => 
              s.shift === val && s.day === selected.day
            );
            
            const approvedCount = shiftSchedules.filter(s => s.status === 'Approved').length;
            const maxCapacity = <?= json_encode(SHIFT_CAPACITIES) ?>[val] || 0;
            
            disabled = approvedCount >= maxCapacity;
            capacityInfo = `<span class="capacity-info">${approvedCount}/${maxCapacity} slots filled</span>`;
          } else if (field === 'day' && selected.shift) {
            // Calculate capacity for this day+shift combination
            const daySchedules = schedules.filter(s => 
              s.day === val && s.shift === selected.shift
            );
            
            const approvedCount = daySchedules.filter(s => s.status === 'Approved').length;
            const maxCapacity = <?= json_encode(SHIFT_CAPACITIES) ?>[selected.shift] || 0;
            
            disabled = approvedCount >= maxCapacity;
            capacityInfo = `<span class="capacity-info">${approvedCount}/${maxCapacity} slots filled</span>`;
          }

          // Also disable if already exists (regardless of capacity)
          const exists = schedules.some(s =>
            (s.status === 'Approved' || s.status === 'Pending') &&
            s.shift === (field === 'shift' ? val : selected.shift) &&
            s.day === (field === 'day' ? val : selected.day)
          );
          
          if (exists) disabled = true;

          return `
            <div class="option-item${disabled ? ' disabled' : ''}${selected[field] === val ? ' selected' : ''}"
                 data-field="${field}" data-val="${val}">
              ${val}
              ${capacityInfo}
            </div>
          `;
        }).join('');
        
        document.getElementById('options-container').innerHTML = `
          <div class="options-grid">${html}</div>
          ${field === 'shift' && selected.shift ? `<div class="capacity-note"><small>Max: ${<?= json_encode(SHIFT_CAPACITIES) ?>[selected.shift]} per shift</small></div>` : ''}
        `;
      }

      function refreshOptions() {
        fields.filter(f => !selected[f]).forEach(showOptions);
      }

      // Load schedules
      fetch('Tabs/add_schedule.php?action=get_schedules')
        .then(r => r.json())
        .then(data => {
          schedules = data;
          refreshOptions();
        });

      fields.forEach(f =>
        document.getElementById(f).addEventListener('click', () => showOptions(f))
      );

      document.addEventListener('click', e => {
        const item = e.target.closest('.option-item');
        if (!item || item.classList.contains('disabled')) return;
        
        const f = item.dataset.field, val = item.dataset.val;
        selected[f] = val;
        document.getElementById(f).value = val;
        refreshOptions();
      });

      document.getElementById('schedule-form').addEventListener('submit', e => {
        e.preventDefault();
        const btn = e.target.querySelector('.btn-submit');
        btn.disabled = true;
        btn.textContent = 'Adding...';
        document.getElementById('error-message').textContent = '';

        const data = new URLSearchParams();
        data.set('csrf_token', document.querySelector('[name=csrf_token]').value);
        fields.forEach(f => data.set(f, selected[f]));

        fetch('Tabs/add_schedule.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: data
        })
          .then(r => r.json())
          .then(res => {
            if (res.status === 'success') {
              alert(res.message);
              document.getElementById('schedule-form').reset();
              selected = { shift: '', day: '' };
              return fetch('Tabs/add_schedule.php?action=get_schedules');
            } else {
              document.getElementById('error-message').textContent = res.message;
              throw new Error('Handled');
            }
          })
          .then(r => r.json())
          .then(data => {
            schedules = data;
            refreshOptions();
          })
          .catch(err => {
            if (err.message !== 'Handled') {
              console.error('Error:', err);
              document.getElementById('error-message').textContent = 'An unexpected error occurred.';
            }
          })
          .finally(() => {
            btn.disabled = false;
            btn.textContent = 'Add Schedule';
          });
      });
    })();
  </script>
</body>
</html>