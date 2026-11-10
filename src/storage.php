
<?php

// Location of the JSON file
define('TASKS_FILE', __DIR__ . '/../data/tasks.json');

// Read all tasks from the JSON file
function loadTasks() {
    if (!file_exists(TASKS_FILE)) {
        return [];
    }

    $json = file_get_contents(TASKS_FILE);

    if ($json === false) {
        throw new RuntimeException('Could not read tasks file.');
    }

    $tasks = json_decode($json, true);

    if (!is_array($tasks)) {
        throw new RuntimeException('Invalid tasks JSON.');
    }

    return $tasks;
}

// Save tasks to the JSON file
function saveTasks($tasks) {
    $json = json_encode($tasks, JSON_PRETTY_PRINT);

    if ($json === false) {
        throw new RuntimeException('Could not encode tasks.');
    }

    if (file_put_contents(TASKS_FILE, $json, LOCK_EX) === false) {
        throw new RuntimeException('Could not save tasks.');
    }
}

// Add a new task
function addTask($task) {
    $tasks = loadTasks();
    $tasks[] = $task;
    saveTasks($tasks);
}


 // Mark a task as completed
function completeTask($id) {
    $tasks = loadTasks();

    foreach ($tasks as $index => $task) {
        if ($task['id'] === $id) {
            $tasks[$index]['completed'] = true;
            saveTasks($tasks);
            return true;
        }
    }

    return false;
}

// Delete a task
function deleteTask($id) {
    $tasks = loadTasks();

    foreach ($tasks as $index => $task) {
        if ($task['id'] === $id) {
            array_splice($tasks, $index, 1);
            saveTasks($tasks);
            return true;
        }
    }

    return false;
}
