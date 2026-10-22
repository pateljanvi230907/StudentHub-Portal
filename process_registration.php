
<?php
require "db_mysqli.php";

function showMessage($title, $messages)
{
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($title); ?></title>
        <link rel="stylesheet" href="style1.css">
    </head>
    <body>
        <main>
            <h1><?php echo htmlspecialchars($title); ?></h1>
            <ul>
                <?php foreach ($messages as $message) { ?>
                    <li><?php echo htmlspecialchars($message); ?></li>
                <?php } ?>
            </ul>
            <p><a href="registration.html">Back to Registration</a></p>
            <p><a href="process_registration.php?action=view">View Registrations</a></p>
        </main>
    </body>
    </html>
    <?php
    exit;
}

if (isset($_GET["action"]) && $_GET["action"] === "view") {
    displayRecords($conn);
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    showMessage("Error", ["Please open the registration form first."]);
}

$name = trim($_POST["name"] ?? "");
$username = trim($_POST["username"] ?? "");
$email = trim($_POST["email"] ?? "");
$mobile = trim($_POST["mobile"] ?? "");
$password = $_POST["password"] ?? "";
$confirmPassword = $_POST["confirmPassword"] ?? "";
$course = trim($_POST["course"] ?? "");
$year = trim($_POST["year"] ?? "");
$gender = trim($_POST["gender"] ?? "");

$errors = [];

if (!preg_match("/^[A-Za-z]+( [A-Za-z]+)*$/", $name)) {
    $errors[] = "Enter your first and last name using letters only.";
}

if (!preg_match("/^[A-Za-z0-9_]{3,50}$/", $username)) {
    $errors[] = "Username must contain 3-50 letters, numbers, or underscores.";
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
    $errors[] = "Enter a valid email address.";
}

if (!preg_match("/^[6-9][0-9]{9}$/", $mobile)) {
    $errors[] = "Enter a valid 10-digit mobile number.";
}

if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{8,}$/", $password)) {
    $errors[] = "Password needs uppercase, lowercase, number, special character and at least 8 characters.";
}

if ($password !== $confirmPassword) {
    $errors[] = "Passwords do not match.";
}

$allowedCourses = [
    "Computer Engineering",
    "Information Technology",
    "Computer Science And Engineering"
];

if (!in_array($course, $allowedCourses, true)) {
    $errors[] = "Select a valid course.";
}

if (!in_array($year, ["1", "2", "3", "4"], true)) {
    $errors[] = "Select a valid year.";
}

if (!in_array($gender, ["Male", "Female", "Other"], true)) {
    $errors[] = "Select a valid gender.";
}

if (($_POST["terms"] ?? "") !== "Accepted") {
    $errors[] = "Accept the Terms and Conditions.";
}

if ($errors) {
    showMessage("Registration Failed", $errors);
}

$stmt = $conn->prepare(
    "SELECT username, email FROM students WHERE username = ? OR email = ?"
);
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($existingUsername, $existingEmail);
    $stmt->fetch();
    $stmt->close();

    $errors = [];

    if (strcasecmp($existingUsername, $username) === 0) {
        $errors[] = "This username is already registered.";
    }

    if (strcasecmp($existingEmail, $email) === 0) {
        $errors[] = "This email is already registered.";
    }

    showMessage("Registration Failed", $errors);
}
$stmt->close();

$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare(
    "INSERT INTO students
    (name, username, email, mobile, password_hash, department, year, gender)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
);

$yearNumber = (int)$year;
$stmt->bind_param(
    "ssssssis",
    $name,
    $username,
    $email,
    $mobile,
    $passwordHash,
    $course,
    $yearNumber,
    $gender
);

try {
    if ($stmt->execute()) {
        $stmt->close();
        showMessage("Registration Successful", [
            "Your account has been created successfully."
        ]);
    }
} catch (mysqli_sql_exception $e) {
    $stmt->close();

    if ($e->getCode() === 1062) {
        showMessage("Registration Failed", [
            "This username or email is already registered. Please use different details."
        ]);
    }

    showMessage("Error", [
        "Unable to save your registration. Please check your database setup."
    ]);
}

function displayRecords($conn)
{
    $result = $conn->query(
        "SELECT student_id, name, username, email, mobile, department, year, gender
         FROM students ORDER BY student_id DESC"
    );
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Student Registration Records</title>
        <link rel="stylesheet" href="style1.css">
    </head>
    <body>
        <main>
            <h1>Student Registration Records</h1>
            <?php if ($result->num_rows === 0) { ?>
                <p>No registrations found.</p>
            <?php } else { ?>
                <table border="1" cellpadding="8">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Mobile</th>
                        <th>Course</th>
                        <th>Year</th>
                        <th>Gender</th>
                    </tr>
                    <?php while ($record = $result->fetch_assoc()) { ?>
                        <tr>
                            <?php foreach ($record as $value) { ?>
                                <td><?php echo htmlspecialchars((string)$value); ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </table>
            <?php } ?>
            <p><a href="registration.html">Back to Registration</a></p>
        </main>
    </body>
    </html>
    <?php
    $result->free();
    exit;
}
?>
