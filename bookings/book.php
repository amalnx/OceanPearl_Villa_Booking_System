<?php
session_start();

require_once "../config/database.php";

// Only logged-in guests can make bookings
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "guest") {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

$message = "";
$message_type = "";

// Get booking information from the URL
$room_id = filter_input(INPUT_GET, "room_id", FILTER_VALIDATE_INT);

$check_in = $_POST["check_in"] ?? $_GET["check_in"] ?? "";
$check_out = $_POST["check_out"] ?? $_GET["check_out"] ?? "";
$guests = $_POST["guests"] ?? $_GET["guests"] ?? 1;

// Validate room ID
if (!$room_id) {
    die("Invalid room selected. Please go back and select a room.");
}

// Retrieve room details
$stmt = $pdo->prepare("
    SELECT *
    FROM rooms
    WHERE room_id = ?
    AND room_status = 'available'
");

$stmt->execute([$room_id]);
$room = $stmt->fetch();

if (!$room) {
    die("Sorry, this room is unavailable.");
}

// Process booking submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $guests = filter_var($guests, FILTER_VALIDATE_INT);

    // Validate required information
    if (empty($check_in) || empty($check_out) || !$guests) {

        $message = "Please enter all booking details correctly.";
        $message_type = "error";

    } else {

        // Validate date formats
        $start_date = DateTime::createFromFormat('!Y-m-d', $check_in);
        $end_date = DateTime::createFromFormat('!Y-m-d', $check_out);

        $valid_start = $start_date && $start_date->format('Y-m-d') === $check_in;
        $valid_end = $end_date && $end_date->format('Y-m-d') === $check_out;

        if (!$valid_start || !$valid_end) {

            $message = "Please enter valid booking dates.";
            $message_type = "error";

        } elseif (
            $check_in < date("Y-m-d") ||
            $check_out <= $check_in
        ) {

            $message = "Please select valid check-in and check-out dates.";
            $message_type = "error";

        } elseif ($guests < 1 || $guests > (int)$room["max_guests"]) {

            $message = "The selected room can accommodate a maximum of "
                . (int)$room["max_guests"] . " guests.";
            $message_type = "error";

        } else {

            // Check for overlapping confirmed bookings
            $stmt = $pdo->prepare("
                SELECT COUNT(*)
                FROM bookings
                WHERE room_id = ?
                AND booking_status = 'confirmed'
                AND check_in < ?
                AND check_out > ?
            ");

            $stmt->execute([
                $room_id,
                $check_out,
                $check_in
            ]);

            $overlapping = $stmt->fetchColumn();

            if ($overlapping > 0) {

                $message = "Sorry, this room is already booked for those dates.";
                $message_type = "error";

            } else {

                // Calculate total price using the database price
                $nights = (int)$start_date->diff($end_date)->days;

                $total_amount = $nights * (float)$room["price_per_night"];

                // Save booking as pending
                $stmt = $pdo->prepare("
                    INSERT INTO bookings
                    (
                        user_id,
                        room_id,
                        check_in,
                        check_out,
                        guests,
                        total_amount,
                        booking_status
                    )
                    VALUES (?, ?, ?, ?, ?, ?, 'pending')
                ");

                $stmt->execute([
                    $user_id,
                    $room_id,
                    $check_in,
                    $check_out,
                    $guests,
                    $total_amount
                ]);

                $booking_id = $pdo->lastInsertId();

                // Redirect to My Bookings
                header("Location: my_bookings.php?success=1");
                exit;
            }
        }
    }
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$today = date("Y-m-d");
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Book Your Stay | Ocean Pearl Villa</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        .booking-container {
            max-width: 850px;
            margin: 60px auto;
            padding: 20px;
        }

        .booking-box {
            background: white;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        .booking-heading {
            text-align: center;
            margin-bottom: 30px;
        }

        .booking-heading h1 {
            color: #174b59;
            font-family: 'Playfair Display', serif;
            font-size: 34px;
        }

        .room-summary {
            background: #f6f2eb;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
        }

        .room-summary h2 {
            color: #174b59;
            margin-bottom: 10px;
        }

        .booking-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .booking-form input {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 6px;
            box-sizing: border-box;
        }

        .total-box {
            background: #f6f2eb;
            padding: 18px;
            border-radius: 8px;
            margin-bottom: 20px;
            color: #174b59;
        }

        .book-button {
            width: 100%;
            padding: 14px;
            background: #174b59;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }

        .book-button:hover {
            background: #103943;
        }

        .message {
            padding: 14px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 25px;
            color: #174b59;
        }
    </style>

</head>

<body>

<div class="booking-container">

    <div class="booking-box">

        <div class="booking-heading">
            <h1>Reserve Your Stay</h1>
            <p>Complete your reservation at Ocean Pearl Villa.</p>
        </div>

        <div class="room-summary">

            <h2><?php echo e($room["room_name"]); ?></h2>

            <p>
                <strong>Room Type:</strong>
                <?php echo e($room["room_type"]); ?>
            </p>

            <p>
                <strong>Price per Night:</strong>
                LKR <?php echo number_format((float)$room["price_per_night"], 2); ?>
            </p>

            <p>
                <strong>Maximum Guests:</strong>
                <?php echo (int)$room["max_guests"]; ?>
            </p>

        </div>

        <?php if ($message): ?>

            <div class="message <?php echo e($message_type); ?>">
                <?php echo e($message); ?>
            </div>

        <?php endif; ?>

        <form method="POST" class="booking-form">

            <label>Check-in Date</label>

            <input
                type="date"
                name="check_in"
                min="<?php echo e($today); ?>"
                value="<?php echo e($check_in); ?>"
                required
            >

            <label>Check-out Date</label>

            <input
                type="date"
                name="check_out"
                min="<?php echo e($today); ?>"
                value="<?php echo e($check_out); ?>"
                required
            >

            <label>Number of Guests</label>

            <input
                type="number"
                name="guests"
                min="1"
                max="<?php echo (int)$room["max_guests"]; ?>"
                value="<?php echo e($guests); ?>"
                required
            >

            <div class="total-box">
                <strong>Price per Night:</strong>
                LKR <?php echo number_format((float)$room["price_per_night"], 2); ?>

                <p id="totalPrice">
                    Select your dates to calculate the estimated total.
                </p>
            </div>

            <button type="submit" class="book-button">
                CONFIRM BOOKING
            </button>

        </form>

        <a href="../index.php#rooms" class="back-link">
            ← Back to Rooms
        </a>

    </div>

</div>

<script>
const checkIn = document.querySelector('input[name="check_in"]');
const checkOut = document.querySelector('input[name="check_out"]');
const totalPrice = document.getElementById('totalPrice');

const pricePerNight = <?php echo json_encode((float)$room["price_per_night"]); ?>;

function calculateTotal() {

    if (checkIn.value && checkOut.value) {

        const start = new Date(checkIn.value + "T00:00:00");
        const end = new Date(checkOut.value + "T00:00:00");

        const nights = Math.round((end - start) / 86400000);

        if (nights > 0) {

            const total = nights * pricePerNight;

            totalPrice.textContent =
                nights + " night(s) × LKR " +
                pricePerNight.toLocaleString() +
                " = LKR " + total.toLocaleString();

        } else {

            totalPrice.textContent =
                "Check-out must be after check-in.";

        }
    }
}

checkIn.addEventListener("change", function () {

    checkOut.min = checkIn.value;

    if (checkOut.value && checkOut.value <= checkIn.value) {
        checkOut.value = "";
    }

    calculateTotal();
});

checkOut.addEventListener("change", calculateTotal);

calculateTotal();
</script>

</body>
</html>
