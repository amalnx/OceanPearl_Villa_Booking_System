
<?php
session_start();

require_once "../config/database.php";

// Allow only administrators
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../auth/login.php");
    exit;
}

// Get dashboard statistics
$totalRooms = $pdo->query("
    SELECT COUNT(*) FROM rooms
")->fetchColumn();

$availableRooms = $pdo->query("
    SELECT COUNT(*) FROM rooms
    WHERE room_status = 'available'
")->fetchColumn();

$totalBookings = $pdo->query("
    SELECT COUNT(*) FROM bookings
")->fetchColumn();

$pendingBookings = $pdo->query("
    SELECT COUNT(*) FROM bookings
    WHERE booking_status = 'pending'
")->fetchColumn();

$confirmedBookings = $pdo->query("
    SELECT COUNT(*) FROM bookings
    WHERE booking_status = 'confirmed'
")->fetchColumn();

$totalGuests = $pdo->query("
    SELECT COUNT(*) FROM users
    WHERE role = 'guest'
")->fetchColumn();

// Retrieve recent bookings
$stmt = $pdo->query("
    SELECT
        b.booking_id,
        b.check_in,
        b.check_out,
        b.total_amount,
        b.booking_status,
        u.name AS guest_name,
        r.room_name
    FROM bookings b
    INNER JOIN users u ON b.user_id = u.user_id
    INNER JOIN rooms r ON b.room_id = r.room_id
    ORDER BY b.booking_date DESC
    LIMIT 5
");

$recentBookings = $stmt->fetchAll();

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard | Ocean Pearl Villa</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <style>

        body {
            background: #f6f2eb;
        }

        .admin-header {
            background: #103943;
            color: white;
            padding: 25px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .admin-header h2 {
            font-family: 'Playfair Display', serif;
            margin: 0;
        }

        .admin-header a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
        }

        .admin-header a:hover {
            color: #c6a46a;
        }

        .admin-container {
            max-width: 1200px;
            margin: 45px auto;
            padding: 20px;
        }

        .welcome-section {
            margin-bottom: 35px;
        }

        .welcome-section h1 {
            color: #174b59;
            font-family: 'Playfair Display', serif;
            font-size: 34px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            border-left: 5px solid #c6a46a;
        }

        .stat-card h3 {
            color: #777;
            font-size: 15px;
            margin-bottom: 12px;
        }

        .stat-card p {
            color: #174b59;
            font-size: 32px;
            font-weight: bold;
            margin: 0;
        }

        .admin-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .action-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            text-decoration: none;
            color: #174b59;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            transition: 0.3s;
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        .action-card h3 {
            margin-top: 0;
            font-size: 20px;
        }

        .action-card p {
            color: #777;
            line-height: 1.6;
        }

        .recent-section {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            overflow-x: auto;
        }

        .recent-section h2 {
            color: #174b59;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 650px;
        }

        th, td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #f6f2eb;
            color: #174b59;
        }

        .status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: capitalize;
        }

        .pending {
            background: #fff0c2;
            color: #856404;
        }

        .confirmed {
            background: #d4edda;
            color: #155724;
        }

        .cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .empty-message {
            text-align: center;
            padding: 25px;
            color: #777;
        }

        @media (max-width: 600px) {
            .admin-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .admin-header a {
                margin: 0 12px 0 0;
            }
        }

    </style>

</head>

<body>

<header class="admin-header">

    <h2>Ocean Pearl Villa | Admin</h2>

    <nav>
        <a href="../index.php">View Website</a>
        <a href="../auth/logout.php">Logout</a>
    </nav>

</header>


<main class="admin-container">

    <section class="welcome-section">

        <h1>Welcome, <?php echo e($_SESSION["name"]); ?>!</h1>

        <p>Manage your villa operations from one place.</p>

    </section>


    <!-- Dashboard Statistics -->

    <section class="stats-grid">

        <div class="stat-card">
            <h3>Total Rooms</h3>
            <p><?php echo (int)$totalRooms; ?></p>
        </div>

        <div class="stat-card">
            <h3>Available Rooms</h3>
            <p><?php echo (int)$availableRooms; ?></p>
        </div>

        <div class="stat-card">
            <h3>Total Bookings</h3>
            <p><?php echo (int)$totalBookings; ?></p>
        </div>

        <div class="stat-card">
            <h3>Pending Bookings</h3>
            <p><?php echo (int)$pendingBookings; ?></p>
        </div>

        <div class="stat-card">
            <h3>Confirmed Bookings</h3>
            <p><?php echo (int)$confirmedBookings; ?></p>
        </div>

        <div class="stat-card">
            <h3>Registered Guests</h3>
            <p><?php echo (int)$totalGuests; ?></p>
        </div>

    </section>


    <!-- Admin Management Options -->

    <h2 style="color:#174b59; margin-bottom:20px;">
        Management
    </h2>

    <section class="admin-actions">

        <a href="rooms.php" class="action-card">

            <h3>Manage Rooms</h3>

            <p>Add new rooms, update room details, change prices and manage room availability.</p>

        </a>


        <a href="bookings.php" class="action-card">

            <h3>Manage Bookings</h3>

            <p>View guest reservations and confirm or cancel pending bookings.</p>

        </a>


        <a href="../index.php" class="action-card">

            <h3>View Website</h3>

            <p>Visit the public Ocean Pearl Villa website and explore the guest experience.</p>

        </a>

    </section>


    <!-- Recent Bookings -->

    <section class="recent-section">

        <h2>Recent Bookings</h2>

        <?php if (count($recentBookings) > 0): ?>

            <table>

                <thead>

                    <tr>
                        <th>Booking ID</th>
                        <th>Guest</th>
                        <th>Room</th>
                        <th>Check-in</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($recentBookings as $booking): ?>

                        <tr>

                            <td>#<?php echo (int)$booking["booking_id"]; ?></td>

                            <td><?php echo e($booking["guest_name"]); ?></td>

                            <td><?php echo e($booking["room_name"]); ?></td>

                            <td><?php echo e($booking["check_in"]); ?></td>

                            <td>
                                LKR <?php echo number_format((float)$booking["total_amount"], 2); ?>
                            </td>

                            <td>

                                <span class="status <?php echo e($booking["booking_status"]); ?>">
                                    <?php echo e($booking["booking_status"]); ?>
                                </span>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        <?php else: ?>

            <div class="empty-message">
                No bookings have been made yet.
            </div>

        <?php endif; ?>

    </section>

</main>

</body>
</html>
