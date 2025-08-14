<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit();
}
include '../includes/db.php';

if (isset($_POST['add_student'])) {
    $name = $_POST['name'];
    $dob = $_POST['dob'];
    $class = $_POST['class'];
    $roll_number = $_POST['roll_number'];
    $marks = $_POST['marks'];
    $result_file = '';

    if (isset($_FILES['result_file']) && $_FILES['result_file']['error'] == 0) {
        $target_dir = "../uploads/";
        $result_file = time() . '_' . basename($_FILES["result_file"]["name"]);
        $target_file = $target_dir . $result_file;
        move_uploaded_file($_FILES["result_file"]["tmp_name"], $target_file);
    }

    $sql = "INSERT INTO students (name, dob, class, roll_number, marks, result_file) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $name, $dob, $class, $roll_number, $marks, $result_file);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    header('Location: dashboard.php?message=Student added successfully');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header class="dashboard-header">
        <div class="container">
            <h1>Add New Student</h1>
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </header>

    <main class="container">
        <div class="form-container card">
            <form action="add_student.php" method="post" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="input-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="input-group">
                        <label for="dob">Date of Birth</label>
                        <input type="date" id="dob" name="dob" required>
                    </div>
                    <div class="input-group">
                        <label for="class">Class</label>
                        <input type="text" id="class" name="class" required>
                    </div>
                    <div class="input-group">
                        <label for="roll_number">Roll Number</label>
                        <input type="text" id="roll_number" name="roll_number" required>
                    </div>
                    <div class="input-group full-width">
                        <label for="marks">Marks (optional)</label>
                        <textarea id="marks" name="marks" rows="5"></textarea>
                    </div>
                    <div class="input-group full-width">
                        <label for="result_file">Result File (PDF/Image, optional)</label>
                        <input type="file" id="result_file" name="result_file">
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" name="add_student" class="btn btn-primary">Add Student</button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
