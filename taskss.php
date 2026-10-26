
<?php
$conn = mysqli_connect("localhost", "root", "");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS student_tasks");
mysqli_select_db($conn, "student_tasks");

$sql = "CREATE TABLE IF NOT EXISTS tasks (
    task_no INT AUTO_INCREMENT PRIMARY KEY,
    student_name VARCHAR(100) NOT NULL,
    task VARCHAR(100) NOT NULL,
    time_taken VARCHAR(50) NOT NULL,
    deadline DATE NOT NULL,
    description TEXT NOT NULL
)";

mysqli_query($conn, $sql);

// Add task
if (isset($_POST['add'])) {
    $student = $_POST['student_name'];
    $task = $_POST['task'];
    $time = $_POST['time_taken'];
    $deadline = $_POST['deadline'];
    $description = $_POST['description'];

    $sql = "INSERT INTO tasks
    (student_name, task, time_taken, deadline, description)
    VALUES (?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param(
        $stmt, "sssss",
        $student, $task, $time, $deadline, $description
    );

    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('Task received successfully!');</script>";
    }

    mysqli_stmt_close($stmt);
}

// Complete task
if (isset($_POST['complete'])) {
    $task_no = (int) $_POST['task_no'];

    $stmt = mysqli_prepare($conn, "DELETE FROM tasks WHERE task_no = ?");
    mysqli_stmt_bind_param($stmt, "i", $task_no);
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_affected_rows($stmt) > 0) {
        echo "<script>alert('Task completed successfully!');</script>";
    } else {
        echo "<script>alert('Task number not found!');</script>";
    }

    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Task Management</title>


<style>
    body {
        font-family: Arial;
        background-color: lightblue;
    }

    h1, h2 {
        color: darkblue;
    }

    .container {
        background-color: white;
        padding: 20px;
        margin: 20px;
    }

    input, textarea {
        padding: 5px;
        margin: 5px;
    }

    input[type="submit"] {
        background-color: lightgreen;
        cursor: pointer;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        border: 1px solid black;
        padding: 8px;
    }

    th {
        background-color: lightgray;
    }
</style>

    <script>
        function welcomeMessage() {
            alert("Welcome to Student Task Management!");
        }

        function validateTaskForm() {
            let student = document.forms["taskForm"]["student_name"].value;
            let task = document.forms["taskForm"]["task"].value;

            if (student.trim() === "" || task.trim() === "") {
                alert("Please enter the student name and task.");
                return false;
            }

            return true;
        }

        function confirmComplete() {
            return confirm("Are you sure you want to complete and delete this task?");
        }
    </script>
</head>

<body onload="welcomeMessage()">

<div class="container">

    <h1>Student Task Management System</h1>

    <h2>Receive Task from Student</h2>

    <form name="taskForm" method="post"
          onsubmit="return validateTaskForm()">

        Student Name:
        <input type="text" name="student_name" required>

        Task:
        <input type="text" name="task" required>

        Time Required:
        <input type="text" name="time_taken"
               placeholder="Example: 2 hours" required>

        Deadline:
        <input type="date" name="deadline" required>

        Description:
        <textarea name="description" rows="3" required></textarea>

        <input type="submit" name="add" value="Receive Task">
    </form>

    <hr>

    <h2>Pending Tasks</h2>

    <?php
    $result = mysqli_query($conn, "SELECT * FROM tasks ORDER BY task_no ASC");

    if (mysqli_num_rows($result) > 0) {
        echo "<table>";
        echo "<tr>
                <th>Task No.</th>
                <th>Student</th>
                <th>Task</th>
                <th>Time Required</th>
                <th>Deadline</th>
                <th>Description</th>
                <th>Status</th>
              </tr>";

        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . $row['task_no'] . "</td>";
            echo "<td>" . htmlspecialchars($row['student_name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['task']) . "</td>";
            echo "<td>" . htmlspecialchars($row['time_taken']) . "</td>";
            echo "<td>" . htmlspecialchars($row['deadline']) . "</td>";
            echo "<td>" . htmlspecialchars($row['description']) . "</td>";
            echo "<td class='status'>Pending</td>";
            echo "</tr>";
        }

        echo "</table>";
    } else {
        echo "<p style='text-align:center'>No pending tasks.</p>";
    }
    ?>

    <hr>

    <h2>Complete a Task</h2>

    <form method="post" onsubmit="return confirmComplete()">
        Enter Task Number:
        <input type="number" name="task_no" min="1" required>

        <input type="submit" name="complete"
               value="Complete Task" class="complete-btn">
    </form>

</div>

</body>
</html>

<?php
mysqli_close($conn);
?>
