<!DOCTYPE html>
<html lang="en">

<head>

    <style>
        .container {
            background-color: transparent;
        }

        .calendar-container {
            max-width: 95%;
            margin: 0 auto;
            background-color: white;
            padding: 10px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            height: 95vh;
            overflow-y: hidden;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #f1f1f1;
            padding-bottom: 10px;
        }

        .calendar-header h2 {
            margin: 0;
            font-size: 32px;
            font-weight: 600;
            color: #333;
        }

        .calendar-header button {
            background-color: #007bff;
            color: white;
            border: none;
            font-size: 20px;
            padding: 12px 20px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .calendar-header button:hover {
            background-color: #0056b3;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 0.5fr);
            gap: 10px;
            text-align: center;
        }

        .calendar-grid .day-name {
            font-weight: bold;
            font-size: 16px;
            color: #333;
            background-color: #e2e2e2;
            border-radius: 8px;
            padding: 5px 0;
        }

        .calendar-days {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
            height: calc(80vh);
        }

        .calendar-days div {
            padding: 15px;
            background-color: #fff;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            max-height: 100%;
        }

        .calendar-days div:hover {
            background-color: #f0f0f0;
            transform: scale(1.05);
        }

        .calendar-days .disabled {
            color: #bbb;
        }

        .calendar-days .current-day {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }

        .calendar-days .active-day {
            background-color: #28a745;
            color: white;
            border-radius: 50%;
        }

        @media (max-width: 600px) {
            .calendar-container {
                padding: 20px;
            }

            .calendar-header h2 {
                font-size: 28px;
            }

            .calendar-grid .day-name,
            .calendar-days div {
                font-size: 14px;
                padding: 10px;
            }
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="calendar-container">
            <div class="calendar-header">
                <button id="prevBtn">&#8249;</button>
                <h2 id="monthYear"></h2>
                <button id="nextBtn">&#8250;</button>
            </div>
            <div class="calendar-grid">
                <div class="day-name">Sun</div>
                <div class="day-name">Mon</div>
                <div class="day-name">Tue</div>
                <div class="day-name">Wed</div>
                <div class="day-name">Thu</div>
                <div class="day-name">Fri</div>
                <div class="day-name">Sat</div>
            </div>
            <div class="calendar-days" id="calendarDays">
                <!-- Calendar days will be injected here -->
            </div>
        </div>
    </div>

    <script>
        let currentMonth = localStorage.getItem("currentMonth") ? parseInt(localStorage.getItem("currentMonth")) : new Date().getMonth();
        let currentYear = localStorage.getItem("currentYear") ? parseInt(localStorage.getItem("currentYear")) : new Date().getFullYear();

        const monthNames = [
            "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"
        ];

        const today = new Date();
        const todayDate = today.getDate();
        const todayMonth = today.getMonth();
        const todayYear = today.getFullYear();

        function generateCalendar() {
            const firstDay = new Date(currentYear, currentMonth, 1);
            const lastDay = new Date(currentYear, currentMonth + 1, 0);

            const monthYear = document.getElementById("monthYear");
            const calendarDays = document.getElementById("calendarDays");

            monthYear.textContent = `${monthNames[currentMonth]} ${currentYear}`;
            calendarDays.innerHTML = "";

            const emptyCells = firstDay.getDay();
            for (let i = 0; i < emptyCells; i++) {
                const emptyCell = document.createElement("div");
                emptyCell.classList.add("disabled");
                calendarDays.appendChild(emptyCell);
            }

            for (let day = 1; day <= lastDay.getDate(); day++) {
                const dayCell = document.createElement("div");
                dayCell.classList.add("day");
                dayCell.textContent = day;

                if (day === todayDate && currentMonth === todayMonth && currentYear === todayYear) {
                    dayCell.classList.add("current-day");
                }

                calendarDays.appendChild(dayCell);
            }

            // Save to localStorage
            localStorage.setItem("currentMonth", currentMonth);
            localStorage.setItem("currentYear", currentYear);
        }

        document.getElementById("prevBtn").addEventListener("click", function () {
            currentMonth--;
            if (currentMonth < 0) {
                currentMonth = 11;
                currentYear--;
            }
            generateCalendar();
        });

        document.getElementById("nextBtn").addEventListener("click", function () {
            currentMonth++;
            if (currentMonth > 11) {
                currentMonth = 0;
                currentYear++;
            }
            generateCalendar();
        });

        document.addEventListener('DOMContentLoaded', generateCalendar);
    </script>

</body>

</html>
