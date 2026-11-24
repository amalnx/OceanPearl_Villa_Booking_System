
<?php
session_start();
require_once "../config/database.php";

// Admin access control
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../auth/login.php");
    exit;
}

// CSRF token
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

$message = "";
$messageType = "";

// Handle booking actions
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (
        !isset($_POST["csrf_token"]) ||
        !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])
    ) {
        die("Invalid request.");
    }

    $bookingId = filter_input(INPUT_POST, "booking_id", FILTER_VALIDATE_INT);
    $action = $_POST["action"] ?? "";

    if (!$bookingId) {
        $message = "Invalid booking ID.";
        $messageType = "error";
    } else {

        try {

            if ($action === "confirm") {

                $pdo->beginTransaction();

                // Get booking details
                $stmt = $pdo->prepare(
                    "SELECT * FROM bookings
                     WHERE booking_id = ? AND booking_status = 'pending'"
                );
                $stmt->execute([$bookingId]);
                $booking = $stmt->fetch();

                if (!$booking) {
                    throw new Exception("Booking not found or already processed.");
                }

                // Lock the room while checking availability
                $lock = $pdo->prepare(
                    "SELECT room_id FROM rooms WHERE room_id = ? FOR UPDATE"
                );
                $lock->execute([$booking["room_id"]]);

                // Check overlapping confirmed bookings
                $check = $pdo->prepare(
                    "SELECT COUNT(*) FROM bookings
                     WHERE room_id = ?
                     AND booking_status = 'confirmed'
                     AND booking_id <> ?
                     AND check_in < ?
                     AND check_out > ?"
                );

                $check->execute([
                    $booking["room_id"],
                    $bookingId,
                    $booking["check_out"],
                    $booking["check_in"]
                ]);

                if ($check->fetchColumn() > 0) {
                    throw new Exception(
                        "Cannot confirm! This room already has a confirmed booking during these dates."
                    );
                }

                // Confirm booking
                $update = $pdo->prepare(
                    "UPDATE bookings
                     SET booking_status = 'confirmed'
                     WHERE booking_id = ? AND booking_status = 'pending'"
                );
                $update->execute([$bookingId]);

                $pdo->commit();

                $message = "Booking confirmed successfully!";
                $messageType = "success";

            } elseif ($action === "cancel") {

                $stmt = $pdo->prepare(
                    "UPDATE bookings
                     SET booking_status = 'cancelled'
                     WHERE booking_id = ?
                     AND booking_status IN ('pending', 'confirmed')"
                );

                $stmt->execute([$bookingId]);

                if ($stmt->rowCount() > 0) {
                    $message = "Booking cancelled successfully!";
                    $messageType = "success";
                } else {
                    $message = "Booking could not be cancelled.";
                    $messageType = "error";
                }

            }

        } catch (Exception $ex) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $message = $ex->getMessage();
            $messageType = "error";
        }
    }
}

// Get all bookings
$stmt = $pdo->query(
    "SELECT b.*, 
            u.name AS guest_name,
            u.email AS guest_email,
            u.contact_number,
            r.room_name,
            r.room_type
     FROM bookings b
     INNER JOIN users u ON b.user_id = u.user_id
     INNER JOIN rooms r ON b.room_id = r.room_id
     ORDER BY b.booking_date DESC"
);

