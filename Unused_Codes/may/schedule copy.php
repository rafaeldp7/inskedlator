<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require '../../config.php';
require '../Models/ScheduleModel.php';


$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
$times = [
    '7:00 AM - 8:00 AM', '8:00 AM - 9:00 AM', '9:00 AM - 10:00 AM',
    '10:00 AM - 11:00 AM', '11:00 AM - 12:00 PM', '1:00 PM - 2:00 PM',
    '2:00 PM - 3:00 PM', '3:00 PM - 4:00 PM', '4:00 PM - 5:00 PM',
    '5:00 PM - 6:00 PM'
];

$loggedInUserID = $_SESSION['user_id'] ?? null;
$scheduleModel = new ScheduleModel($conn);
$schedules = $scheduleModel->getSchedulesUserApproved($loggedInUserID);




$approvedSchedules = array_filter($schedules, fn($s) => $s['status'] === 'Approved');


$scheduleTable = [];
foreach ($approvedSchedules as $sched) {
    $time = strtoupper(trim($sched['time']));  // Convert time to uppercase
    $day = trim($sched['day']);

    $scheduleTable[$time][$day] = [
        'subject' => $sched['subject'] ?? 'N/A',
        'section' => $sched['section'] ?? 'N/A',
    ];
}

?>

<!DOCTYPE html>
<html lang="en">

<body>
<div class="container table-responsive">
    <h2 class="text-center mb-4">Confirmed Schedule</h2>
    
    <table class="table table-bordered align-middle">
        <thead class="table-dark">
            <tr>
                <th>TIME</th>
                <?php foreach ($days as $day): ?>
                    <th><?php echo htmlspecialchars($day); ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($times as $time): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($time); ?></strong></td>
                    <?php foreach ($days as $day): ?>
                        <td>
                            <?php
                            $event = $scheduleTable[$time][$day] ?? null;
                            if ($event) {
                                echo "<strong>" . htmlspecialchars($event['subject']) . "</strong><br>" . 
                                    htmlspecialchars($event['section']);
                            }
                            ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>



</body>

</html>

<style>
    .container {
        width: 100%;
        height: 100%;
        background: white;
        padding: 10px;
        border-radius: 10px;
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
    }
    table {
        width: 100%;
        border-collapse: collapse;
        padding-top: 20px;
    }
    th, td {
        text-align: center;
        vertical-align: middle;
        padding: 12px;
        border: 1px solid #ddd;
    }
    th {
        width: 10%;
        background-color: #343a40;
        color: white;
    }
    tr:nth-child(even) {
        background-color: #f2f2f2;
    }
    tr:hover {
        background-color: #d1ecf1;
        transition: 0.3s ease-in-out;
    }
    td {
        height: 80px;
        font-size: 14px;
    }

    @media (max-width: 768px) {
        .container {
            width: 95%;
        }
        th, td {
            font-size: 12px;
            padding: 8px;
        }
    }
</style> 


 