<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_task'])) {
        // Add new task
        $title = $_POST['title'];
        $description = $_POST['description'];
        $category = $_POST['category'];
        $due_date = $_POST['due_date'];
        $status = $_POST['status'];

        $stmt = $conn->prepare("INSERT INTO tasks (user_id, task, description, category, due_date, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $user_id, $title, $description, $category, $due_date, $status);
        $stmt->execute();
    } elseif (isset($_POST['edit_task'])) {
        // Edit task
        $task_id = $_POST['task_id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $category = $_POST['category'];
        $due_date = $_POST['due_date'];
        $status = $_POST['status'];

        $stmt = $conn->prepare("UPDATE tasks SET task = ?, description = ?, category = ?, due_date = ?, status = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sssssii", $title, $description, $category, $due_date, $status, $task_id, $user_id);
        $stmt->execute();
    } elseif (isset($_GET['delete'])) {
        // Delete task
        $task_id = $_GET['delete'];
        $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $task_id, $user_id);
        $stmt->execute();
    }
}

// Filter tasks by category or status
$filter_category = $_GET['category'] ?? '';
$filter_status = $_GET['status'] ?? '';

$query = "SELECT * FROM tasks WHERE user_id = ? ";
$params = [$user_id];
$types = "i";

if ($filter_category) {
    $query .= "AND category = ? ";
    $params[] = $filter_category;
    $types .= "s";
}

if ($filter_status) {
    $query .= "AND status = ? ";
    $params[] = $filter_status;
    $types .= "s";
}

$query .= "ORDER BY due_date ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$tasks = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Sujith To-Do List</h2>
        
        <!-- Task Filters -->
        <form method="GET">
            <label for="category">Filter by Category:</label>
            <select name="category">
                <option value="">All</option>
                <option value="Work" <?= $filter_category == 'Work' ? 'selected' : '' ?>>Work</option>
                <option value="Personal" <?= $filter_category == 'Personal' ? 'selected' : '' ?>>Personal</option>
            </select>
            <label for="status">Filter by Status:</label>
            <select name="status">
                <option value="">All</option>
                <option value="Pending" <?= $filter_status == 'Pending' ? 'selected' : '' ?>>Pending</option>
                <option value="In Progress" <?= $filter_status == 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                <option value="Completed" <?= $filter_status == 'Completed' ? 'selected' : '' ?>>Completed</option>
            </select>
            <button type="submit">Filter</button>
        </form>

        <!-- Add Task Form -->
        <h3>Add New Task</h3>
        <form method="POST">
            <label for="title">Title:</label>
            <input type="text" name="title" required>
            <label for="description">Description:</label>
            <textarea name="description"></textarea>
            <label for="category">Category:</label>
            <select name="category">
                <option value="Work">Work</option>
                <option value="Personal">Personal</option>
            </select>
            <label for="due_date">Due Date:</label>
            <input type="date" name="due_date" required>
            <label for="status">Status:</label>
            <select name="status">
                <option value="Pending">Pending</option>
                <option value="In Progress">In Progress</option>
                <option value="Completed">Completed</option>
            </select>
            <button type="submit" name="add_task">Add Task</button>
        </form>

        <!-- Task List Display -->
        <h3>Your Tasks</h3>
        <ul>
            <?php while ($task = $tasks->fetch_assoc()): ?>
                <li>
                    <strong><?= htmlspecialchars($task['task']) ?></strong> - <?= htmlspecialchars($task['description']) ?><br>
                    Category: <?= htmlspecialchars($task['category']) ?> | Due: <?= htmlspecialchars($task['due_date']) ?> | Status: <?= htmlspecialchars($task['status']) ?>
                    <a href="edit.php?id=<?= $task['id'] ?>">Edit</a> | 
                    <a href="index.php?delete=<?= $task['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                </li>
            <?php endwhile; ?>
        </ul>

        <a href="logout.php">Logout</a>
    </div>
</body>
</html>