$bookings = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings | Ocean Pearl Villa</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        .admin-bookings {
            max-width: 1250px;
            margin: 50px auto;
            padding: 20px;
        }

        .admin-bookings h1 {
            color: #174b59;
            margin-bottom: 10px;
        }

        .booking-table-container {
            overflow-x: auto;
            background: white;
            border-radius: 12px;
            padding: 15px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.08);
            margin-top: 25px;
        }

        .booking-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1050px;
        }

        .booking-table th,
        .booking-table td {
            padding: 13px 12px;
            border-bottom: 1px solid #eee;
            text-align: left;
            font-size: 14px;
        }

        .booking-table th {
            background: #174b59;
            color: white;
        }

        .booking-table tr:hover {
            background: #faf7f1;
        }

        .status {
            padding: 6px 11px;
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

        .action-form {
            display: inline-block;
            margin: 3px;
        }

        .action-btn {
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            font-weight: bold;
        }

        .confirm-btn {
            background: #198754;
            color: white;
        }

        .cancel-btn {
            background: #dc3545;
            color: white;
        }

        .message {
            padding: 14px;
            border-radius: 7px;
            margin: 20px 0;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #174b59;
            text-decoration: none;
            font-weight: bold;
        }

        .empty-message {
            text-align: center;
            padding: 35px;
            color: #777;
        }
    </style>
</head>

<body>

<div class="admin-bookings">

    <h1>Manage Bookings</h1>
    <p>View and manage all guest reservations at Ocean Pearl Villa.</p>

    <a href="dashboard.php" class="back-link">← Back to Dashboard</a>

    <?php if ($message): ?>
        <div class="message <?php echo $messageType === 'success' ? 'success-message' : 'error-message'; ?>">
            <?php echo e($message); ?>
        </div>
    <?php endif; ?>

    <div class="booking-table-container">

        <?php if (count($bookings) > 0): ?>

            <table class="booking-table">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Guest</th>
                        <th>Room</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Guests</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                <?php foreach ($bookings as $booking): ?>

                    <tr>
                        <td>#<?php echo e($booking["booking_id"]); ?></td>

                        <td>
                            <strong><?php echo e($booking["guest_name"]); ?></strong><br>
                            <?php echo e($booking["guest_email"]); ?><br>
                            <?php echo e($booking["contact_number"]); ?>
                        </td>

                        <td>
                            <?php echo e($booking["room_name"]); ?><br>
                            <small><?php echo e($booking["room_type"]); ?></small>
                        </td>

                        <td><?php echo e($booking["check_in"]); ?></td>

                        <td><?php echo e($booking["check_out"]); ?></td>

                        <td><?php echo e($booking["guests"]); ?></td>

                        <td>
                            LKR <?php echo number_format((float)$booking["total_amount"], 2); ?>
                        </td>

                        <td>
                            <span class="status <?php echo e($booking["booking_status"]); ?>">
                                <?php echo e($booking["booking_status"]); ?>
                            </span>
                        </td>

                        <td>

                            <?php if ($booking["booking_status"] === "pending"): ?>

                                <form method="POST" class="action-form">
                                    <input type="hidden" name="csrf_token"
                                           value="<?php echo e($_SESSION["csrf_token"]); ?>">
                                    <input type="hidden" name="booking_id"
                                           value="<?php echo (int)$booking["booking_id"]; ?>">
                                    <input type="hidden" name="action" value="confirm">

                                    <button type="submit" class="action-btn confirm-btn"
                                            onclick="return confirm('Confirm this booking?')">
                                        Confirm
                                    </button>
                                </form>

                            <?php endif; ?>

                            <?php if (in_array($booking["booking_status"], ["pending", "confirmed"], true)): ?>

                                <form method="POST" class="action-form">
                                    <input type="hidden" name="csrf_token"
                                           value="<?php echo e($_SESSION["csrf_token"]); ?>">
                                    <input type="hidden" name="booking_id"
                                           value="<?php echo (int)$booking["booking_id"]; ?>">
                                    <input type="hidden" name="action" value="cancel">

                                    <button type="submit" class="action-btn cancel-btn"
                                            onclick="return confirm('Cancel this booking?')">
                                        Cancel
                                    </button>
                                </form>

                            <?php else: ?>

                                <small>No actions</small>

                            <?php endif; ?>

                        </td>
                    </tr>

                <?php endforeach; ?>

                </tbody>
            </table>

        <?php else: ?>

            <div class="empty-message">
                <h3>No bookings yet</h3>
                <p>Guest reservations will appear here.</p>
            </div>

        <?php endif; ?>

    </div>

</div>

</body>
</html>
