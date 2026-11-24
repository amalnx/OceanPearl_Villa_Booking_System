
<?php
session_start();

require_once "../config/database.php";

$error = "";

$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if (empty($email) || empty($password)) {

        $error = "Please enter your email and password.";

    } else {

        // Find user by email
        $stmt = $pdo->prepare(
            "SELECT * FROM users WHERE email = ?"
        );

        $stmt->execute([$email]);

        $user = $stmt->fetch();

        // Verify password
        if ($user && password_verify($password, $user["password"])) {

            // Regenerate session ID for security
            session_regenerate_id(true);

            // Store user information in session
            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["name"] = $user["name"];
            $_SESSION["email"] = $user["email"];
            $_SESSION["role"] = $user["role"];

            // Redirect based on user role
            if ($user["role"] === "admin") {

                header("Location: ../admin/dashboard.php");
                exit;

            } else {

                header("Location: ../guest/dashboard.php");
                exit;

            }

        } else {

            $error = "Invalid email address or password.";

        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | Ocean Pearl Villa</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">

    <style>

        body {
            background: #f6f2eb;
            font-family: 'DM Sans', sans-serif;
            margin: 0;
        }

        .login-container {
            max-width: 450px;
            margin: 80px auto;
            padding: 40px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
        }

        .login-heading {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-heading h1 {
            font-family: 'Playfair Display', serif;
            color: #174b59;
            font-size: 36px;
        }

        .login-heading p {
            color: #777;
        }

        .form-group {
            margin-bottom: 22px;
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

        .login-button {
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

        .login-button:hover {
            background: #c6a46a;
        }

        .error-message {
            background: #ffe6e6;
            color: #a00000;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }

        .login-footer {
            text-align: center;
            margin-top: 25px;
            color: #666;
        }

        .login-footer a {
            color: #174b59;
            font-weight: 600;
            text-decoration: none;
        }

        .back-home {
            display: block;
            text-align: center;
            margin-top: 25px;
            color: #174b59;
            text-decoration: none;
        }

        @media (max-width: 500px) {

            .login-container {
                margin: 30px 15px;
                padding: 25px;
            }

        }

    </style>

</head>

<body>

<div class="login-container">

    <div class="login-heading">

        <p class="section-label">WELCOME BACK</p>

        <h1>Guest Login</h1>

        <p>Sign in to continue your journey with us.</p>

    </div>


    <?php if ($error): ?>

        <div class="error-message">
            <?php echo htmlspecialchars($error); ?>
        </div>

    <?php endif; ?>


    <form method="POST" action="">

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

            <label>Password</label>

            <input
                type="password"
                name="password"
                placeholder="Enter your password"
                required
            >

        </div>


        <button type="submit" class="login-button">
            LOGIN
        </button>

    </form>


    <div class="login-footer">

        Don't have an account?

        <a href="register.php">Register</a>

    </div>


    <a href="../index.php" class="back-home">
        ← Back to Home
    </a>

</div>

</body>
</html>
