<?php
session_start();

require_once "../config/database.php";

// Check whether the guest is logged in
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "guest") {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$message = "";
$message_type = "";

// Handle booking cancellation
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["cancel_booking"])) {

    $booking_id = filter_input(INPUT_POST, "booking_id", FILTER_VALIDATE_INT);

    if ($booking_id) {

        $stmt = $pdo->prepare("
            UPDATE bookings
            SET booking_status = 'cancelled'
            WHERE booking_id = ?
            AND user_id = ?
            AND booking_status = 'pending'
        ");

        $stmt->execute([$booking_id, $user_id]);

        if ($stmt->rowCount() > 0) {
            $message = "Your booking has been cancelled successfully.";
            $message_type = "success";
        } else {
            $message = "Unable to cancel this booking. It may already be confirmed or cancelled.";
            $message_type = "error";
        }

    } else {
        $message = "Invalid booking selected.";
        $message_type = "error";
    }
}

// Retrieve all bookings belonging to this guest
$stmt = $pdo->prepare("
    SELECT 
        b.*,
        r.room_name,
        r.room_type,
        r.image
    FROM bookings b
    INNER JOIN rooms r ON b.room_id = r.room_id
    WHERE b.user_id = ?
    ORDER BY b.booking_date DESC
");

$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll();

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Bookings | Ocean Pearl Villa</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        .bookings-container {
            max-width: 1100px;
            margin: 60px auto;
            padding: 20px;
        }

        .bookings-heading {
            text-align: center;
            margin-bottom: 35px;
        }

        .bookings-heading h1 {
            font-family: 'Playfair Display', serif;
            color: #174b59;
            font-size: 36px;
        }

        .booking-card {
            display: flex;
            gap: 25px;
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            align-items: center;
            flex-wrap: wrap;
        }

        .booking-card img {
            width: 220px;
            height: 170px;
            object-fit: cover;
            border-radius: 10px;
        }

        .booking-details {
            flex: 1;
        }

        .booking-details h2 {
            color: #174b59;
            margin-bottom: 12px;
        }

        .booking-details p {
            margin: 8px 0;
        }

        .status {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
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

        .cancel-button {
            background: #b63b3b;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 12px;
        }

        .cancel-button:hover {
            background: #922d2d;
        }

        .message {
            padding: 14px;
            margin-bottom: 25px;
            border-radius: 6px;
            text-align: center;
        }

        .success {
            background: #d4edda;
            color: #155724;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
        }

        .empty-bookings {
            text-align: center;
            padding: 50px 20px;
            background: white;
            border-radius: 12px;
        }

        .empty-bookings a {
            display: inline-block;
            margin-top: 20px;
            background: #174b59;
            color: white;
            padding: 12px 25px;
            border-radius: 6px;
            text-decoration: none;
        }

        @media (max-width: 600px) {
            .booking-card {
                flex-direction: column;
                align-items: flex-start;
            }

            .booking-card img {
                width: 100%;
            }
        }
    </style>

</head>

<body>

<div class="bookings-container">

    <div class="bookings-heading">
        <h1>My Bookings</h1>
        <p>Manage and view your Ocean Pearl Villa reservations.</p>
    </div>

    <?php if ($message): ?>
        <div class="message <?php echo e($message_type); ?>">
            <?php echo e($message); ?>
        </div>
    <?php endif; ?>

    <?php if (count($bookings) > 0): ?>

        <?php foreach ($bookings as $booking): ?>

            <div class="booking-card">

                <img
                    src="<?php
                        $image = $booking["image"] ?: "default-room.jpg";

                        echo e(
                            filter_var($image, FILTER_VALIDATE_URL)
                                ? $image
                                : "../assets/images/" . $image
                        );
                    ?>"
                    alt="<?php echo e($booking["room_name"]); ?>"
                >

                <div class="booking-details">

                    <h2><?php echo e($booking["room_name"]); ?></h2>

                    <p>
                        <strong>Booking ID:</strong>
                        #<?php echo e($booking["booking_id"]); ?>
                    </p>

                    <p>
                        <strong>Room Type:</strong>
                        <?php echo e($booking["room_type"]); ?>
                    </p>

                    <p>
                        <strong>Check-in:</strong>
                        <?php echo e($booking["check_in"]); ?>
                    </p>

                    <p>
                        <strong>Check-out:</strong>
                        <?php echo e($booking["check_out"]); ?>
                    </p>

                    <p>
                        <strong>Guests:</strong>
                        <?php echo e($booking["guests"]); ?>
                    </p>

                    <p>
                        <strong>Total Amount:</strong>
                        LKR <?php echo number_format((float)$booking["total_amount"], 2); ?>
                    </p>

                    <p>
                        <strong>Status:</strong>

                        <span class="status <?php echo e($booking["booking_status"]); ?>">
                            <?php echo e($booking["booking_status"]); ?>
                        </span>
                    </p>

                    <?php if ($booking["booking_status"] === "pending"): ?>

                        <form method="POST"
                              onsubmit="return confirm('Are you sure you want to cancel this booking?');">

                            <input
                                type="hidden"
                                name="booking_id"
                                value="<?php echo (int)$booking["booking_id"]; ?>"
                            >

                            <button
                                type="submit"
                                name="cancel_booking"
                                class="cancel-button"
                            >
                                Cancel Booking
                            </button>

                        </form>

                    <?php endif; ?>

                </div>

            </div>

        <?php endforeach; ?>

    <?php else: ?>

        <div class="empty-bookings">

            <h2>You haven't made any bookings yet.</h2>

            <p>Explore our beautiful rooms and plan your next getaway!</p>

            <a href="../index.php#rooms">Explore Rooms</a>

        </div>

    <?php endif; ?>

    <div style="text-align:center; margin-top:30px;">
        <a href="../guest/dashboard.php" style="color:#174b59;">
            ← Back to Dashboard
        </a>
    </div>

</div>

</body>
</html>
