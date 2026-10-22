
<?php
require "db_mysqli.php";

$message = "";
$editStudent = null;

function h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function redirectMessage($message)
{
    header("Location: student_management.php?message=" . urlencode($message));
    exit;
}

if (isset($_GET["message"])) {
    $message = $_GET["message"];
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "add" || $action === "update") {
        $name = trim($_POST["name"] ?? "");
        $username = trim($_POST["username"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $mobile = trim($_POST["mobile"] ?? "");
        $department = trim($_POST["department"] ?? "");
        $year = (int)($_POST["year"] ?? 0);
        $gender = trim($_POST["gender"] ?? "");

        if (
            $name === "" || $username === "" || $email === "" ||
            $mobile === "" || $department === "" || $gender === "" ||
            $year < 1 || $year > 6
        ) {
            redirectMessage("Please fill all fields correctly.");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            redirectMessage("Please enter a valid email address.");
        }

        if (strlen($mobile) > 10) {
            redirectMessage("Mobile number must not exceed 10 characters.");
        }

        $id = (int)($_POST["student_id"] ?? 0);

        if ($action === "add") {
            $check = $conn->prepare(
                "SELECT student_id FROM students WHERE email = ? OR username = ?"
            );
            $check->bind_param("ss", $email, $username);
        } else {
            $check = $conn->prepare(
                "SELECT student_id FROM students
                 WHERE (email = ? OR username = ?) AND student_id <> ?"
            );
            $check->bind_param("ssi", $email, $username, $id);
        }

        $check->execute();
        $check->store_result();
        $duplicate = $check->num_rows > 0;
        $check->close();

        if ($duplicate) {
            redirectMessage("Email or username already exists.");
        }

        if ($action === "add") {
            $passwordHash = password_hash("Student@123", PASSWORD_DEFAULT);
            $role = "student";

            $stmt = $conn->prepare(
                "INSERT INTO students
                (name, username, email, mobile, password_hash,
                 department, year, gender, role)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "ssssssiss",
                $name,
                $username,
                $email,
                $mobile,
                $passwordHash,
                $department,
                $year,
                $gender,
                $role
            );

            if ($stmt->execute()) {
                $stmt->close();
                redirectMessage(
                    "Student added successfully. Default password: Student@123"
                );
            }

            $stmt->close();
            redirectMessage("Failed to add student.");
        }

        $stmt = $conn->prepare(
            "UPDATE students
             SET name = ?, username = ?, email = ?, mobile = ?,
                 department = ?, year = ?, gender = ?
             WHERE student_id = ?"
        );

        $stmt->bind_param(
            "sssssisi",
            $name,
            $username,
            $email,
            $mobile,
            $department,
            $year,
            $gender,
            $id
        );

        if ($stmt->execute()) {
            $stmt->close();
            redirectMessage("Student updated successfully.");
        }

        $stmt->close();
        redirectMessage("Failed to update student.");
    }

    if ($action === "delete") {
        $id = (int)($_POST["student_id"] ?? 0);

        $stmt = $conn->prepare(
            "DELETE FROM students WHERE student_id = ?"
        );
        $stmt->bind_param("i", $id);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $stmt->close();
            redirectMessage("Student deleted successfully.");
        }

        $stmt->close();
        redirectMessage("Student not found or deletion failed.");
    }
}

if (isset($_GET["edit"])) {
    $id = (int)$_GET["edit"];

    $stmt = $conn->prepare(
        "SELECT student_id, name, username, email, mobile,
                department, year, gender
         FROM students WHERE student_id = ?"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $stmt->bind_result(
        $editId,
        $editName,
        $editUsername,
        $editEmail,
        $editMobile,
        $editDepartment,
        $editYear,
        $editGender
    );

    if ($stmt->fetch()) {
        $editStudent = [
            "student_id" => $editId,
            "name" => $editName,
            "username" => $editUsername,
            "email" => $editEmail,
            "mobile" => $editMobile,
            "department" => $editDepartment,
            "year" => $editYear,
            "gender" => $editGender
        ];
    }

    $stmt->close();
}

