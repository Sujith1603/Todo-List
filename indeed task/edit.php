<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$task_id = $_GET['id'];

// Fetch the task to be edited
$stmt = $conn->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $task_id, $user_id);
$stmt->execute();
$task = $stmt->get_result()->fetch_assoc();

if (!$task) {
    echo "Task not found!";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $due_date = $_POST['due_date'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE tasks SET task = ?, description = ?, category = ?, due_date = ?, status = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("sssssii", $title, $description, $category, $due_date, $status, $task_id, $user_id);
    
    if ($stmt->execute()) {
        header('Location: index.php');
        exit;
    } else {
        echo "Error updating task.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Task</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Edit Task</h2>
        <form method="POST">
            <label for="title">Title:</label>
            <input type="text" name="title" value="<?= htmlspecialchars($task['task']) ?>" required>
            <label for="description">Description:</label>
            <textarea name="description"><?= htmlspecialchars($task['description']) ?></textarea>
            <label for="category">Category:</label>
            <select name="category">
                <option value="Work" <?= $task['category'] == 'Work' ? 'selected' : '' ?>>Work</option>
                <option value="Personal" <?= $task['category'] == 'Personal' ? 'selected' : '' ?>>Personal</option>
           
