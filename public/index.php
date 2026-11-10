
<?php

session_start();

require_once __DIR__ . '/../src/storage.php';
require_once __DIR__ . '/../src/helpers.php';

header('Content-Type: text/html; charset=UTF-8');

// Load tasks from JSON
$storageError = '';

try {
    $tasks = loadTasks();
} catch (RuntimeException $e) {
    $tasks = [];
    $storageError = 'Unable to load tasks. Please try again.';
}

// Read GET filters safely
$q = isset($_GET['q']) && is_string($_GET['q'])
    ? trim($_GET['q']) : '';

$priority = isset($_GET['priority']) && is_string($_GET['priority'])
    ? $_GET['priority'] : '';

// Validate priority filter
$allowedPriorities = ['Low', 'Medium', 'High'];

if (!in_array($priority, $allowedPriorities, true)) {
    $priority = '';
}

 // Read the status filter from the URL
$status = isset($_GET['status']) && is_string($_GET['status'])
    ? $_GET['status']
    : 'all';

// Only allow valid status values
if (!in_array($status, ['all', 'open', 'completed'], true)) {
    $status = 'all';
}


// Calculate task counts
$totalTasks = count($tasks);
$openTasks = 0;
$completedTasks = 0;

foreach ($tasks as $task) {
    if ($task['completed']) {
        $completedTasks++;
    } else {
        $openTasks++;
    }
}

// Filter tasks by text, priority and status
$filteredTasks = [];

foreach ($tasks as $task) {

    $matchesText = $q === ''
        || stripos($task['title'], $q) !== false
        || stripos($task['description'], $q) !== false;

    $matchesPriority = $priority === ''
        || $task['priority'] === $priority;

    
    $matchesStatus = $status === 'all'
        || ($status === 'open' && !$task['completed'])
        || ($status === 'completed' && $task['completed']);

    if ($matchesText && $matchesPriority && $matchesStatus) {
        $filteredTasks[] = $task;
    }

}

// Read and clear the flash message
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskPad PHP - My Tasks</title>
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
        <a href="index.php" class="nav-link active"
           aria-current="page">My Tasks</a>
        <a href="create.php" class="nav-link">Add Task</a>
    </nav>
</aside>


<main>

    <section>
        <h2>My Tasks</h2>
        <a href="create.php">+ Add New Task</a>
    </section>

    <!-- Success message -->
    <?php if ($flash !== ''): ?>
        <p class="success-message" role="status">
            <?= escapeHtml($flash) ?>
        </p>
    <?php endif; ?>

    <?php if ($storageError !== ''): ?>
        <p class="error-message" role="alert">
            <?= escapeHtml($storageError) ?>
        </p>
    <?php else: ?>

    <!-- Task counters -->
    <section class="task-summary">
        <h2>Task Summary</h2>

        <div>
            <p>Total Tasks: <?= $totalTasks ?></p>
            <p>Open: <?= $openTasks ?></p>
            <p>Completed: <?= $completedTasks ?></p>
        </div>
    </section>

    <!-- Search and filter -->
    <section class="task-filters">
        <h2>Filter Tasks</h2>

        <form action="index.php" method="GET">

            <div>
                <label for="q">Search Tasks</label>
                <input
                    type="search"
                    id="q"
                    name="q"
                    placeholder="Search by title or description"
                    value="<?= escapeHtml($q) ?>"
                >
            </div>

            <div>
                <label for="priority">Priority</label>

                <select id="priority" name="priority">
                    <option value="">All Priorities</option>

                    <?php foreach ($allowedPriorities as $option): ?>
                        <option
                            value="<?= escapeHtml($option) ?>"
                            <?= $priority === $option ? 'selected' : '' ?>
                        >
                            <?= escapeHtml($option) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <button type="submit">Apply Filters</button>
                <a href="index.php">Clear Filters</a>
            </div>

            <input type="hidden"
                name="status"
                value="<?= escapeHtml($status) ?>">

        </form>
    </section>
   
    
