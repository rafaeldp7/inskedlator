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

}
class ScheduleModel
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;

    }

    

    public function addSchedule($user_id, $grade, $subject, $section, $day, $time)
    {
        // Validate grade
        $grade = filter_var($grade, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 6]
        ]);
        if ($grade === false) {
            return false;
        }

        // Sanitize other inputs
        $subject = filter_var(trim($subject), FILTER_SANITIZE_STRING);
        $section = filter_var(trim($section), FILTER_SANITIZE_STRING);
        $day     = ucfirst(strtolower(trim($day)));
        $time    = filter_var(trim($time), FILTER_SANITIZE_STRING);
        $status  = "Pending";

        // Duplicate‑check including grade
        $checkSql = "
          SELECT id FROM schedules
           WHERE grade = ?
             AND section = ?
             AND day     = ?
             AND time    = ?
             AND status IN ('Pending','Approved')
        ";
        $checkStmt = $this->conn->prepare($checkSql);
        $checkStmt->bind_param("isss", $grade, $section, $day, $time);
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

        // Insert with grade
        $insertSql = "
          INSERT INTO schedules
            (user_id, grade, subject, section, day, time, status)
          VALUES (?, ?, ?, ?, ?, ?, ?)
        ";
        $stmt = $this->conn->prepare($insertSql);
        $stmt->bind_param(
            "iisssss",
            $user_id,
            $grade,
            $subject,
            $section,
            $day,
            $time,
            $status
        );

        return $stmt->execute();
    }

    public function getSchedulesUserApproved($userId) {
        $sql = "SELECT * FROM schedules WHERE user_id = ? AND status = 'Approved'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
        

    }
    
    public function getSchedulesUser($user_id) {
        $sql = "SELECT * FROM schedules WHERE user_id = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id); // Bind user_id as an integerI
        $stmt->execute();
    
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    public function getSchedulesAll() {
        $sql = "SELECT * FROM schedules";
        
        $stmt = $this->conn->prepare($sql);

        $stmt->execute();
    
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }



    public function getSchedules($sort_by = 'subject', $order = 'asc', $status = 'all') {
        // Sanitize input for security purposes
        $valid_columns = ['subject', 'section', 'day', 'time', 'user_name'];
        if (!in_array($sort_by, $valid_columns)) {
            $sort_by = 'subject';  // Default sort column
        }
        $order = ($order === 'asc' || $order === 'desc') ? $order : 'asc';
    
        // Modify the query to include status filtering if needed
        $status_condition = '';
        if ($status !== 'all') {
            $status_condition = "WHERE schedules.status = '$status'";
        }
    
        $sql = "
            SELECT schedules.*, 
                   CONCAT(users.lastname, ', ', users.firstname, ' ', users.middlename) AS user_name
            FROM schedules
            JOIN users ON schedules.user_id = users.id
            $status_condition
            ORDER BY $sort_by $order
        ";
    
        $result = $this->conn->query($sql);
    
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getSchedulesAdmin($sort_by = 'subject', $order = 'asc', $status = 'all') {
        $order = in_array($order, ['asc', 'desc']) ? $order : 'asc';
        $allowed_columns = ['subject', 'section', 'day', 'time', 'status'];
        $sort_by = in_array($sort_by, $allowed_columns) ? $sort_by : 'subject';
    
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
        $result = $stmt->get_result();
    
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    
    

    // public function deleteSchedule($schedule_id) {
    //     // Fetch the record before deleting
    //     $sql = "SELECT * FROM schedules WHERE id = ?";
    //     $stmt = $this->conn->prepare($sql);
    //     $stmt->bind_param("i", $schedule_id);
    //     $stmt->execute();
    //     $result = $stmt->get_result();
    //     $schedule = $result->fetch_assoc();
    
    //     if ($schedule) {
    //         // Convert the record to JSON format
    //         $deleted_record = json_encode($schedule);
    
    //         // Store in deleted_data table
    //         $sql = "INSERT INTO deleted_data (table_name, deleted_record) VALUES ('schedules', ?)";
    //         $stmt = $this->conn->prepare($sql);
    //         $stmt->bind_param("s", $deleted_record);
    //         $stmt->execute();
    
    //         // Now delete the schedule from schedules table
    //         $sql = "DELETE FROM schedules WHERE id = ?";
    //         $stmt = $this->conn->prepare($sql);
    //         $stmt->bind_param("i", $schedule_id);
    //         $stmt->execute();
    //     }
    // }
    
    

    
    public function updateScheduleStatus($id, $status)
    {
        $allowedStatuses = ['Pending', 'Approved', 'Rejected'];
        if (!in_array($status, $allowedStatuses)) {
            return false; // Invalid status
        }

        $stmt = $this->conn->prepare("UPDATE schedules SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);

        return $stmt->execute();
    }
    public function deleteSchedule($schedule_id) {
        // Fetch the record before deleting
        $sql = "SELECT * FROM schedules WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $schedule_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $schedule = $result->fetch_assoc();
    
        if ($schedule) {
            // Convert the record to JSON format
            $deleted_record = json_encode($schedule);
    
            // Store in deleted_data table
            $sql = "INSERT INTO deleted_data (table_name, deleted_record) VALUES ('schedules', ?)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("s", $deleted_record);
            $stmt->execute();
    
            // Now delete the schedule from schedules table
            $sql = "DELETE FROM schedules WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $schedule_id);
            $result = $stmt->execute();
            
            // Return true if deletion succeeded
            return $result;
        }
        
        // Return false if no schedule found or deletion failed
        return false;
    }
    
}
?>
