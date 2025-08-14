<?php
session_start();

// Handle form submission with Post-Redirect-Get pattern
if (isset($_POST['search'])) {
    include 'includes/db.php';
    
    // Use trim and a case-insensitive LIKE search for flexibility
    $name_param = '%' . trim($_POST['name']) . '%';
    $dob = $_POST['dob'];

    // Using LOWER() on both sides makes the search case-insensitive
    $sql = "SELECT * FROM students WHERE LOWER(name) LIKE LOWER(?) AND dob = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $name_param, $dob);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['search_results'] = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        $_SESSION['no_result'] = true;
    }
    $stmt->close();
    $conn->close();

    // Redirect to the same page to prevent form resubmission
    header('Location: index.php');
    exit();
}

include 'includes/header.php';
?>

<section class="hero">
    <div class="hero-content">
        <h2>Find Your Exam Result</h2>
        <p>Enter your details below to view your result instantly.</p>
    </div>
</section>

<section class="search-section">
    <div class="search-container card">
        <form action="index.php" method="post">
            <div class="form-grid">
                <div class="input-group">
                    <label for="name">Student Name</label>
                    <input type="text" id="name" name="name" placeholder="e.g., John Doe" required>
                </div>
                <div class="input-group">
                    <label for="dob">Date of Birth</label>
                    <input type="date" id="dob" name="dob" required>
                </div>
            </div>
            <button type="submit" name="search" class="btn btn-primary">Search Result</button>
        </form>
    </div>
</section>

<section class="result-section">
    <?php
    // Display results from session
    if (isset($_SESSION['search_results'])) {
        echo '<div class="result-container card">';
        foreach ($_SESSION['search_results'] as $row) {
            echo "<div class='result-card'>";
            echo "<h3>Result for: " . htmlspecialchars($row['name']) . "</h3>";
            echo "<div class='result-details'>";
            echo "<p><strong>Class:</strong> " . htmlspecialchars($row['class']) . "</p>";
            echo "<p><strong>Roll Number:</strong> " . htmlspecialchars($row['roll_number']) . "</p>";
            echo "</div>";
            
            if (!empty($row['marks'])) {
                echo "<h4>Marks Obtained:</h4>";
                echo "<pre class='marks-display'>" . htmlspecialchars($row['marks']) . "</pre>";
            }

            if (!empty($row['result_file'])) {
                echo "<a href='uploads/" . htmlspecialchars($row['result_file']) . "' class='btn btn-download' download>Download Full Result</a>";
            }
            echo "</div>";
        }
        echo '</div>';
        unset($_SESSION['search_results']);
    } elseif (isset($_SESSION['no_result'])) {
        echo "<div class='no-result-message card'><p>No result found for the given details. Please check your input and try again.</p></div>";
        unset($_SESSION['no_result']);
    }
    ?>
</section>

<?php include 'includes/footer.php'; ?>