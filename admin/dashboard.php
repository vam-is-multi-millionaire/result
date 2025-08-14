<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit();
}
include '../includes/db.php';

// Handle student deletion
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    // First, get the filename to delete the file from server
    $sql = "SELECT result_file FROM students WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if($row = $result->fetch_assoc()){
        if(!empty($row['result_file']) && file_exists("../uploads/" . $row['result_file'])){
            unlink("../uploads/" . $row['result_file']);
        }
    }
    $stmt->close();

    // Then, delete the record from database
    $sql = "DELETE FROM students WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header('Location: dashboard.php?message=Student deleted successfully');
    exit();
}

// Handle CSV Upload
if(isset($_POST['upload_csv'])){
    if(is_uploaded_file($_FILES['csv_file']['tmp_name'])){
        $file = fopen($_FILES['csv_file']['tmp_name'], "r");
        fgetcsv($file); // Skip header row

        $sql = "INSERT INTO students (name, dob, class, roll_number, marks) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        while(($data = fgetcsv($file, 1000, ",")) !== FALSE){
            $stmt->bind_param("sssss", $data[0], $data[1], $data[2], $data[3], $data[4]);
            $stmt->execute();
        }
        $stmt->close();
        fclose($file);
        header('Location: dashboard.php?message=CSV imported successfully');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header class="dashboard-header">
        <div class="container">
            <h1>Admin Dashboard</h1>
            <a href="logout.php" class="btn btn-logout">Logout</a>
        </div>
    </header>

    <main class="container">
        <?php if(isset($_GET['message'])): ?>
            <div class="message-banner">
                <p><?php echo htmlspecialchars($_GET['message']); ?></p>
            </div>
        <?php endif; ?>

        <section class="dashboard-actions card">
            <div class="action-item">
                <h2>Add Student</h2>
                <p>Manually add a new student record.</p>
                <a href="add_student.php" class="btn btn-primary">Add New Student</a>
            </div>
            <div class="action-item">
                <h2>Bulk Upload</h2>
                <p>Upload a CSV file to add multiple students at once.</p>
                <form action="dashboard.php" method="post" enctype="multipart/form-data" class="csv-upload-form">
                    <input type="file" name="csv_file" id="csv_file" accept=".csv" required>
                    <label for="csv_file" class="btn btn-secondary">Choose File</label>
                    <button type="submit" name="upload_csv" class="btn btn-primary">Upload CSV</button>
                </form>
                <p class="csv-info"><small>Format: Name, DOB (YYYY-MM-DD), Class, Roll, Marks</small></p>
            </div>
        </section>

        <section class="student-list-container card">
            <h2>Current Students</h2>
            <div class="table-responsive">
                <table class="student-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Date of Birth</th>
                            <th>Class</th>
                            <th>Roll Number</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM students ORDER BY id DESC";
                        $result = $conn->query($sql);
                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td data-label='Name'>" . htmlspecialchars($row['name']) . "</td>";
                                echo "<td data-label='DOB'>" . htmlspecialchars($row['dob']) . "</td>";
                                echo "<td data-label='Class'>" . htmlspecialchars($row['class']) . "</td>";
                                echo "<td data-label='Roll No'>" . htmlspecialchars($row['roll_number']) . "</td>";
                                echo "<td data-label='Actions' class='actions'>";
                                echo "<a href='edit_student.php?id=" . $row['id'] . "' class='btn btn-edit'>Edit</a>";
                                echo "<a href='dashboard.php?delete=" . $row['id'] . "' class='btn btn-delete' onclick='return confirm(\"Are you sure you want to delete this student? This action cannot be undone.\")'>Delete</a>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5'>No students found. Add one to get started.</td></tr>";
                        }
                        $conn->close();
                        ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>