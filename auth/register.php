
<?php
require_once "../config/database.php";

$error = "";
$success = "";

$name = "";
$email = "";
$contact_number = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Retrieve form data
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $contact_number = trim($_POST["contact_number"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    // Validate inputs
    if (
        empty($name) ||
        empty($email) ||
        empty($contact_number) ||
        empty($password) ||
        empty($confirm_password)
    ) {

        $error = "Please fill in all fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } elseif (strlen($password) < 8) {

        $error = "Password must contain at least 8 characters.";

    } elseif ($password !== $confirm_password) {

        $error = "Passwords do not match.";

    } else {

        // Check whether email already exists
        $stmt = $pdo->prepare(
            "SELECT user_id FROM users WHERE email = ?"
        );

        $stmt->execute([$email]);

        if ($stmt->fetch()) {

            $error = "An account with this email already exists.";

        } else {

            // Hash password before saving
            $hashed_password = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            // Register new guest
            $stmt = $pdo->prepare(
                "INSERT INTO users
                (name, email, contact_number, password, role)
                VALUES (?, ?, ?, ?, 'guest')"
            );

            $stmt->execute([
                $name,
                $email,
                $contact_number,
                $hashed_password
            ]);

            $success = "Registration successful! You can now log in.";

            // Clear form values
            $name = "";
            $email = "";
            $contact_number = "";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Guest Registration | Ocean Pearl Villa</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">

    <style>

        body {
            background: #f6f2eb;
            font-family: 'DM Sans', sans-serif;
            margin: 0;
        }

        .register-container {
            max-width: 480px;
            margin: 60px auto;
            padding: 40px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
        }

        .register-heading {
            text-align: center;
            margin-bottom: 30px;
        }

        .register-heading h1 {
            font-family: 'Playfair Display', serif;
            color: #174b59;
            font-size: 34px;
        }

        .register-heading p {
            color: #777;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #174b59;
        }

        .form-group input {
            width: 100%;
            padding: 13px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
            font-family: inherit;
        }

        .form-group input:focus {
            outline: none;
            border-color: #174b59;
        }

        .register-button {
            width: 100%;
            padding: 14px;
            background: #174b59;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }

        .register-button:hover {
            background: #c6a46a;
        }

        .message {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }

        .error-message {
            background: #ffe6e6;
            color: #a00000;
        }

        .success-message {
            background: #e5f5e9;
            color: #176b35;
        }

        .register-footer {
            text-align: center;
            margin-top: 25px;
            color: #666;
        }

        .register-footer a {
            color: #174b59;
            font-weight: 600;
            text-decoration: none;
        }

        .back-home {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #174b59;
            text-decoration: none;
        }

        @media (max-width: 550px) {
            .register-container {
                margin: 25px 15px;
                padding: 25px;
            }
        }

    </style>

</head>

<body>

<div class="register-container">

    <div class="register-heading">

        <p class="section-label">WELCOME TO OCEAN PEARL VILLA</p>

        <h1>Create an Account</h1>

        <p>Join us and begin your coastal escape.</p>

    </div>


    <?php if ($error): ?>

        <div class="message error-message">
            <?php echo htmlspecialchars($error); ?>
        </div>

    <?php endif; ?>


    <?php if ($success): ?>

        <div class="message success-message">

            <?php echo htmlspecialchars($success); ?>

            <br><br>

            <a href="login.php">Login to Your Account</a>

        </div>

    <?php endif; ?>


    <form method="POST" action="">

        <div class="form-group">

            <label>Full Name</label>

            <input
                type="text"
                name="name"
                value="<?php echo htmlspecialchars($name); ?>"
                placeholder="Enter your full name"
                required
            >

        </div>


        <div class="form-group">

            <label>Email Address</label>

            <input
                type="email"
                name="email"
                value="<?php echo htmlspecialchars($email); ?>"
                placeholder="Enter your email"
                required
            >

        </div>


        <div class="form-group">

            <label>Contact Number</label>

            <input
                type="tel"
                name="contact_number"
                value="<?php echo htmlspecialchars($contact_number); ?>"
                placeholder="Enter your contact number"
                required
            >

        </div>


        <div class="form-group">

            <label>Password</label>

            <input
                type="password"
                name="password"
                placeholder="Minimum 8 characters"
                minlength="8"
                required
            >

        </div>


        <div class="form-group">

            <label>Confirm Password</label>

            <input
                type="password"
                name="confirm_password"
                placeholder="Re-enter your password"
                minlength="8"
                required
            >

        </div>


        <button type="submit" class="register-button">
            CREATE ACCOUNT
        </button>

    </form>


    <div class="register-footer">

        Already have an account?

        <a href="login.php">Login</a>

    </div>


    <a href="../index.php" class="back-home">
        ← Back to Home
    </a>

</div>

</body>
</html>
