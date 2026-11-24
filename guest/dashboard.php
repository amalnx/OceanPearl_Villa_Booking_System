
<?php
session_start();

require_once "../config/database.php";

// Check whether the user is logged in as a guest
if (
    !isset($_SESSION["user_id"]) ||
    $_SESSION["role"] !== "guest"
) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$user_name = $_SESSION["name"];

// Retrieve guest booking statistics
$stmt = $pdo->prepare(
    "SELECT
        COUNT(*) AS total_bookings,
        SUM(CASE WHEN booking_status = 'confirmed' THEN 1 ELSE 0 END) AS confirmed_bookings,
        SUM(CASE WHEN booking_status = 'pending' THEN 1 ELSE 0 END) AS pending_bookings
     FROM bookings
     WHERE user_id = ?"
);

$stmt->execute([$user_id]);
$stats = $stmt->fetch();

$total_bookings = $stats["total_bookings"] ?? 0;
$confirmed_bookings = $stats["confirmed_bookings"] ?? 0;
$pending_bookings = $stats["pending_bookings"] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Dashboard | Ocean Pearl Villa</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">

    <style>

        body {
            margin: 0;
            background: #f6f2eb;
            font-family: 'DM Sans', sans-serif;
        }

        .dashboard-header {
            background: #174b59;
            color: white;
            padding: 25px 7%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .dashboard-logo {
            font-family: 'Playfair Display', serif;
            font-size: 24px;
        }

        .dashboard-header a {
            color: white;
            text-decoration: none;
        }

        .logout-button {
            padding: 10px 20px;
            border: 1px solid white;
            border-radius: 5px;
        }

        .dashboard-container {
            max-width: 1100px;
            margin: 60px auto;
            padding: 20px;
        }

        .welcome-section {
            margin-bottom: 40px;
        }

        .welcome-section h1 {
            font-family: 'Playfair Display', serif;
            color: #174b59;
            font-size: 38px;
        }

        .welcome-section p {
            color: #777;
            line-height: 1.7;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 45px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.04);
        }

        .stat-card h3 {
            color: #777;
            font-size: 15px;
            font-weight: 500;
        }

        .stat-card .number {
            color: #174b59;
            font-size: 32px;
            font-weight: bold;
        }

        .dashboard-actions h2 {
            font-family: 'Playfair Display', serif;
            color: #174b59;
            margin-bottom: 25px;
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .action-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            text-decoration: none;
            color: #555;
            transition: 0.3s;
            border: 1px solid #eee;
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        .action-card span {
            font-size: 30px;
        }

        .action-card h3 {
            color: #174b59;
            font-family: 'Playfair Display', serif;
        }

        .dashboard-footer {
            text-align: center;
            padding: 25px;
            color: #777;
        }

        @media (max-width: 650px) {

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .actions-grid {
                grid-template-columns: 1fr;
            }

            .welcome-section h1 {
                font-size: 30px;
            }

            .dashboard-header {
                padding: 20px;
            }

        }

    </style>

</head>

<body>

<!-- HEADER -->

<header class="dashboard-header">

    <div class="dashboard-logo">
        OCEAN PEARL VILLA
    </div>

    <a href="../auth/logout.php" class="logout-button">
        LOGOUT
    </a>

</header>


<!-- DASHBOARD -->

<main class="dashboard-container">

    <section class="welcome-section">

        <p class="section-label">GUEST DASHBOARD</p>

        <h1>
            Welcome, <?php echo htmlspecialchars($user_name); ?>!
        </h1>

        <p>
            We're delighted to have you at Ocean Pearl Villa.
            Manage your reservations and plan your next coastal escape.
        </p>

    </section>


    <!-- BOOKING STATISTICS -->

    <section class="stats-grid">

        <div class="stat-card">

            <h3>Total Bookings</h3>

            <div class="number">
                <?php echo (int)$total_bookings; ?>
            </div>

        </div>


        <div class="stat-card">

            <h3>Confirmed Bookings</h3>

            <div class="number">
                <?php echo (int)$confirmed_bookings; ?>
            </div>

        </div>


        <div class="stat-card">

            <h3>Pending Bookings</h3>

            <div class="number">
                <?php echo (int)$pending_bookings; ?>
            </div>

        </div>

    </section>


    <!-- DASHBOARD ACTIONS -->

    <section class="dashboard-actions">

        <h2>Manage Your Stay</h2>

        <div class="actions-grid">

            <a href="../index.php#rooms" class="action-card">

                <span>🏝️</span>

                <h3>Explore Rooms</h3>

                <p>
                    Discover our accommodation options
                    and find your perfect room.
                </p>

            </a>


            <a href="../bookings/availability.php" class="action-card">

                <span>📅</span>

                <h3>Check Availability</h3>

                <p>
                    Search for available rooms
                    for your preferred dates.
                </p>

            </a>


            <a href="../bookings/my_bookings.php" class="action-card">

                <span>🧾</span>

                <h3>My Bookings</h3>

                <p>
                    View your reservations
                    and check their current status.
                </p>

            </a>


            <a href="../auth/logout.php" class="action-card">

                <span>🚪</span>

                <h3>Logout</h3>

                <p>
                    Securely sign out of your account.
                </p>

            </a>

        </div>

    </section>

</main>


<footer class="dashboard-footer">

    © <?php echo date("Y"); ?> Ocean Pearl Villa.
    Where the Ocean Meets Luxury.

</footer>

</body>
</html>
