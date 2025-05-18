<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../config.php';
require_once '../Models/scheduleModel.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "User not logged in."]);
    exit();
}

$user_id = $_SESSION['user_id'];
$model = new ScheduleModel($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate user input
    $subject = trim($_POST['subject'] ?? '');
    $section = trim($_POST['section'] ?? '');
    $day = trim($_POST['day'] ?? '');
    $time = trim($_POST['time'] ?? '');

    if (empty($subject) || empty($section) || empty($day) || empty($time)) {
        echo json_encode(["status" => "error", "message" => "All fields are required."]);
        exit();
    }

    // Check if schedule already exists (block if duplicate Pending or Approved exists)
    $check_sql = "SELECT * FROM schedules WHERE section = ? AND day = ? AND time = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("sss", $section, $day, $time);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "This schedule is already in Pending! Please choose a different time."]);
        exit();
    } else {
        // Try adding the schedule
        if ($model->addSchedule($user_id, $subject, $section, $day, $time)) {
            echo json_encode(["status" => "success", "message" => "Schedule added successfully."]);
            exit();
        } else {
            error_log("Failed to add schedule for user ID: $user_id. Error: " . $conn->error);
            echo json_encode(["status" => "error", "message" => "Failed to add schedule. Please try again."]);
            exit();
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add New Schedule</title>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- Include your CSS here -->
  <style>
    /* Your CSS styles here */
    body, html {
     
      margin: 0;

    }
    .whole-container {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 20px;
      min-height: 100vh;
      overflow-y: auto;
      padding: 20px;
    }
    .left { width: 30%; }
    .form-container {
      background: white;
      width: 100%;
      height: 90vh;
      padding: 25px;
      border-radius: 10px;
      box-shadow: 0px 4px 10px rgba(0,0,0,0.1);
      text-align: center;
    }
    h2 { color: #2C3E50; font-weight: bold; margin-bottom: 20px; }
    form {
      display: flex;
      flex-direction: column;
      gap: 10px;
      
    }
    label { font-weight: bold; text-align: left; }
    input, select {
      width: 100%;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 5px;
      outline: none;
      transition: 0.3s;
      background-color: #fff;
      cursor: pointer;
    }
    input:focus, select:focus {
      border-color: #1ABC9C;
      box-shadow: 0px 0px 5px rgba(26,188,156,0.5);
    }
    .btn-submit {
      padding: 12px;
      background: #1ABC9C;
      color: white;
      border: none;
      cursor: pointer;
      font-weight: bold;
      border-radius: 5px;
      transition: all 0.3s ease-in-out;
    }
    .btn-submit:hover {
      background: #16A085;
      transform: scale(1.05);
    }
    .right {
      width: 70%;
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0px 4px 10px rgba(0,0,0,0.1);
      text-align: left;
      
    }
    .right h3 { text-align: center; color: #2C3E50; }
    .option-item {
      padding: 10px;
      background: #f4f4f4;
      margin: 5px 0;
      border-radius: 5px;
      cursor: pointer;
      transition: background 0.3s;
      text-align: center;
    }
    .option-item:hover { background: #d1e7dd; }
    .option-item.disabled {
      background: #ccc;
      pointer-events: none;
      color: #888;
    }
    .options-grid { display: flex; flex-wrap: wrap; gap: 10px; }
    @media (max-width: 768px) {
      .whole-container { flex-direction: column; align-items: center; }
      .left, .right { width: 90%; }
    }
  </style>
</head>
<body>
<div class="whole-container">
  <!-- Left Side - Form -->
  <div class="left">
    <div class="form-container">
      <h2>Add New Schedule</h2>
      <form id="schedule-form">
        <label>Subject:</label>
        <input type="text" id="subject" name="subject" readonly placeholder="Select Subject">
        <label>Section:</label>
        <input type="text" id="section" name="section" readonly placeholder="Select Section">
        <label>Day:</label>
        <input type="text" id="day" name="day" readonly placeholder="Select Day">
        <label>Time:</label>
        <input type="text" id="time" name="time" readonly placeholder="Select Time">
        <button type="submit" class="btn-submit">Add Schedule</button>
        <p id="error-message" style="color: red; font-weight: bold;"></p>
      </form>
    </div>
  </div>
  <!-- Right Side - Dropdown Choices -->
  <div class="right">
    <h3>Select an Option</h3>
    <div id="options-container">
      <p>Click an input field to see available options here.</p>
    </div>
  </div>
</div>

<script>
$(document).ready(function () {
    var schedulesData = []; // Stores fetched schedules
    const selectionFields = ["subject", "section", "day", "time"];
    let selectedFields = { subject: "", section: "", day: "", time: "" };

    // Dropdown options for each field
    const options = {
        subject: ["Araling Panlipunan", "Computer", "English", "Filipino", "GMRC", "Math", "MAPEH", "Science", "TLE", "Mother Tongue"],
        section: ["Grade 1 - A", "Grade 1 - B", "Grade 1 - C", "Grade 2 - A", "Grade 2 - B", "Grade 2 - C", "Grade 3 - A", "Grade 3 - B", "Grade 3 - C", "Grade 4 - A", "Grade 4 - B", "Grade 4 - C", "Grade 5 - A", "Grade 5 - B", "Grade 5 - C", "Grade 6 - A", "Grade 6 - B", "Grade 6 - C"],
        day: ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"],
        time: ["7:00 am - 8:00 am", "8:00 am - 9:00 am", "9:00 am - 10:00 am", "10:00 am - 11:00 am", "11:00 am - 12:00 pm", "12:00 pm - 1:00 pm", "1:00 pm - 2:00 pm", "2:00 pm - 3:00 pm", "3:00 pm - 4:00 pm", "4:00 pm - 5:00 pm", "5:00 pm - 6:00 pm"]
    };

    // When clicking an input field, show options on the right side
    $(".form-container input").click(function () {
        let type = $(this).attr("id");
        showOptions(type);
    });

    // Function to display options for a given field
    function showOptions(type) {
        let optionsHTML = `<h4>Select ${capitalize(type)}</h4><div class="options-grid">`;
        options[type].forEach(function(option) {
            let disabledClass = isOptionDisabled(type, option) ? "disabled" : "";
            optionsHTML += `<p class="option-item ${disabledClass}" data-type="${type}" data-value="${option}">${option}</p>`;
        });
        optionsHTML += `</div>`;
        $("#options-container").html(optionsHTML);
    }

    // Function to check if an option should be disabled based on existing schedules
    function isOptionDisabled(type, value) {
        return schedulesData.some(function(schedule) {
            // Check only if required selectedFields are set
            if ((schedule.status === "Approved" || schedule.status === "Pending")) {
                if (type === "section" && selectedFields.day && selectedFields.time) {
                    return schedule.section === value && schedule.day === selectedFields.day && schedule.time === selectedFields.time;
                } else if (type === "day" && selectedFields.section && selectedFields.time) {
                    return schedule.day === value && schedule.section === selectedFields.section && schedule.time === selectedFields.time;
                } else if (type === "time" && selectedFields.section && selectedFields.day) {
                    return schedule.time === value && schedule.section === selectedFields.section && schedule.day === selectedFields.day;
                }
            }
            return false;
        });
    }

    // Handle selection of an option
    $(document).on("click", ".option-item:not(.disabled)", function () {
        let type = $(this).data("type");
        let value = $(this).data("value");
        $("#" + type).val(value);
        selectedFields[type] = value;
        console.log(`Selected: ${type} = ${value}`);

        // Refresh options for remaining fields
        selectionFields.filter(field => !selectedFields[field]).forEach(field => showOptions(field));
        showNextOptions(type);
    });

    // Show next available field's options
    function showNextOptions(selectedType) {
        let nextOptions = selectionFields.filter(field => !selectedFields[field]);
        if (nextOptions.length > 0) {
            showOptions(nextOptions[0]);
        } else {
            console.log("All fields selected:", selectedFields);
        }
    }

    // Fetch schedules from server
    async function fetchSchedules() {
        try {
            let response = await $.ajax({
                url: "./Process/getschedule.php",
                method: "GET",
                dataType: "json"
            });
            if (response && Array.isArray(response)) {
                schedulesData = response;
                console.log("Schedules Loaded:", schedulesData);
                // Once loaded, update options for each field
                selectionFields.forEach(field => showOptions(field));
            } else {
                console.error("Invalid response:", response);
                $("#options-container").html("<p style='color: red;'>Failed to load schedules. Please try again.</p>");
            }
        } catch (error) {
            console.error("AJAX Error:", error);
            $("#options-container").html("<p style='color: red;'>Failed to load schedules. Please try again.</p>");
        }
    }

    // Capitalize first letter helper
    function capitalize(word) {
        return word.charAt(0).toUpperCase() + word.slice(1);
    }

    // Form submission using AJAX
    $("#schedule-form").on("submit", function (event) {
        event.preventDefault();
        let subject = $("#subject").val().trim();
        let section = $("#section").val().trim();
        let day = $("#day").val().trim();
        let time = $("#time").val().trim();

        if (!subject || !section || !day || !time) {
            $("#error-message").text("⚠️ Please complete all fields before submitting!").fadeIn();
            setTimeout(() => $("#error-message").fadeOut(), 3000);
            return;
        }

        $.ajax({
            url: "./Tabs/add_schedule.php",
            method: "POST",
            data: { subject, section, day, time },
            dataType: "json",
            success: function(response) {
                if (response.status === "error") {
                    $("#error-message").text(response.message).fadeIn();
                    setTimeout(() => $("#error-message").fadeOut(), 3000);
                } else if (response.status === "success") {
                    alert("Schedule added successfully!");
                    window.location.href = "./home.php";
                }
            },
            error: function() {
                $("#error-message").text("An error occurred. Please try again.").fadeIn();
                setTimeout(() => $("#error-message").fadeOut(), 3000);
            }
        });
    });

    // Fetch schedules on page load
    fetchSchedules();
});
</script>
</body>
</html>














<style>
 /* Apply full height to the body and set the overflow */



/* Layout: Left (Form) & Right (Options) */
.whole-container {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 20px;
    max-height: 100vh; /* Maximum height set to the full height of the viewport */
            overflow-y: auto; /* Makes the container scrollable if content exceeds the height */
            padding: 20px;
}

/* Left Side - Form */
.left {
    width: 30%;
}

.form-container {
    background: white;
    width: 100%;
    height: 100vh;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
    text-align: center;
}

h2 {
    color: #2C3E50;
    font-weight: bold;
    margin-bottom: 20px;
}

/* Form Fields */
form {
    display: flex;
    flex-direction: column;
    gap: 10px;
  
}

label {
    font-weight: bold;
    text-align: left;
}

input, select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    outline: none;
    transition: 0.3s;
    background-color: #fff;
    cursor: pointer;
}

input:focus, select:focus {
    border-color: #1ABC9C;
    box-shadow: 0px 0px 5px rgba(26, 188, 156, 0.5);
}

/* Submit Button */
.btn-submit {
    padding: 12px;
    background: #1ABC9C;
    color: white;
    border: none;
    cursor: pointer;
    font-weight: bold;
    border-radius: 5px;
    transition: all 0.3s ease-in-out;
}

.btn-submit:hover {
    background: #16A085;
    transform: scale(1.05);
}

/* Right Side - Options Panel */
.right {
    width: 70%;
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
    text-align: left;
    
    
}

.right h3 {
    text-align: center;
    color: #2C3E50;
}


/* Option Items */
.option-item {
    padding: 10px;
    background: #f4f4f4;
    margin: 5px 0;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.3s;
}
.options-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.option-item {
    width: calc(50% - 10px); /* Each option takes half of the width */
    padding: 10px;
    background: #f4f4f4;
    border-radius: 5px;
    text-align: center;
    cursor: pointer;
    transition: background 0.3s;
}

.option-item:hover {
    background: #d1e7dd;
}

.option-item.disabled {
    background: #ccc;
    pointer-events: none;
    color: #888;
}


/* Responsive Design */
@media (max-width: 768px) {
    .whole-container {
        flex-direction: column;
        align-items: center;
    }

    .left, .right {
        width: 90%;
    }
}
</style>