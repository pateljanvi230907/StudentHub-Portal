
<?php
session_start();
require "db_mysqli.php";

if (!isset($_SESSION["student_id"])) {
    header("Location: login.html");
    exit;
}

if (($_SESSION["role"] ?? "") !== "admin") {
    http_response_code(403);
    exit("Access denied. Admin only.");
}

if (time() - ($_SESSION["last_activity"] ?? time()) > 900) {
    $_SESSION = [];
    session_destroy();
    header("Location: login.html?timeout=1");
    exit;
}

$_SESSION["last_activity"] = time();

$adminId = (int) $_SESSION["student_id"];

$stmt = $conn->prepare(
    "INSERT INTO audit_logs (student_id, action, details)
     VALUES (?, ?, ?)"
);

$action = "VIEW_DASHBOARD";
$details = "Administrator opened the dashboard";

$stmt->bind_param("iss", $adminId, $action, $details);
$stmt->execute();
$stmt->close();


function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

$studentCount = 0;
$eventCount = 0;
$registrationCount = 0;
$latestRegistrations = [];
$auditLogs = [];
$errorMessage = "";

try {
    $result = $conn->query("SELECT COUNT(*) AS total FROM students");
    $studentCount = $result->fetch_assoc()["total"];

    $result = $conn->query("SELECT COUNT(*) AS total FROM events");
    $eventCount = $result->fetch_assoc()["total"];

    $result = $conn->query("SELECT COUNT(*) AS total FROM registrations");
    $registrationCount = $result->fetch_assoc()["total"];

    $sql = "SELECT s.name, e.event_name, r.registration_date
            FROM registrations r
            INNER JOIN students s ON r.student_id = s.student_id
            INNER JOIN events e ON r.event_id = e.event_id
            ORDER BY r.registration_date DESC
            LIMIT 5";

    $result = $conn->query($sql);

    while ($row = $result->fetch_assoc()) {
        $latestRegistrations[] = $row;
    }
    
     $sql = "SELECT log_id, student_id, action, details, log_time
            FROM audit_logs
            ORDER BY log_time DESC
            LIMIT 10";

    $result = $conn->query($sql);

    while ($row = $result->fetch_assoc()) {
        $auditLogs[] = $row;
    }

} catch (mysqli_sql_exception $ex) {
    $errorMessage = "Unable to load dashboard data. Please check the database.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - StudentHub</title>
    <link rel="stylesheet" href="style1.css">
</head>
<body>

<header>
    <h1 align="center">StudentHub Portal</h1>
    <hr>
       
    <nav class="admin-navbar">
        <a href="home.html" class="nav-home">Home</a>
        <a href="admin_dashboard.php" class="active">Dashboard</a>
        <a href="event_management.php">Event Management</a>
        <a href="Myprofile.html">Profile</a>
        <a href="setting.html">Settings</a>
        <a href="logout.php" class="logout-link">Logout</a>
    </nav>

    <hr>
</header>

<main>
    <h2 align="center">Administrator Dashboard</h2>

    <section>
        <h3>Administrator Information</h3>
        <p><strong>Admin Name:</strong>
            <?= e($_SESSION["student_name"] ?? "Administrator") ?>
        </p>
        <p><strong>Admin ID:</strong>
            <?= e($_SESSION["student_id"]) ?>
        </p>
        <p><strong>Role:</strong>
            <?= e($_SESSION["role"]) ?>
        </p>
    </section>

    <hr>

    <section>
        <h3>Portal Statistics</h3>

        <?php if ($errorMessage !== "") { ?>
            <p><?= e($errorMessage) ?></p>
        <?php } else { ?>
            <table border="1" width="100%" cellpadding="10">
                <tr>
                    <th>Total Students</th>
                    <th>Total Events</th>
                    <th>Total Registrations</th>
                </tr>
                <tr align="center">
                    <td><?= e($studentCount) ?></td>
                    <td><?= e($eventCount) ?></td>
                    <td><?= e($registrationCount) ?></td>
                </tr>
            </table>
        <?php } ?>
    </section>

    <hr>

    <section>
        <h3>Latest Event Registrations</h3>

        <?php if (empty($latestRegistrations)) { ?>
            <p>No event registrations found.</p>
        <?php } else { ?>
            <table border="1" width="100%" cellpadding="8">
                <tr>
                    <th>Student Name</th>
                    <th>Event Name</th>
                    <th>Registration Date</th>
                </tr>

                <?php foreach ($latestRegistrations as $registration) { ?>
                    <tr>
                        <td><?= e($registration["name"]) ?></td>
                        <td><?= e($registration["event_name"]) ?></td>
                        <td><?= e($registration["registration_date"]) ?></td>
                    </tr>
                <?php } ?>
            </table>
        <?php } ?>
    </section>

    <hr>
    <section>
    <h3>Audit Log</h3>

    <?php if (empty($auditLogs)) { ?>
        <p>No audit logs found.</p>
    <?php } else { ?>
        <table border="1" width="100%" cellpadding="8">
            <tr>
                <th>Log ID</th>
                <th>Student ID</th>
                <th>Action</th>
                <th>Details</th>
                <th>Log Time</th>
            </tr>

            <?php foreach ($auditLogs as $log) { ?>
                <tr>
                    <td><?= e($log["log_id"]) ?></td>
                    <td><?= e($log["student_id"]) ?></td>
                    <td><?= e($log["action"]) ?></td>
                    <td><?= e($log["details"]) ?></td>
                    <td><?= e($log["log_time"]) ?></td>
                </tr>
            <?php } ?>
        </table>
    <?php } ?>
</section>

<hr>

    <section>
        <h3>Administrator Responsibilities</h3>
        <ul>
            <li>Monitor student and event statistics.</li>
            <li>View the latest event registrations.</li>
            <li>Manage college events.</li>
            <li>Monitor important portal activities.</li>
        </ul>
    </section>
</main>

<footer>
    <hr>
    <p align="center">Email: studenthub@gmail.com</p>
    <p align="center">&copy; 2026 StudentHub Portal</p>
</footer>

</body>
</html>
