<?php
require_once "../config/database.php";

$check_in = "";
$check_out = "";
$guests = 1;
$room_id = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 0;

$rooms = [];
$error = "";
$searched = false;

$today = date("Y-m-d");

// Process availability search
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["search"])) {

    $searched = true;

    $check_in = $_GET["check_in"] ?? "";
    $check_out = $_GET["check_out"] ?? "";
    $guests = filter_var($_GET["guests"] ?? 1, FILTER_VALIDATE_INT);

    // Validate inputs
    if (!$check_in || !$check_out || !$guests || $guests < 1) {

        $error = "Please enter valid booking details.";

    } elseif (
        $check_in < $today ||
        $check_out <= $check_in
    ) {

        $error = "Please select valid check-in and check-out dates.";

    } else {

        // Find rooms without overlapping confirmed bookings
        $sql = "
            SELECT *
            FROM rooms r
            WHERE r.room_status = 'available'
            AND r.max_guests >= :guests

            AND NOT EXISTS (
                SELECT 1
                FROM bookings b
                WHERE b.room_id = r.room_id
                AND b.booking_status = 'confirmed'
                AND b.check_in < :check_out
                AND b.check_out > :check_in
            )
        ";

        // If visitor selected a particular room
        if ($room_id > 0) {
            $sql .= " AND r.room_id = :room_id";
        }

        $sql .= " ORDER BY r.price_per_night ASC";

        $stmt = $pdo->prepare($sql);

        $params = [
            ":guests" => $guests,
            ":check_in" => $check_in,
            ":check_out" => $check_out
        ];

        if ($room_id > 0) {
            $params[":room_id"] = $room_id;
        }

        $stmt->execute($params);

        $rooms = $stmt->fetchAll();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Check Availability | Ocean Pearl Villa</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>

        .availability-section {
            max-width: 1100px;
            margin: 60px auto;
            padding: 20px;
        }

        .availability-heading {
            text-align: center;
            margin-bottom: 35px;
        }

        .availability-heading h1 {
            font-family: 'Playfair Display', serif;
            color: #174b59;
            font-size: 38px;
            margin: 10px 0;
        }

        .availability-form {
            background: #f6f2eb;
            padding: 35px;
            border-radius: 12px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            align-items: end;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: #174b59;
        }

        .form-group input {
            width: 100%;
            padding: 13px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: inherit;
            box-sizing: border-box;
        }

        .search-button {
            background: #174b59;
            color: white;
            border: none;
            padding: 14px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: 0.3s;
        }

        .search-button:hover {
            background: #c6a46a;
        }

        .availability-results {
            margin-top: 50px;
        }

        .availability-results h2 {
            font-family: 'Playfair Display', serif;
            color: #174b59;
            margin-bottom: 25px;
        }

        .available-room {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 25px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid #eee;
            border-radius: 10px;
            align-items: center;
        }

        .available-room img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 8px;
        }

        .available-room h3 {
            color: #174b59;
            font-family: 'Playfair Display', serif;
        }

        .available-room p {
            color: #666;
            line-height: 1.6;
        }

        .available-price {
            color: #c6a46a;
            font-weight: bold;
            font-size: 20px;
        }

        .book-button {
            display: inline-block;
            padding: 12px 25px;
            background: #174b59;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 10px;
        }

        .book-button:hover {
            background: #c6a46a;
        }

        .message {
            padding: 15px;
            margin-top: 20px;
            border-radius: 5px;
        }

        .error-message {
            background: #ffe6e6;
            color: #a00000;
        }

        .no-results {
            background: #f6f2eb;
            padding: 25px;
            border-radius: 8px;
            color: #555;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 25px;
            color: #174b59;
            text-decoration: none;
        }

        @media (max-width: 768px) {

            .availability-form {
                grid-template-columns: 1fr;
            }

            .available-room {
                grid-template-columns: 1fr;
            }

            .available-room img {
                height: 230px;
            }

        }

    </style>

</head>

<body>

<section class="availability-section">

    <a href="../index.php" class="back-link">
        ← Back to Home
    </a>

    <div class="availability-heading">

        <p class="section-label">PLAN YOUR COASTAL ESCAPE</p>

        <h1>Check Room Availability</h1>

        <p>
            Find the perfect room for your stay at Ocean Pearl Villa.
        </p>

    </div>


    <!-- AVAILABILITY SEARCH FORM -->

    <form method="GET" class="availability-form">

        <?php if ($room_id > 0): ?>

            <input type="hidden" name="room_id"
                   value="<?php echo $room_id; ?>">

        <?php endif; ?>

        <div class="form-group">

            <label>Check-in Date</label>

            <input
                type="date"
                name="check_in"
                min="<?php echo $today; ?>"
                value="<?php echo htmlspecialchars($check_in); ?>"
                required
            >

        </div>


        <div class="form-group">

            <label>Check-out Date</label>

            <input
                type="date"
                name="check_out"
                min="<?php echo $today; ?>"
                value="<?php echo htmlspecialchars($check_out); ?>"
                required
            >

        </div>


        <div class="form-group">

            <label>Number of Guests</label>

            <input
                type="number"
                name="guests"
                min="1"
                max="20"
                value="<?php echo htmlspecialchars((string)$guests); ?>"
                required
            >

        </div>


        <button
            type="submit"
            name="search"
            value="1"
            class="search-button"
        >
            SEARCH AVAILABILITY
        </button>

    </form>


    <!-- SEARCH RESULTS -->

    <?php if ($error): ?>

        <div class="message error-message">
            <?php echo htmlspecialchars($error); ?>
        </div>

    <?php endif; ?>


    <?php if ($searched && !$error): ?>

        <div class="availability-results">

            <h2>Available Rooms</h2>

            <p>
                Check-in:
                <?php echo htmlspecialchars($check_in); ?>
                |
                Check-out:
                <?php echo htmlspecialchars($check_out); ?>
                |
                Guests:
                <?php echo (int)$guests; ?>
            </p>


            <?php if (count($rooms) > 0): ?>

                <?php foreach ($rooms as $room): ?>

                    <?php

                    $image = $room['image'] ?: 'default-room.jpg';

                    $image_path = filter_var($image, FILTER_VALIDATE_URL)
                        ? $image
                        : '../assets/images/' . $image;

                    ?>

                    <div class="available-room">

                        <img
                            src="<?php echo htmlspecialchars($image_path); ?>"
                            alt="<?php echo htmlspecialchars($room['room_name']); ?>"
                        >

                        <div>

                            <h3>
                                <?php echo htmlspecialchars($room['room_name']); ?>
                            </h3>

                            <p>
                                <?php echo htmlspecialchars($room['description']); ?>
                            </p>

                            <p>
                                Maximum Guests:
                                <?php echo (int)$room['max_guests']; ?>
                            </p>

                            <p class="available-price">
                                LKR
                                <?php echo number_format($room['price_per_night']); ?>
                                / night
                            </p>

                            <a
                                href="book.php?room_id=<?php echo (int)$room['room_id']; ?>&check_in=<?php echo urlencode($check_in); ?>&check_out=<?php echo urlencode($check_out); ?>&guests=<?php echo (int)$guests; ?>"
                                class="book-button"
                            >
                                BOOK THIS ROOM →
                            </a>

                        </div>

                    </div>

                <?php endforeach; ?>

            <?php else: ?>

                <div class="no-results">

                    <h3>No Rooms Available</h3>

                    <p>
                        Sorry! No rooms are available for your selected
                        dates and number of guests. Please try different dates.
                    </p>

                </div>

            <?php endif; ?>

        </div>

    <?php endif; ?>

</section>

</body>
</html>