
<?php
session_start();
require "db_mysqli.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.html");
    exit;
}

$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";

if ($email === "" || $password === "") {
    exit("Enter email and password. <a href='login.html'>Try again</a>");
}

$stmt = $conn->prepare(
    "SELECT student_id, name, email, password_hash, role
     FROM students WHERE email = ? LIMIT 1"
);

$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($id, $name, $dbEmail, $hash, $role);

$found = $stmt->fetch();
$stmt->close();

if (!$found || !password_verify($password, $hash)) {
    exit("Invalid email or password. <a href='login.html'>Try again</a>");
}

if (!in_array($role, ["student", "admin"], true)) {
    exit("Invalid account role.");
}

session_regenerate_id(true);

$_SESSION["student_id"] = $id;
$_SESSION["student_name"] = $name;
$_SESSION["role"] = $role;
$_SESSION["last_activity"] = time();

if ($role === "admin") {
    header("Location: admin_dashboard.php");
} else {
    header("Location: home.html");
}
exit;
?>
