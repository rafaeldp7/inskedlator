<?php
// Include the database configuration
require_once '../../config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fetch announcements from the database
$query = "SELECT title, DATE_FORMAT(created_at, '%M %d, %Y') AS date, content 
          FROM announcements 
          ORDER BY created_at DESC 
          LIMIT 10"; // Limit to 10 latest announcements
$result = $conn->query($query);

// Store the announcements in an array
$announcements = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $announcements[] = $row;
    }
}
?>
<div class="announcement-panel">
    <h4 class="text-dark"><i class="fa fa-bullhorn"></i> Announcements</h4>
    <hr>
    <div class="announcement-list">
        <?php if (count($announcements) > 0): ?>
            <?php foreach ($announcements as $announcement): ?>
                <div class="announcement-card">
                    <h4><?= htmlspecialchars($announcement['title']) ?></h4>
                    <p class="text-muted"><i class="fa fa-calendar-alt"></i> <?= htmlspecialchars($announcement['date']) ?></p>
                    <p><?= nl2br(htmlspecialchars($announcement['content'])) ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-announcements">
                <i class="fa fa-info-circle"></i> No announcements available.
            </div>
        <?php endif; ?>
    </div>
</div>
<style>


.announcement-panel {

    width: auto; /* Adjust based on layout needs */
    background-color: #f8f9fa; /* Light gray */
    border-left: 2px solid #dee2e6;
    padding: 10px;
    overflow-y: scroll;
    overflow-x: hidden;
    max-height: 80vh; /* Adjust based on layout needs */
    box-shadow: -2px 0 5px rgba(0, 0, 0, 0.1);
}

.announcement-card {
    border-radius: 10px;
    background-color: #ffffff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    padding: 15px;
    margin-bottom: 15px;
    transition: transform 0.2s ease;
}

.announcement-card:hover {
    transform: translateY(-5px);
}

.announcement-card h4 {
    font-size: 1rem;
    font-weight: 600;
    color: #007bff;
}

.announcement-card .text-muted {
    font-size: 0.9rem;
    color: #6c757d;
}

.announcement-card p {
    font-size: 1rem;
    color: #495057;
    margin-top: 5px;
}

.no-announcements {
    text-align: center;
    font-size: 1rem;
    color: #999;
    padding: 20px;
    border: 1px dashed #ddd;
    border-radius: 10px;
}

/* Responsive: Remove fixed position on small screens */
@media (max-width: 768px) {
    .announcement-panel {
        position: static;
        width: 100%;
        height: auto;
        border-left: none;
    }
}

</style>