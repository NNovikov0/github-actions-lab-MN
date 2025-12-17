<?php
// ================================
// Database configuration
// ================================
// Read from environment variables (set on the VM via Apache)
$host     = getenv('DB_HOST') ?: 'localhost';
$dbname   = getenv('DB_NAME') ?: '88327';
$username = getenv('DB_USER') ?: 'app_user';

try {
    // No password (socket / local auth)
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// ================================
// Handle form submissions
// ================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {

            case 'add':
                $title = trim($_POST['title']);
                $description = trim($_POST['description']);

                $stmt = $pdo->prepare(
                    "INSERT INTO tasks (title, description) VALUES (?, ?)"
                );
                $stmt->execute([$title, $description]);

                header("Location: " . $_SERVER['PHP_SELF']);
                exit;

            case 'complete':
                $id = (int) $_POST['id'];

                $stmt = $pdo->prepare(
                    "UPDATE tasks SET completed = 1 WHERE id = ?"
                );
                $stmt->execute([$id]);

                header("Location: " . $_SERVER['PHP_SELF']);
                exit;

            case 'delete':
                $id = (int) $_POST['id'];

                $stmt = $pdo->prepare(
                    "DELETE FROM tasks WHERE id = ?"
                );
                $stmt->execute([$id]);

                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
        }
    }
}

// ================================
// Fetch all tasks
// ================================
$stmt = $pdo->query(
    "SELECT * FROM tasks ORDER BY created_at DESC"
);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Manager - UWB Lab</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }

        h1 {
            color: #333;
            margin-bottom: 10px;
            text-align: center;
        }

        .db-info {
            text-align: center;
            color: #666;
            font-size: 14px;
            margin-bottom: 30px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .form-group { margin-bottom: 15px; }

        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }

        input[type="text"], textarea {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        button {
            background: #667eea;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .tasks { margin-top: 40px; }

        .task {
            background: #f8f9fa;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .task.completed {
            opacity: 0.6;
            border-left-color: #28a745;
        }

        .task.completed h3 {
            text-decoration: line-through;
        }

        .task-actions {
            display: flex;
            gap: 10px;
        }

        .btn-complete { background: #28a745; }
        .btn-delete { background: #dc3545; }
    </style>
</head>
<body>
<div class="container">
    <h1>Task Manager</h1>

    <div class="db-info">
        Connected to: <strong><?= htmlspecialchars($host) ?></strong> |
        Database: <strong><?= htmlspecialchars($dbname) ?></strong> |
        User: <strong><?= htmlspecialchars($username) ?></strong>
    </div>

    <form method="POST">
        <input type="hidden" name="action" value="add">

        <div class="form-group">
            <label for="title">Task Title</label>
            <input type="text" id="title" name="title" required>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" required></textarea>
        </div>

        <button type="submit">Add Task</button>
    </form>

    <div class="tasks">
        <h2>Your Tasks (<?= count($tasks) ?>)</h2>

        <?php foreach ($tasks as $task): ?>
            <div class="task <?= $task['completed'] ? 'completed' : '' ?>">
                <div>
                    <h3><?= htmlspecialchars($task['title']) ?></h3>
                    <p><?= htmlspecialchars($task['description']) ?></p>
                </div>

                <div class="task-actions">
                    <?php if (!$task['completed']): ?>
                        <form method="POST">
                            <input type="hidden" name="action" value="complete">
                            <input type="hidden" name="id" value="<?= $task['id'] ?>">
                            <button class="btn-complete">✓</button>
                        </form>
                    <?php endif; ?>

                    <form method="POST">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $task['id'] ?>">
                        <button class="btn-delete">✗</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
</html>
