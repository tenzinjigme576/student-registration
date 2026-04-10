<?php
// index.php
// Student Registration Form (HTML + CSS + PHP + MySQL)
// - Server-side validation
// - Error messages next to each field
// - Preserves user input on validation failure
// - Stores valid data in MySQL (password saved as a hash)

// Helper: safe output to prevent XSS when showing values in HTML
function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Helper: basic input sanitization
function clean_input($value)
{
    // Trim spaces and remove invisible characters
    $value = trim((string)$value);
    $value = str_replace("\0", "", $value);
    return $value;
}

// Default values
$full_name = "";
$email = "";
$phone = "";
$gender = "";
$course = "";
$password = "";
$confirm_password = "";

// Error messages
$errors = [
    "full_name" => "",
    "email" => "",
    "phone" => "",
    "gender" => "",
    "course" => "",
    "password" => "",
    "confirm_password" => ""
];

$success_message = "";

// Course options (as requested)
$course_options = ["BCA", "BSc CS", "BTech CSE", "MCA", "MSc CS"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Collect and sanitize inputs
    $full_name = clean_input($_POST["full_name"] ?? "");
    $email = clean_input($_POST["email"] ?? "");
    $phone = clean_input($_POST["phone"] ?? "");
    $gender = clean_input($_POST["gender"] ?? "");
    $course = clean_input($_POST["course"] ?? "");
    $password = (string)($_POST["password"] ?? "");
    $confirm_password = (string)($_POST["confirm_password"] ?? "");

    // Validate Full Name
    if ($full_name === "") {
        $errors["full_name"] = "Full name is required.";
    } elseif (!preg_match("/^[a-zA-Z ]+$/", $full_name)) {
        $errors["full_name"] = "Only letters and spaces are allowed.";
    }

    // Validate Email
    if ($email === "") {
        $errors["email"] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors["email"] = "Enter a valid email address.";
    }

    // Validate Phone (exactly 10 digits)
    if ($phone === "") {
        $errors["phone"] = "Phone number is required.";
    } elseif (!preg_match("/^[0-9]{10}$/", $phone)) {
        $errors["phone"] = "Phone number must be exactly 10 digits.";
    }

    // Validate Gender
    if ($gender === "") {
        $errors["gender"] = "Please select gender.";
    } elseif (!in_array($gender, ["Male", "Female", "Other"], true)) {
        $errors["gender"] = "Invalid gender selected.";
    }

    // Validate Course
    if ($course === "") {
        $errors["course"] = "Please select a course.";
    } elseif (!in_array($course, $course_options, true)) {
        $errors["course"] = "Invalid course selected.";
    }

    // Validate Password
    if ($password === "") {
        $errors["password"] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors["password"] = "Password must be at least 6 characters.";
    }

    // Validate Confirm Password
    if ($confirm_password === "") {
        $errors["confirm_password"] = "Confirm password is required.";
    } elseif ($confirm_password !== $password) {
        $errors["confirm_password"] = "Passwords do not match.";
    }

    // If no errors, insert into database
    $has_errors = false;
    foreach ($errors as $msg) {
        if ($msg !== "") {
            $has_errors = true;
            break;
        }
    }

    if (!$has_errors) {
        // Connect DB
        require_once __DIR__ . "/db.php";

        // Hash password securely
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Insert using prepared statement
        $sql = "INSERT INTO students (full_name, email, phone, gender, course, password_hash) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            // In lab exams, a simple message is fine
            $success_message = "Error: Could not prepare the database statement.";
        } else {
            $stmt->bind_param("ssssss", $full_name, $email, $phone, $gender, $course, $password_hash);

            if ($stmt->execute()) {
                $success_message = "Registration successful!";

                // Clear form after success
                $full_name = "";
                $email = "";
                $phone = "";
                $gender = "";
                $course = "";
                $password = "";
                $confirm_password = "";
            } else {
                $success_message = "Error: Could not save data. Please try again.";
            }

            $stmt->close();
        }

        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Student Registration Form</title>
    <link rel="stylesheet" href="style.css" />
</head>
<body>
<div class="page">
    <div class="card">
        <h1>Student Registration</h1>

        <?php if ($success_message !== ""): ?>
            <div class="success"><?php echo e($success_message); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="row">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" value="<?php echo e($full_name); ?>" />
                <?php if ($errors["full_name"] !== ""): ?>
                    <div class="error"><?php echo e($errors["full_name"]); ?></div>
                <?php endif; ?>
            </div>

            <div class="row">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo e($email); ?>" />
                <?php if ($errors["email"] !== ""): ?>
                    <div class="error"><?php echo e($errors["email"]); ?></div>
                <?php endif; ?>
            </div>

            <div class="row">
                <label for="phone">Phone Number</label>
                <input type="text" id="phone" name="phone" value="<?php echo e($phone); ?>" />
                <div class="hint">Enter 10-digit number (example: 9876543210)</div>
                <?php if ($errors["phone"] !== ""): ?>
                    <div class="error"><?php echo e($errors["phone"]); ?></div>
                <?php endif; ?>
            </div>

            <div class="row">
                <label>Gender</label>
                <div class="inline">
                    <label class="radio-item">
                        <input type="radio" name="gender" value="Male" <?php echo ($gender === "Male") ? "checked" : ""; ?> />
                        Male
                    </label>
                    <label class="radio-item">
                        <input type="radio" name="gender" value="Female" <?php echo ($gender === "Female") ? "checked" : ""; ?> />
                        Female
                    </label>
                    <label class="radio-item">
                        <input type="radio" name="gender" value="Other" <?php echo ($gender === "Other") ? "checked" : ""; ?> />
                        Other
                    </label>
                </div>
                <?php if ($errors["gender"] !== ""): ?>
                    <div class="error"><?php echo e($errors["gender"]); ?></div>
                <?php endif; ?>
            </div>

            <div class="row">
                <label for="course">Course</label>
                <select id="course" name="course">
                    <option value="">-- Select Course --</option>
                    <?php foreach ($course_options as $opt): ?>
                        <option value="<?php echo e($opt); ?>" <?php echo ($course === $opt) ? "selected" : ""; ?>>
                            <?php echo e($opt); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($errors["course"] !== ""): ?>
                    <div class="error"><?php echo e($errors["course"]); ?></div>
                <?php endif; ?>
            </div>

            <div class="row">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" value="<?php echo e($password); ?>" />
                <?php if ($errors["password"] !== ""): ?>
                    <div class="error"><?php echo e($errors["password"]); ?></div>
                <?php endif; ?>
            </div>

            <div class="row">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" value="<?php echo e($confirm_password); ?>" />
                <?php if ($errors["confirm_password"] !== ""): ?>
                    <div class="error"><?php echo e($errors["confirm_password"]); ?></div>
                <?php endif; ?>
            </div>

            <div class="actions">
                <button type="submit">Register</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>

