
<?php

function validateTask($input) {
    $errors = [];

    // Read and trim form values
    $title = trim(is_string($input['title'] ?? null) ? $input['title'] : '');
    $description = trim(is_string($input['description'] ?? null) ? $input['description'] : '');
    $priority = trim(is_string($input['priority'] ?? null) ? $input['priority'] : '');
    $due = trim(is_string($input['due'] ?? null) ? $input['due'] : '');

    // Validate title
    if ($title === '') {
        $errors['title'] = 'Task title is required.';
    } elseif (strlen($title) > 100) {
        $errors['title'] = 'Title must be 100 characters or less.';
    }

    // Validate description
    if (strlen($description) > 500) {
        $errors['description'] = 'Description must be 500 characters or less.';
    }

    // Validate priority
    $allowedPriorities = ['Low', 'Medium', 'High'];

    if (!in_array($priority, $allowedPriorities, true)) {
        $errors['priority'] = 'Please select a valid priority.';
    }

    // Validate optional due date
    if ($due !== '') {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $due)) {
            $errors['due'] = 'Please enter a valid date (YYYY-MM-DD).';
        } else {
            $parts = explode('-', $due);

            if (!checkdate((int)$parts[1], (int)$parts[2], (int)$parts[0])) {
                $errors['due'] = 'Please enter a valid calendar date.';
            }
        }
    }

    return [
        'errors' => $errors,
        'values' => [
            'title' => $title,
            'description' => $description,
            'priority' => $priority,
            'due' => $due === '' ? null : $due
        ]
    ];
}
