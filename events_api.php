
<?php
header("Content-Type: application/json; charset=UTF-8");
require "db_mysqli.php";

function sendResponse($success, $message, $data = null)
{
    echo json_encode([
        "success" => $success,
        "message" => $message,
        "data" => $data
    ]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $result = $conn->query(
        "SELECT event_id, event_name, event_date, venue,
                description, status, poster
         FROM events
         ORDER BY event_id DESC"
    );

    if (!$result) {
        sendResponse(false, "Unable to fetch events.");
    }

    $events = [];

    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }

    sendResponse(true, "Events fetched successfully.", $events);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $input = json_decode(file_get_contents("php://input"), true);

    if (!is_array($input)) {
        sendResponse(false, "Invalid JSON data.");
    }

    $action = $input["action"] ?? "";

    if ($action === "add" || $action === "update") {
        $event_name = trim($input["event_name"] ?? "");
        $event_date = trim($input["event_date"] ?? "");
        $venue = trim($input["venue"] ?? "");
        $description = trim($input["description"] ?? "");
        $status = $input["status"] ?? "Upcoming";

        $allowedStatuses = [
            "Upcoming", "Ongoing", "Completed", "Cancelled"
        ];

        if ($event_name === "" || strlen($event_name) > 100) {
            sendResponse(false, "Enter an event name up to 100 characters.");
        }

        if ($venue === "" || strlen($venue) > 100) {
            sendResponse(false, "Enter a venue up to 100 characters.");
        }

        $date = DateTime::createFromFormat("!Y-m-d", $event_date);

        if (!$date || $date->format("Y-m-d") !== $event_date) {
            sendResponse(false, "Enter a valid event date.");
        }

        if (!in_array($status, $allowedStatuses, true)) {
            sendResponse(false, "Select a valid event status.");
        }

        if ($action === "add") {
            $stmt = $conn->prepare(
                "INSERT INTO events
                (event_name, event_date, venue, description, status)
                VALUES (?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "sssss",
                $event_name,
                $event_date,
                $venue,
                $description,
                $status
            );

            if ($stmt->execute()) {
                $stmt->close();
                sendResponse(true, "Event added successfully.");
            }

            $stmt->close();
            sendResponse(false, "Unable to add event.");
        }

        $event_id = filter_var(
            $input["event_id"] ?? null,
            FILTER_VALIDATE_INT
        );

        if (!$event_id || $event_id < 1) {
            sendResponse(false, "Invalid event ID.");
        }

        $stmt = $conn->prepare(
            "UPDATE events
             SET event_name = ?, event_date = ?, venue = ?,
                 description = ?, status = ?
             WHERE event_id = ?"
        );

        $stmt->bind_param(
            "sssssi",
            $event_name,
            $event_date,
            $venue,
            $description,
            $status,
            $event_id
        );

        if ($stmt->execute()) {
            $stmt->close();
            sendResponse(true, "Event updated successfully.");
        }

        $stmt->close();
        sendResponse(false, "Unable to update event.");
    }

    if ($action === "delete") {
        $event_id = filter_var(
            $input["event_id"] ?? null,
            FILTER_VALIDATE_INT
        );

        if (!$event_id || $event_id < 1) {
            sendResponse(false, "Invalid event ID.");
        }

        $stmt = $conn->prepare(
            "DELETE FROM events WHERE event_id = ?"
        );

        $stmt->bind_param("i", $event_id);

        if ($stmt->execute()) {
            $deleted = $stmt->affected_rows;
            $stmt->close();

            if ($deleted > 0) {
                sendResponse(true, "Event deleted successfully.");
            }

            sendResponse(false, "Event not found.");
        }

        $stmt->close();
        sendResponse(false, "Unable to delete event.");
    }

    sendResponse(false, "Invalid action.");
}

sendResponse(false, "Only GET and POST requests are supported.");
?>
