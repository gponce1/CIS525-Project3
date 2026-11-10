
<?php

session_start();

require_once __DIR__ . '/../src/storage.php';
require_once __DIR__ . '/../src/helpers.php';

header('Content-Type: text/html; charset=UTF-8');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Method not allowed.');
}

// Verify the form security token
if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('Invalid form submission.');
}

// Read submitted values
$action = $_POST['action'] ?? '';
$id = $_POST['id'] ?? '';

// Validate submitted action and task ID
if (
    !is_string($action) ||
    !in_array($action, ['complete', 'delete'], true) ||
    !is_string($id) ||
    trim($id) === ''
) {
    http_response_code(400);
    exit('Invalid task action.');
}

// Process the requested action
try {

    if ($action === 'complete') {
        $success = completeTask($id);

        $_SESSION['flash'] = $success
            ? 'Task completed successfully!'
            : 'Task not found.';
    }

    if ($action === 'delete') {
        $success = deleteTask($id);

        $_SESSION['flash'] = $success
            ? 'Task deleted successfully!'
            : 'Task not found.';
    }

} catch (RuntimeException $e) {
    $_SESSION['flash'] = 'Unable to update tasks. Please try again.';
}

// Redirect to the task list (PRG pattern)
header('Location: index.php', true, 303);
exit;
