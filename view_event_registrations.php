
<?php
require "db.php";

$sql = "SELECT
            registration_id,
            fullname,
            email,
            student_id,
            college,
            department,
            semester,
            event_name,
            participation,
            team_members,
            payment_status,
            registration_date
        FROM event_registration_details
        ORDER BY registration_id DESC";

$stmt = $pdo->query($sql);
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Registration Records</title>
    <link rel="stylesheet" href="style1.css">
</head>
<body>
    <main>
        <h1>Event Registration Records</h1>

        <?php if (empty($records)) { ?>
            <p>No event registrations found.</p>
        <?php } else { ?>
            <table border="1" cellpadding="8">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Student ID</th>
                    <th>College</th>
                    <th>Department</th>
                    <th>Semester</th>
                    <th>Event</th>
                    <th>Participation</th>
                    <th>Members</th>
                    <th>Payment</th>
                    <th>Registration Date</th>
                </tr>

                <?php foreach ($records as $record) { ?>
                    <tr>
                        <?php foreach ($record as $value) { ?>
                            <td>
                                <?php echo htmlspecialchars((string)$value); ?>
                            </td>
                        <?php } ?>
                    </tr>
                <?php } ?>
            </table>
        <?php } ?>

        <p>
            <a href="registrationForEvent.html">Register for an Event</a>
        </p>

        <p><a href="Events.html">Back to Events</a></p>
    </main>
</body>
</html>
