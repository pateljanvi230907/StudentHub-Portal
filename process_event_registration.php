
<?php
require "db.php";

function showMessage($title, $message, $errors = []) {
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
            <p><?php echo htmlspecialchars($message); ?></p>

            <?php if (!empty($errors)) { ?>
                <ul>
                    <?php foreach ($errors as $error) { ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php } ?>
                </ul>
            <?php } ?>

            <p><a href="registrationForEvent.html">Back to Registration</a></p>
            <p><a href="Events.html">Back to Events</a></p>
            <p><a href="view_event_registrations.php">View Event Registrations</a></p>
        </main>
    </body>
    </html>
    <?php
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    showMessage("Invalid Request", "Please submit the registration form.");
}

$fullname = trim($_POST["fullname"] ?? "");
$email = trim($_POST["email"] ?? "");
$dob = trim($_POST["dob"] ?? "");
$gender = $_POST["gender"] ?? "";
$blood = $_POST["blood"] ?? "";
$mobile = trim($_POST["mobile"] ?? "");
$address = trim($_POST["address"] ?? "");
$studentId = trim($_POST["studentId"] ?? "");
$university = trim($_POST["university"] ?? "");
$college = trim($_POST["college"] ?? "");
$department = $_POST["department"] ?? "";
$semester = $_POST["semester"] ?? "";
$event = $_POST["event"] ?? "";
$team = $_POST["team"] ?? "";
$members = trim($_POST["members"] ?? "");
$leader = trim($_POST["leader"] ?? "");
$payment = $_POST["payment"] ?? "";
$transaction = trim($_POST["transaction"] ?? "");
$remarks = trim($_POST["remarks"] ?? "");

$errors = [];

if (!preg_match("/^[a-zA-Z ]{2,100}$/", $fullname)) {
    $errors[] = "Enter a valid full name.";
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Enter a valid email.";
}

$date = DateTime::createFromFormat("!Y-m-d", $dob);

if (!$date || $date->format("Y-m-d") !== $dob) {
    $errors[] = "Enter a valid date of birth.";
}

if (!in_array($gender, ["Male", "Female", "Other"], true)) {
    $errors[] = "Select a valid gender.";
}

if (!in_array($blood, ["A+", "A-", "B+", "B-", "AB+", "AB-", "O+", "O-"], true)) {
    $errors[] = "Select a valid blood group.";
}

if (!preg_match("/^[6-9][0-9]{9}$/", $mobile)) {
    $errors[] = "Enter a valid 10-digit mobile number.";
}

if ($address === "" || $university === "" || $college === "") {
    $errors[] = "Address, university and college are required.";
}

if (!preg_match("/^[a-zA-Z0-9-]+$/", $studentId)) {
    $errors[] = "Enter a valid student ID.";
}

$departments = [
    "Computer Engineering",
    "Information Technology",
    "Mechanical Engineering",
    "Civil Engineering",
    "Electrical Engineering"
];

if (!in_array($department, $departments, true)) {
    $errors[] = "Select a valid department.";
}

if (!in_array($semester, ["1", "2", "3", "4", "5", "6", "7", "8"], true)) {
    $errors[] = "Select a valid semester.";
}

$allowedEvents = [
    "Hackathon",
    "Coding Competition",
    "Workshop",
    "Sports Day",
    "Technical Quiz"
];

if (!in_array($event, $allowedEvents, true)) {
    $errors[] = "Select a valid event.";
}

if (!in_array($team, ["Solo", "Team"], true)) {
    $errors[] = "Select Solo or Team participation.";
}

if ($team === "Team") {
    if (
        filter_var($members, FILTER_VALIDATE_INT) === false ||
        (int)$members < 1 ||
        (int)$members > 10
    ) {
        $errors[] = "Team size must be between 1 and 10.";
    }

    if ($leader === "") {
        $errors[] = "Enter the team leader name.";
    }
} else {
    $members = "1";
    $leader = $fullname;
}

if (!in_array($payment, ["Paid", "Pending"], true)) {
    $errors[] = "Select a payment status.";
}

if ($payment === "Paid" && $transaction === "") {
    $errors[] = "Enter the transaction ID.";
}

if (($_POST["agree"] ?? "") !== "yes") {
    $errors[] = "Accept the Terms and Conditions.";
}

$extension = "";

if (
    !isset($_FILES["idcard"]) ||
    $_FILES["idcard"]["error"] !== UPLOAD_ERR_OK
) {
    $errors[] = "Upload your college ID card.";
} else {
    $file = $_FILES["idcard"];
    $extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));

    if ($file["size"] > 2 * 1024 * 1024) {
        $errors[] = "ID card must be smaller than 2 MB.";
    }

    $types = [
        "pdf" => "application/pdf",
        "jpg" => "image/jpeg",
        "jpeg" => "image/jpeg",
        "png" => "image/png"
    ];

    if (!isset($types[$extension])) {
        $errors[] = "Only PDF, JPG, JPEG and PNG files are allowed.";
    } else {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file["tmp_name"]);
        finfo_close($finfo);

        if ($mime !== $types[$extension]) {
            $errors[] = "The uploaded file type is invalid.";
        }
    }
}

if (!empty($errors)) {
    showMessage("Registration Failed", "Please correct the errors.", $errors);
}

$uploadDirectory = __DIR__ . "/uploads";

if (!is_dir($uploadDirectory) &&
    !mkdir($uploadDirectory, 0755, true)) {
    showMessage("Storage Error", "Unable to create uploads folder.");
}

$filename = uniqid("idcard_", true) . "." . $extension;
$filePath = $uploadDirectory . "/" . $filename;

if (!move_uploaded_file($_FILES["idcard"]["tmp_name"], $filePath)) {
    showMessage("Upload Error", "Could not save the ID card.");
}

try {
    $sql = "INSERT INTO event_registration_details (
                fullname, email, dob, gender, blood_group,
                mobile, address, student_id, university, college,
                department, semester, event_name, participation,
                team_members, team_leader, id_card_file,
                payment_status, transaction_ref, remarks
            ) VALUES (
                :fullname, :email, :dob, :gender, :blood_group,
                :mobile, :address, :student_id, :university, :college,
                :department, :semester, :event_name, :participation,
                :team_members, :team_leader, :id_card_file,
                :payment_status, :transaction_ref, :remarks
            )";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        "fullname" => $fullname,
        "email" => $email,
        "dob" => $dob,
        "gender" => $gender,
        "blood_group" => $blood,
        "mobile" => $mobile,
        "address" => $address,
        "student_id" => $studentId,
        "university" => $university,
        "college" => $college,
        "department" => $department,
        "semester" => (int)$semester,
        "event_name" => $event,
        "participation" => $team,
        "team_members" => (int)$members,
        "team_leader" => $leader,
        "id_card_file" => $filename,
        "payment_status" => $payment,
        "transaction_ref" => $transaction !== "" ? $transaction : null,
        "remarks" => $remarks
    ]);

    showMessage(
        "Registration Successful",
        "Your event registration has been saved in the database."
    );

} catch (PDOException $e) {
    if (file_exists($filePath)) {
        unlink($filePath);
    }

    showMessage(
        "Database Error",
        "Unable to save the registration. Check your database table and settings."
    );
}
?>
