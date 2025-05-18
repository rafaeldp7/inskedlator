<?php
require_once '../../config.php';

// Get sorting parameters from URL
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'id';
$order = isset($_GET['order']) && $_GET['order'] === 'asc' ? 'ASC' : 'DESC';
// $status = isset($_GET['status']) ? $_GET['status'] : 'all'; // Filter by status

// Prevent SQL Injection by allowing only specific column names
$allowed_columns = ['id', 'fullname', 'subject', 'day', 'time', 'status'];
if (!in_array($sort_by, $allowed_columns)) {
    $sort_by = 'id'; // Default sorting column
}

// Fetch schedules with sorting
$sql = "SELECT schedules.*, 
               CONCAT(users.lastname, ', ', users.firstname, ' ', users.middlename) AS fullname 
        FROM schedules 
        JOIN users ON schedules.user_id = users.id 
        ORDER BY $sort_by $order";

$result = $conn->query($sql);
?>

<div class="container mt-3">
    <h2>Schedule Management</h2>

    <table class="table table-striped">
        <thead>
            <tr>
                <!-- <th><a href="#" class="sort" data-sort="id">ID</a></th> -->
                <th><a href="#" class="sort" data-sort="fullname">Name</a></th>
                <th><a href="#" class="sort" data-sort="subject">Subject</a></th>
                <th><a href="#" class="sort" data-sort="day">Date</a></th>
                <th><a href="#" class="sort" data-sort="status">Status</a></th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr data-id="<?= $row['id'] ?>">
                    <!-- <td><?//= $row['id'] ?></td> -->
                    <td><?= $row['fullname'] ?></td>
                    <td><?= $row['subject'] ?></td>
                    <td><?= $row['day'] ?> - <?= $row['time'] ?></td>
                    <td class="status"><?= $row['status'] ?></td>
                    <td>
                        <button class="btn btn-success btn-sm action-btn" data-action="approve">Approve</button>
                        <button class="btn btn-warning btn-sm action-btn" data-action="reject">Reject</button>
                        <button class="btn btn-danger btn-sm action-btn" data-action="delete">Delete</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
