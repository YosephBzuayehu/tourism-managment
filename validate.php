<?php
function validateUserInput($data) {
    $errors = [];

    // Required fields
    $required_fields = ["firstname", "lastname", "email", "phone", "password", "role"];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            $errors[$field] = "$field is required";
        }
    }

    // Email validation
    if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format";
    }

    // Password validation (minimum 6 characters)
    if (!empty($data['password']) && strlen($data['password']) < 6) {
        $errors['password'] = "Password must be at least 6 characters";
    }

    // Phone validation (10 digits)
    if (!empty($data['phone']) && !preg_match("/^[0-9]{10}$/", $data['phone'])) {
        $errors['phone'] = "Phone must be 10 digits";
    }

    // Role validation
    $valid_roles = ["Visitor","admin","Site Agent","Tourism Expert"];
    if (!empty($data['role']) && !in_array($data['role'], $valid_roles)) {
        $errors['role'] = "Invalid role";
    }

    return $errors;
}
?>
