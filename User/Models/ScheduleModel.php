<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config.php';

$user_id = $_SESSION['user_id'] ?? null;
$model = new ScheduleModel($conn);

if ($user_id) {
    // Now you can use $user_id for schedule-related operations
} else {
    // User not logged in
}

class ScheduleModel
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function addSchedule($user_id, $shift, $day)
    {
        // Sanitize inputs
        $shift = filter_var(trim($shift), FILTER_SANITIZE_STRING);
        $day   = ucfirst(strtolower(trim($day)));
        $status = "Pending";

        // Check for duplicate schedule
        $checkSql = "
            SELECT id FROM schedules
            WHERE shift = ?
              AND day   = ?
              AND status IN ('Pending','Approved')
        ";
        $checkStmt = $this->conn->prepare($checkSql);
        $checkStmt->bind_param("ss", $shift, $day);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
            return false;
        }

        // Validate user exists
        $userCheck = $this->conn->prepare("SELECT id FROM users WHERE id = ?");
        $userCheck->bind_param("i", $user_id);
        $userCheck->execute();
        if ($userCheck->get_result()->num_rows === 0) {
            return false;
        }

        // Insert schedule
        $insertSql = "
            INSERT INTO schedules
                (user_id, shift, day, status)
            VALUES (?, ?, ?, ?)
        ";
        $stmt = $this->conn->prepare($insertSql);
        $stmt->bind_param("isss", $user_id, $shift, $day, $status);

        return $stmt->execute();
    }

    public function getSchedulesUserApproved($userId) {
        $sql = "SELECT * FROM schedules WHERE user_id = ? AND status = 'Approved'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getSchedulesUser($user_id) {
        $sql = "SELECT * FROM schedules WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getSchedulesAll() {
        $sql = "SELECT * FROM schedules";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getSchedules($sort_by = 'shift', $order = 'asc', $status = 'all') {
        $validSortColumns = ['shift', 'day', 'status'];
        if (!in_array($sort_by, $validSortColumns)) {
            $sort_by = 'shift';
        }

        $order = strtolower($order) === 'desc' ? 'DESC' : 'ASC';

        $query = "
            SELECT schedules.*, users.lastname AS user_name 
            FROM schedules 
            INNER JOIN users ON schedules.user_id = users.id
        ";

        if ($status !== 'all') {
            $query .= " WHERE schedules.status = ?";
        }

        $query .= " ORDER BY schedules.{$sort_by} $order";

        $stmt = $this->conn->prepare($query);

        if ($status !== 'all') {
            $stmt->bind_param("s", $status);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $schedules = [];
        while ($row = $result->fetch_assoc()) {
            $schedules[] = $row;
        }

        return $schedules;
    }

    public function getSchedulesAdmin($sort_by = 'shift', $order = 'asc', $status = 'all') {
        $order = in_array($order, ['asc', 'desc']) ? $order : 'asc';
        $allowed_columns = ['shift', 'day', 'status'];
        $sort_by = in_array($sort_by, $allowed_columns) ? $sort_by : 'shift';

        $sql = "SELECT * FROM schedules";
        if ($status !== 'all') {
            $sql .= " WHERE status = ?";
        }
        $sql .= " ORDER BY $sort_by $order";

        $stmt = $this->conn->prepare($sql);
        if ($status !== 'all') {
            $stmt->bind_param("s", $status);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function updateScheduleStatus($id, $status)
    {
        $allowedStatuses = ['Pending', 'Approved', 'Rejected'];
        if (!in_array($status, $allowedStatuses)) {
            return false;
        }

        $stmt = $this->conn->prepare("UPDATE schedules SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);

        return $stmt->execute();
    }

    public function deleteSchedule($schedule_id) {
        $sql = "SELECT * FROM schedules WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $schedule_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $schedule = $result->fetch_assoc();

        if ($schedule) {
            $deleted_record = json_encode($schedule);

            $sql = "INSERT INTO deleted_data (table_name, deleted_record) VALUES ('schedules', ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("s", $deleted_record);
            $stmt->execute();

            $sql = "DELETE FROM schedules WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $schedule_id);
            return $stmt->execute();
        }

        return false;
    }
}
?>
