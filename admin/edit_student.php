<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit();
}
include '../includes/db.php';

$id = $_GET['id'];
if (!isset($id)) {
    header('Location: dashboard.php');
    exit();
}

// Fetch existing student data
$sql = "SELECT * FROM students WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

if (!$student) {
    header('Location: dashboard.php?message=Student not found');
    exit();
}

if (isset($_POST['update_student'])) {
    $name = $_POST['name'];
    $dob = $_POST['dob'];
    $class = $_POST['class'];
    $roll_number = $_POST['roll_number'];
    $marks = $_POST['marks'];
    $result_file = $student['result_file']; // Keep old file by default

    // Check if a new file is uploaded
    if (isset($_FILES['result_file']) && $_FILES['result_file']['error'] == 0) {
        // Delete old file if it exists
        if (!empty($result_file) && file_exists("../uploads/" . $result_file)) {
            unlink("../uploads/" . $result_file);
        }
        // Upload new file
        $target_dir = "../uploads/";
        $result_file = time() . '_' . basename($_FILES["result_file"]["name"]);
        $target_file = $target_dir . $result_file;
        move_uploaded_file($_FILES["result_file"]["tmp_name"], $target_file);
    }

    $sql = "UPDATE students SET name = ?, dob = ?, class = ?, roll_number = ?, marks = ?, result_file = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $name, $dob, $class, $roll_number, $marks, $result_file, $id);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    header('Location: dashboard.php?message=Student updated successfully');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header class="dashboard-header">
        <div class="container">
            <h1>Edit Student</h1>
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </header>

    <main class="container">
        <div class="form-container card">
            <form action="edit_student.php?id=<?php echo $id; ?>" method="post" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="input-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($student['name']); ?>" required>
                    </div>
                    <div class="input-group">
                        <label for="dob">Date of Birth</label>
                        <input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($student['dob']); ?>" required>
                    </div>
                    <div class="input-group">
                        <label for="class">Class</label>
                        <input type="text" id="class" name="class" value="<?php echo htmlspecialchars($student['class']); ?>" required>
                    </div>
                    <div class="input-group">
                        <label for="roll_number">Roll Number</label>
                        <input type="text" id="roll_number" name="roll_number" value="<?php echo htmlspecialchars($student['roll_number']); ?>" required>
                    </div>
                    <div class="input-group full-width">
                        <label for="marks">Marks (optional)</label>
                        <textarea id="marks" name="marks" rows="5"><?php echo htmlspecialchars($student['marks']); ?></textarea>
                    </div>
                    <div class="input-group full-width">
                        <label for="result_file">Result File (PDF/Image, optional)</label>
                        <input type="file" id="result_file" name="result_file">
                        <?php if ($student['result_file']): ?>
                            <p class="current-file-info"><small>Current file: <a href="../uploads/<?php echo htmlspecialchars($student['result_file']); ?>" target="_blank"><?php echo htmlspecialchars($student['result_file']); ?></a>. Uploading a new file will replace it.</small></p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" name="update_student" class="btn btn-primary">Update Student</button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