$search = trim($_GET["search"] ?? "");
$departmentFilter = trim($_GET["department"] ?? "");
$term = "%" . $search . "%";

$stmt = $conn->prepare(
    "SELECT student_id, name, username, email, mobile,
            department, year, gender
     FROM students
     WHERE (? = '' OR name LIKE ? OR email LIKE ? OR department LIKE ?)
       AND (? = '' OR department = ?)
     ORDER BY student_id DESC"
);

$stmt->bind_param(
    "ssssss",
    $search,
    $term,
    $term,
    $term,
    $departmentFilter,
    $departmentFilter
);

$stmt->execute();

$stmt->bind_result(
    $studentId,
    $studentName,
    $studentUsername,
    $studentEmail,
    $studentMobile,
    $studentDepartment,
    $studentYear,
    $studentGender
);

$students = [];

while ($stmt->fetch()) {
    $students[] = [
        "student_id" => $studentId,
        "name" => $studentName,
        "username" => $studentUsername,
        "email" => $studentEmail,
        "mobile" => $studentMobile,
        "department" => $studentDepartment,
        "year" => $studentYear,
        "gender" => $studentGender
    ];
}

$stmt->close();

$departments = [];
$result = $conn->query(
    "SELECT DISTINCT department FROM students ORDER BY department"
);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        if ($row["department"] !== "") {
            $departments[] = $row["department"];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management - StudentHub</title>

    <link rel="stylesheet" href="style1.css">

    <style>
        .crud-container {
            max-width: 1100px;
            margin: 30px auto;
            padding: 20px;
            box-sizing: border-box;
        }

        .crud-container h1,
        .crud-container h2 {
            color: #1d4778;
        }

        .crud-form, .search-form {
            display: block;
            background: white;
            padding: 25px;
            margin-bottom: 25px;
            border: 1px solid #dddddd;
            border-radius: 8px;
            box-sizing: border-box;
        }

        .crud-form label, .search-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        .crud-form input, .crud-form select,
        .search-form input, .search-form select {
            display: block;
            width: 100%;
            padding: 12px;
            margin: 0 0 18px;
            border: 1px solid #bbbbbb;
            border-radius: 5px;
            box-sizing: border-box;
            background: white;
        }

        .crud-container button,
        .action-button {
            display: inline-block;
            padding: 10px 16px;
            margin: 3px;
            border: none;
            border-radius: 5px;
            background: #1d4e89;
            color: white;
            text-decoration: none;
            cursor: pointer;
        }

        .crud-container button:hover,
        .action-button:hover {
            background: #163966;
        }

        .crud-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        .crud-table th, .crud-table td {
            border: 1px solid #dddddd;
            padding: 10px;
            text-align: left;
        }

        .crud-table th {
            background: #243b64;
            color: white;
        }

        .message {
            padding: 12px;
            background: #e4f6e8;
            border: 1px solid #a6d9b0;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        @media (max-width: 600px) {
            .crud-container {
                padding: 12px;
                margin: 10px auto;
            }

            .crud-form, .search-form {
                padding: 15px;
            }
        }
    </style>
</head>
<body>

<div class="crud-container">

    <h1>Student Management</h1>
    <p><bold>Add, view, edit, delete, search and filter student records.</p>

    <?php if ($message !== ""): ?>
        <div class="message"><?= h($message) ?></div>
    <?php endif; ?>

    <h2><?= $editStudent ? "Edit Student" : "Add New Student" ?></h2>

    <form class="crud-form" method="post" action="student_management.php">

        <input type="hidden" name="action"
               value="<?= $editStudent ? "update" : "add" ?>">

        <?php if ($editStudent): ?>
            <input type="hidden" name="student_id"
                   value="<?= h($editStudent["student_id"]) ?>">
        <?php endif; ?>

        <label>Student Name</label>
        <input type="text" name="name" maxlength="100" required
               value="<?= h($editStudent["name"] ?? "") ?>">

        <label>Username</label>
        <input type="text" name="username" maxlength="50" required
               value="<?= h($editStudent["username"] ?? "") ?>">

        <label>Email</label>
        <input type="email" name="email" maxlength="100" required
               value="<?= h($editStudent["email"] ?? "") ?>">

        <label>Mobile</label>
        <input type="text" name="mobile" maxlength="10" required
               value="<?= h($editStudent["mobile"] ?? "") ?>">

        <label>Department / Course</label>
        <input type="text" name="department" maxlength="100" required
               value="<?= h($editStudent["department"] ?? "") ?>">

        <label>Year of Study</label>
        <select name="year" required>
            <option value="">Select year</option>
            <?php for ($y = 1; $y <= 6; $y++): ?>
                <option value="<?= $y ?>"
                    <?= isset($editStudent["year"]) &&
                        (int)$editStudent["year"] === $y ? "selected" : "" ?>>
                    Year <?= $y ?>
                </option>
            <?php endfor; ?>
        </select>

        <label>Gender</label>
        <select name="gender" required>
            <option value="">Select gender</option>
            <?php foreach (["Male", "Female", "Other"] as $g): ?>
                <option value="<?= h($g) ?>"
                    <?= isset($editStudent["gender"]) &&
                        $editStudent["gender"] === $g ? "selected" : "" ?>>
                    <?= h($g) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">
            <?= $editStudent ? "Update Student" : "Add Student" ?>
        </button>

        <?php if ($editStudent): ?>
            <a class="action-button" href="student_management.php">
                Cancel Edit
            </a>
        <?php endif; ?>

    </form>

    <h2>Search and Filter Students</h2>

    <form class="search-form" method="get" action="student_management.php">

        <label>Search by Name, Email or Department/Course</label>
        <input type="text" name="search"
               placeholder="Enter name, email or course"
               value="<?= h($search) ?>">

        <label>Filter by Department/Course</label>
        <select name="department">
            <option value="">All departments</option>

            <?php foreach ($departments as $dept): ?>
                <option value="<?= h($dept) ?>"
                    <?= $departmentFilter === $dept ? "selected" : "" ?>>
                    <?= h($dept) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Search / Filter</button>
        <a class="action-button" href="student_management.php">Clear</a>

    </form>

    <h2>Student Records (<?= count($students) ?>)</h2>

    <div class="table-wrapper">
        <table class="crud-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Mobile</th>
                    <th>Department / Course</th>
                    <th>Year</th>
                    <th>Gender</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
            <?php if (count($students) > 0): ?>

                <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?= h($student["student_id"]) ?></td>
                        <td><?= h($student["name"]) ?></td>
                        <td><?= h($student["username"]) ?></td>
                        <td><?= h($student["email"]) ?></td>
                        <td><?= h($student["mobile"]) ?></td>
                        <td><?= h($student["department"]) ?></td>
                        <td><?= h($student["year"]) ?></td>
                        <td><?= h($student["gender"]) ?></td>
                        <td>
                            <a class="action-button"
                               href="student_management.php?edit=<?= h($student["student_id"]) ?>">
                                Edit
                            </a>

                            <form method="post"
                                  action="student_management.php"
                                  style="display:inline"
                                  onsubmit="return confirm('Are you sure you want to delete this student?');">

                                <input type="hidden" name="action" value="delete">

                                <input type="hidden" name="student_id"
                                       value="<?= h($student["student_id"]) ?>">

                                <button type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>

            <?php else: ?>
                <tr>
                    <td colspan="9">No student records found.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <p><a href="home.html">Back to Home</a></p>

</div>

</body>
</html>
