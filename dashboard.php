
<?php
session_start();

if (!isset($_SESSION["student_id"])) {
    header("Location: login.html");
    exit;
}

if (($_SESSION["role"] ?? "") !== "student") {
    http_response_code(403);
    exit("Access denied.");
}

if (time() - ($_SESSION["last_activity"] ?? time()) > 900) {
    $_SESSION = [];
    session_destroy();
    header("Location: login.html?timeout=1");
    exit;
}

$_SESSION["last_activity"] = time();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>StudentHub Dashboard</title>
    <link rel="stylesheet" href="style.css">
    
</head>

<body>
    <header>
        <h1 align="center">StudentHub Dashboard</h1>
        <hr>
        <table width="100%">
            <tr>
                    <td><a href="home.html">Home</a></td>
                    <td><input type="search" placeholder="Search..." size="45"></td>      
                    <td align="center" class="logo-cell">
                    <img src="da9cbd3c-384f-4191-b633-f761fbc19138.png" alt="StudentHub Logo" class="studenthub-logo">
                    </td>  
                    <td>
                    <a href="registration.html">Account Creation</a>
                    </td>
                    <td ><a href="Myprofile.html">Profile</a></td>          
                    <td ><a href="setting.html">Settings</a></td>
            </tr>
        </table>
        <table width="100%" border="1">
            <tr align="center">
                     
                    <td> <a href="dashboard.html">Dashboard</a></td>
                    <td><a href="Events.html">Events</a></td>
                    <td><a href="MyCourse.html">My Courses</a></td>
                    <td><a href="about.html">About</a></td>
                    <td><a href="faq.html">FAQs</a></td>
            </tr>
        </table>
        <hr>
    </header>

    <main>

        <h2 align="center">Welcome to StudentHub Dashboard</h2>
        <table border="1" width="100%" cellpadding="10">
            <tr>
                <th>Quick Links</th>
                <th>My Courses</th>
                <th>Upcoming Events</th>
            </tr>

            <tr>
                <td>
                    <ul>
                        <li><a href="attendance.html">Attendance</a></li>
                        <li><a href="assignment.html">Assignments</a></li>
                        <li><a href="registration.html">Registration</a></li>
                        <li><a href="feedback.html">Feedback</a></li>
                    </ul>

                </td>
                <td>
                    <ul>

                        <li>Web Technology</li>
                        <li>Java Programming</li>
                        <li>Data Structures</li>
                        <li>Computer Networks</li>
                        <li>Operating System</li>

                    </ul>

                </td>
                <td>

                    <ul>
                        <li>Hackathon - 20 Aug 2026</li>
                        <li>Workshop on AI - 25 Aug 2026</li>
                        <li>Sports Week - 30 Aug 2026</li>
                        <li>Cultural Fest - 10 Sept 2026</li>
                        <li></li>
                    </ul>
                </td>
            </tr>

        </table>
        <br>
        <table border="1" width="100%" cellpadding="10">
            <tr>
                <th>Recent Announcements</th>
            </tr>

            <tr>
                <td>
                    <marquee direction="left">

                        Assignment Submission Last Date : 18 August |
                        Internal Exam starts from 25 August |
                        Register for Coding Competition before 20 August.
                    </marquee>
                </td>
            </tr>
        </table>

        <br>
        <table border="1" width="100%" cellpadding="10">
            <tr>
                <th colspan="3">Student Progress</th>
            </tr>

            <tr>
                <td align="center">

                    Progress

                    <br><br>
                    <progress value="70" max="100"></progress>
                </td>

                <td align="center">Semester

                    <br>
                    <p>Semester </p>
                </td>
            </tr>
        </table>
    </main>

    <hr>

    <footer>

        <table width="100%">
            <tr>
                <td>
                    <h4>Contact Us</h4>
                    <p>Email : studenthub@gmail.com</p>
                    <p>Phone : +91 9876543210</p>

                </td>
                <td align="center">
                    <h4>Follow Us</h4>
                    <a href="#">Facebook</a> |
                    <a href="#">Instagram</a> |
                    <a href="#">LinkedIn</a>
                </td>
            </tr>
        </table>
        <p align="center">
           &copy 2026 StudentHub Portal
        </p>
    </footer>
     <script src="script.js"></script>
</body>

</html>