<!-- Task List -->
    <section class="task-list">

        <div class="task-list-heading">
            <h2>Task List</h2>
            <p>
                Showing <?= count($filteredTasks) ?>
                of <?= $totalTasks ?> tasks
            </p>
        </div>

        <!-- Status filters -->
        <nav class="status-tabs" aria-label="Filter by status">

            <a href="index.php?<?= escapeHtml(http_build_query([
                'q' => $q,
                'priority' => $priority,
                'status' => 'all'
            ])) ?>"
            class="<?= $status === 'all' ? 'active' : '' ?>"
            <?= $status === 'all' ? 'aria-current="page"' : '' ?>>
                All <span><?= $totalTasks ?></span>
            </a>

            <a href="index.php?<?= escapeHtml(http_build_query([
                'q' => $q,
                'priority' => $priority,
                'status' => 'open'
            ])) ?>"
            class="<?= $status === 'open' ? 'active' : '' ?>"
            <?= $status === 'open' ? 'aria-current="page"' : '' ?>>
                Open <span><?= $openTasks ?></span>
            </a>

            <a href="index.php?<?= escapeHtml(http_build_query([
                'q' => $q,
                'priority' => $priority,
                'status' => 'completed'
            ])) ?>"
            class="<?= $status === 'completed' ? 'active' : '' ?>"
            <?= $status === 'completed' ? 'aria-current="page"' : '' ?>>
                Completed <span><?= $completedTasks ?></span>
            </a>

        </nav>

        <?php if ($totalTasks === 0): ?>

            <div class="empty-state">
                <h3>No tasks yet</h3>
                <p>Add your first task to get started.</p>
            </div>

        <?php elseif (empty($filteredTasks)): ?>

            <div class="empty-state">
                <h3>No matching tasks found</h3>
                <p>Try a different search or filter.</p>
            </div>

        <?php else: ?>

            <div class="task-table-wrapper">
                <table class="task-table">

                    <thead>
                        <tr>
                            <th scope="col">Task</th>
                            <th scope="col">Priority</th>
                            <th scope="col">Status</th>
                            <th scope="col">Due date</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php foreach ($filteredTasks as $task): ?>

                        <tr class="<?= $task['completed'] ? 'is-done' : '' ?>">

                            <!-- Task title and description -->
                            <td class="c-task">
                                <div class="task-cell">
                                    <div class="task-text">
                                        <div class="task-title-text">
                                            <?= escapeHtml($task['title']) ?>
                                        </div>

                                        <div class="task-desc-text">
                                            <?= escapeHtml($task['description']) ?>
                                        </div>
                                    </div>
                                </div>
                            </td>


                            <!-- Priority -->
                            <td class="c-priority">
                                <span class="priority-indicator priority-<?= strtolower($task['priority']) ?>">
                                    <?= escapeHtml($task['priority']) ?>
                                </span>
                            </td>

                            <!-- Status -->
                            <td class="c-status">
                                <span class="status-pill <?= $task['completed'] ? 'status-done' : 'status-active' ?>">
                                    <?= $task['completed'] ? 'Completed' : 'Open' ?>
                                </span>
                            </td>

                            <!-- Due date -->
                            <td class="c-due">

                                <?php if (!empty($task['due'])): ?>

                                    <?php if ($task['due'] === date('Y-m-d')): ?>
                                        <strong class="due-today">Today</strong>
                                    <?php endif; ?>

                                    <span class="due-date <?= $task['due'] === date('Y-m-d') ? 'due-secondary' : '' ?>">
                                        <?= escapeHtml(date('M j, Y', strtotime($task['due']))) ?>
                                    </span>

                                <?php else: ?>

                                    <span class="due-none">No due date</span>

                                <?php endif; ?>

                            </td>

                            <!-- Actions -->
                            <td class="c-actions">
                                <div class="task-actions">

                                    <?php if (!$task['completed']): ?>

                                        <form action="actions.php" method="POST">
                                            <input type="hidden"
                                                name="csrf_token"
                                                value="<?= escapeHtml(csrfToken()) ?>">

                                            <input type="hidden"
                                                name="id"
                                                value="<?= escapeHtml($task['id']) ?>">

                                            <input type="hidden"
                                                name="action"
                                                value="complete">

                                            <button type="submit"
                                                    class="complete-button">
                                                Complete
                                            </button>
                                        </form>

                                    <?php endif; ?>

                                    <form action="actions.php" method="POST">
                                        <input type="hidden"
                                            name="csrf_token"
                                            value="<?= escapeHtml(csrfToken()) ?>">

                                        <input type="hidden"
                                            name="id"
                                            value="<?= escapeHtml($task['id']) ?>">

                                        <input type="hidden"
                                            name="action"
                                            value="delete">

                                        <button type="submit"
                                                class="delete-button"
                                                aria-label="Delete <?= escapeHtml($task['title']) ?>"
                                                title="Delete task">

                                            <svg width="16"
                                                height="16"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="2"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                aria-hidden="true">
                                                <path d="M4 7h16M10 11v6M14 11v6M6 7l1 12a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-12M9 7V4h6v3"/>
                                            </svg>

                                        </button>
                                    </form>

                                </div>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>
                </table>
            </div>

        <?php endif; ?>

    </section>



    <?php endif; ?>

</main>

<footer>
    <p>CIS 525 - Project 3</p>
</footer>

</body>
</html>
