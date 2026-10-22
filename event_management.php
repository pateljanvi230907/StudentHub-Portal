
<?php
session_start();
require "db_mysqli.php";

if (!isset($_SESSION["student_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    http_response_code(403);
    exit("Access denied. Please log in as an administrator.");
}

$uploadDir = __DIR__ . "/uploads/";

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$statuses = ["Upcoming", "Ongoing", "Completed", "Cancelled"];
$message = "";
$error = "";
$edit = null;

function uploadPoster($file, $uploadDir)
{
    if ($file["error"] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file["error"] !== UPLOAD_ERR_OK) {
        throw new Exception("Poster upload failed.");
    }

    if ($file["size"] > 2 * 1024 * 1024) {
        throw new Exception("Poster size must not exceed 2 MB.");
    }

    $allowed = [
        "image/jpeg" => "jpg",
        "image/png" => "png",
        "image/gif" => "gif",
        "image/webp" => "webp"
    ];

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file["tmp_name"]);

    if (!isset($allowed[$mime]) || getimagesize($file["tmp_name"]) === false) {
        throw new Exception("Only JPG, PNG, GIF and WEBP images are allowed.");
    }

    $filename = bin2hex(random_bytes(16)) . "." . $allowed[$mime];

    if (!move_uploaded_file($file["tmp_name"], $uploadDir . $filename)) {
        throw new Exception("Unable to save the poster.");
    }

    return $filename;
}

function removePoster($filename, $uploadDir)
{
    if ($filename !== null && $filename !== "") {
        $path = $uploadDir . basename($filename);

        if (is_file($path)) {
            unlink($path);
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    try {
        if ($action === "add" || $action === "update") {
            $event_name = trim($_POST["event_name"] ?? "");
            $description = trim($_POST["description"] ?? "");
            $event_date = $_POST["event_date"] ?? "";
            $venue = trim($_POST["venue"] ?? "");
            $status = $_POST["status"] ?? "Upcoming";
            $event_id = (int)($_POST["event_id"] ?? 0);

            if ($event_name === "" || strlen($event_name) > 100) {
                throw new Exception("Enter an event name of up to 100 characters.");
            }

            if ($venue === "" || strlen($venue) > 100) {
                throw new Exception("Enter a venue of up to 100 characters.");
            }

            $date = DateTime::createFromFormat("!Y-m-d", $event_date);

            if (!$date || $date->format("Y-m-d") !== $event_date) {
                throw new Exception("Please enter a valid event date.");
            }

            if (!in_array($status, $statuses, true)) {
                throw new Exception("Please select a valid event status.");
            }

            $oldPoster = null;

            if ($action === "update") {
                $check = $conn->prepare(
                    "SELECT poster FROM events WHERE event_id = ?"
                );
                $check->bind_param("i", $event_id);
                $check->execute();
                $check->bind_result($oldPoster);

                if (!$check->fetch()) {
                    $check->close();
                    throw new Exception("Event not found.");
                }

                $check->close();
            }

            $newPoster = uploadPoster(
                $_FILES["poster"] ?? ["error" => UPLOAD_ERR_NO_FILE],
                $uploadDir
            );

            $poster = $newPoster ?? $oldPoster;

            if ($action === "add") {
                $stmt = $conn->prepare(
                    "INSERT INTO events
                    (event_name, description, event_date, venue, status, poster)
                    VALUES (?, ?, ?, ?, ?, ?)"
                );

                $stmt->bind_param(
                    "ssssss",
                    $event_name,
                    $description,
                    $event_date,
                    $venue,
                    $status,
                    $poster
                );

                if (!$stmt->execute()) {
                    throw new Exception("Unable to add the event.");
                }

                $stmt->close();
                header("Location: event_management.php?message=added");
                exit;
            }

            $stmt = $conn->prepare(
                "UPDATE events
                SET event_name = ?, description = ?, event_date = ?,
                    venue = ?, status = ?, poster = ?
                WHERE event_id = ?"
            );

            $stmt->bind_param(
                "ssssssi",
                $event_name,
                $description,
                $event_date,
                $venue,
                $status,
                $poster,
                $event_id
            );

            if (!$stmt->execute()) {
                if ($newPoster !== null) {
                    removePoster($newPoster, $uploadDir);
                }

                throw new Exception("Unable to update the event.");
            }

            $stmt->close();

            if ($newPoster !== null) {
                removePoster($oldPoster, $uploadDir);
            }

            header("Location: event_management.php?message=updated");
            exit;
        }

        if ($action === "delete") {
            $event_id = (int)($_POST["event_id"] ?? 0);

            $stmt = $conn->prepare(
                "SELECT poster FROM events WHERE event_id = ?"
            );
            $stmt->bind_param("i", $event_id);
            $stmt->execute();
            $stmt->bind_result($poster);

            if (!$stmt->fetch()) {
                $stmt->close();
                throw new Exception("Event not found.");
            }

            $stmt->close();

            $stmt = $conn->prepare(
                "DELETE FROM events WHERE event_id = ?"
            );
            $stmt->bind_param("i", $event_id);

            if (!$stmt->execute()) {
                throw new Exception("Unable to delete the event.");
            }

            $stmt->close();
            removePoster($poster, $uploadDir);

            header("Location: event_management.php?message=deleted");
            exit;
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

if (isset($_GET["message"])) {
    $messages = [
        "added" => "Event added successfully.",
        "updated" => "Event updated successfully.",
        "deleted" => "Event deleted successfully."
    ];

    $message = $messages[$_GET["message"]] ?? "";
}

if (isset($_GET["edit"])) {
    $event_id = (int)$_GET["edit"];

    $stmt = $conn->prepare(
        "SELECT event_id, event_name, description, event_date,
                venue, status, poster
         FROM events WHERE event_id = ?"
    );
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit = $result->fetch_assoc();
    $stmt->close();

    if (!$edit) {
        $error = "Event not found.";
    }
}

$events = $conn->query(
    "SELECT event_id, event_name, description, event_date,
            venue, status, poster
     FROM events ORDER BY event_date DESC"
);

function h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management - StudentHub</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f6f9;
            color: #222;
        }

        h1, h2 {
            color: #234b75;
        }

        .container {
            max-width: 1100px;
            margin: auto;
        }

        form, .event-list {
            background-color: white;
            padding: 20px;
            margin-bottom: 25px;
            border-radius: 8px;
            border: 1px solid #ddd;
        }

        label {
            display: block;
            margin-top: 12px;
            font-weight: bold;
        }

        input, textarea, select {
            width: 100%;
            padding: 9px;
            margin-top: 5px;
            box-sizing: border-box;
        }

        
    button, .edit-link {
        display: inline-block;
        padding: 9px 14px;
        margin-top: 12px;
        border: none;
        border-radius: 4px;
        color: white;
        background-color: #234b75;
        cursor: pointer;
        text-decoration: none;
        white-space: nowrap;
    }

    .delete-button {
        background-color: #b42318;
        width: auto;
    }
    .delete-button {
            background-color: #b42318;
        }

        .message {
            color: #176b35;
            margin-bottom: 15px;
        }

        .error {
            color: #b42318;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
        }

        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
            overflow-wrap: anywhere;
        }

        th {
            background-color: #234b75;
            color: white;
        }

        .poster {
            width: 90px;
            max-height: 90px;
            object-fit: contain;
        }

        .table-wrapper {
            overflow-x: auto;
        }
    </style>
</head>

<body>
<div class="container">

    <h1>StudentHub Event Management</h1>

    <?php if ($message !== ""): ?>
        <p class="message"><?= h($message) ?></p>
    <?php endif; ?>

    <?php if ($error !== ""): ?>
        <p class="error"><?= h($error) ?></p>
    <?php endif; ?>

    <h2><?= $edit ? "Update Event" : "Add New Event" ?></h2>

    <form action="event_management.php<?= $edit ? '?edit=' . (int)$edit['event_id'] : '' ?>"
          method="post" enctype="multipart/form-data">

        <input type="hidden" name="action"
               value="<?= $edit ? 'update' : 'add' ?>">

        <?php if ($edit): ?>
            <input type="hidden" name="event_id"
                   value="<?= (int)$edit['event_id'] ?>">
        <?php endif; ?>

        <label for="event_name">Event Name</label>
        <input type="text" id="event_name" name="event_name"
               maxlength="100" required
               value="<?= h($edit['event_name'] ?? '') ?>">

        <label for="description">Description</label>
        <textarea id="description" name="description"
                  rows="4"><?= h($edit['description'] ?? '') ?></textarea>

        <label for="event_date">Event Date</label>
        <input type="date" id="event_date" name="event_date" required
               value="<?= h($edit['event_date'] ?? '') ?>">

        <label for="venue">Venue</label>
        <input type="text" id="venue" name="venue"
               maxlength="100" required
               value="<?= h($edit['venue'] ?? '') ?>">

        <label for="status">Event Status</label>
        <select id="status" name="status" required>
            <?php foreach ($statuses as $s): ?>
                <option value="<?= h($s) ?>"
                    <?= ($edit['status'] ?? 'Upcoming') === $s
                        ? 'selected' : '' ?>>
                    <?= h($s) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="poster">Event Poster (JPG, PNG, GIF or WEBP; maximum 2 MB)</label>
        <input type="hidden" name="MAX_FILE_SIZE" value="2097152">
        <input type="file" id="poster" name="poster"
               accept=".jpg,.jpeg,.png,.gif,.webp,image/jpeg,image/png,image/gif,image/webp">

        <?php if ($edit && !empty($edit['poster'])): ?>
            <p>Current poster:</p>
            <img class="poster"
                 src="uploads/<?= rawurlencode(basename($edit['poster'])) ?>"
                 alt="Current event poster">
            <p>Leave the poster field empty to keep the current poster.</p>
        <?php endif; ?>

        <button type="submit">
            <?= $edit ? "Update Event" : "Add Event" ?>
        </button>

        <?php if ($edit): ?>
            <a class="edit-link" href="event_management.php">Cancel</a>
        <?php endif; ?>
    </form>

    <h2>All Events</h2>

    <div class="event-list">
        <div class="table-wrapper">
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Event Name</th>
                    <th>Description</th>
                    <th>Date</th>
                    <th>Venue</th>
                    <th>Status</th>
                    <th>Poster</th>
                    <th>Actions</th>
                </tr>
                </thead>

                <tbody>
                <?php if ($events && $events->num_rows > 0): ?>
                    <?php while ($row = $events->fetch_assoc()): ?>
                        <tr>
                            <td><?= (int)$row['event_id'] ?></td>
                            <td><?= h($row['event_name']) ?></td>
                            <td><?= nl2br(h($row['description'])) ?></td>
                            <td><?= h($row['event_date']) ?></td>
                            <td><?= h($row['venue']) ?></td>
                            <td><?= h($row['status']) ?></td>
                            <td>
                                <?php if (!empty($row['poster'])): ?>
                                    <img class="poster"
                                         src="uploads/<?= rawurlencode(basename($row['poster'])) ?>"
                                         alt="Event poster">
                                <?php else: ?>
                                    No poster
                                <?php endif; ?>
                            </td>
                            <td>
                                <a class="edit-link"
                                   href="event_management.php?edit=<?= (int)$row['event_id'] ?>">
                                    Edit
                                </a>

                                <form action="event_management.php"
                                      method="post"
                                      onsubmit="return confirm('Are you sure you want to delete this event?');">

                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="event_id"
                                           value="<?= (int)$row['event_id'] ?>">

                                    <button class="delete-button" type="submit">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">No events found.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
</body>
</html>
