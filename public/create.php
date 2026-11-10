
<?php

session_start();

require_once __DIR__ . '/../src/validation.php';
require_once __DIR__ . '/../src/storage.php';
require_once __DIR__ . '/../src/helpers.php';

header('Content-Type: text/html; charset=UTF-8');

$errors = [];

$values = [
    'title' => '',
    'description' => '',
    'priority' => 'Medium',
    'due' => ''
];

// Process submitted form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $result = validateTask($_POST);

    $errors = $result['errors'];
    $values = $result['values'];

    // Verify the security token
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $errors['general'] = 'Invalid form submission. Please try again.';
    }

    // Save only when validation is successful
    if (empty($errors)) {

        $task = [
            'id' => uniqid('task_', true),
            'title' => $values['title'],
            'description' => $values['description'],
            'priority' => $values['priority'],
            'due' => $values['due'],
            'completed' => false
        ];

        try {
            addTask($task);

            $_SESSION['flash'] = 'Task created successfully!';

            // PRG: Redirect after a successful POST
            header('Location: index.php', true, 303);
            exit;

        } catch (RuntimeException $e) {
            $errors['general'] = 'Could not save the task. Please try again.';
        }
    }

} elseif ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET, POST');
    http_response_code(405);
    exit('Method not allowed.');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Task - TaskPad PHP</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<header>
    <h1>TaskPad PHP</h1>
    <p>A simple task management application</p>
</header>


<aside class="sidebar">
    <p class="sidebar-heading">WORKSPACE</p>

    <nav aria-label="Main navigation">
        <a href="index.php" class="nav-link">My Tasks</a>
        <a href="create.php" class="nav-link active"
           aria-current="page">Add Task</a>
    </nav>
</aside>


<main>
    <section>
        <h2>Create New Task</h2>
        <p>Fill out the form below to add a new task.</p>

        <?php if (isset($errors['general'])): ?>
            <p role="alert">
                <?= escapeHtml($errors['general']) ?>
            </p>
        <?php endif; ?>

        <form action="create.php" method="POST" novalidate>

            <input
                type="hidden"
                name="csrf_token"
                value="<?= escapeHtml(csrfToken()) ?>"
            >

            <div>
                <label for="title">Task Title *</label>

                <input
                    type="text"
                    id="title"
                    name="title"
                    maxlength="100"
                    required
                    value="<?= escapeHtml($values['title']) ?>"
                >

                <?php if (isset($errors['title'])): ?>
                    <p role="alert">
                        <?= escapeHtml($errors['title']) ?>
                    </p>
                <?php endif; ?>
            </div>

            <div>
                <label for="description">Description</label>

                <textarea
                    id="description"
                    name="description"
                    rows="4"
                    maxlength="500"
                ><?= escapeHtml($values['description']) ?></textarea>

                <?php if (isset($errors['description'])): ?>
                    <p role="alert">
                        <?= escapeHtml($errors['description']) ?>
                    </p>
                <?php endif; ?>
            </div>

            <div>
                <label for="priority">Priority</label>

                <select id="priority" name="priority">
                    <option value="Low"
                        <?= $values['priority'] === 'Low' ? 'selected' : '' ?>>
                        Low
                    </option>
                    <option value="Medium"
                        <?= $values['priority'] === 'Medium' ? 'selected' : '' ?>>
                        Medium
                    </option>
                    <option value="High"
                        <?= $values['priority'] === 'High' ? 'selected' : '' ?>>
                        High
                    </option>
                </select>

                <?php if (isset($errors['priority'])): ?>
                    <p role="alert">
                        <?= escapeHtml($errors['priority']) ?>
                    </p>
                <?php endif; ?>
            </div>

            <div>
                <label for="due">Due Date</label>

                <input
                    type="date"
                    id="due"
                    name="due"
                    value="<?= escapeHtml($values['due'] ?? '') ?>"
                >

                <?php if (isset($errors['due'])): ?>
                    <p role="alert">
                        <?= escapeHtml($errors['due']) ?>
                    </p>
                <?php endif; ?>
            </div>

            <div>
                <button type="submit">Add Task</button>
                <a href="index.php">Cancel</a>
            </div>

        </form>
    </section>
</main>

<footer>
    <p>CIS 525 - Project 3</p>
</footer>

</body>
</html>
