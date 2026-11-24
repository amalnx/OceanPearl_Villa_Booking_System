<?php
require_once "../config/database.php";

// Get room ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$room_id = (int) $_GET['id'];

// Retrieve room details from database
$stmt = $pdo->prepare(
    "SELECT * FROM rooms WHERE room_id = ? AND room_status = 'available'"
);

$stmt->execute([$room_id]);
$room = $stmt->fetch();

// Redirect if room does not exist
if (!$room) {
    header("Location: ../index.php");
    exit;
}

// Handle room image
$image = $room['image'] ?: 'default-room.jpg';

$image_path = filter_var($image, FILTER_VALIDATE_URL)
    ? $image
    : '../assets/images/' . $image;

// Convert facilities into a list
$facilities = preg_split('/[,;]+/', $room['facilities'] ?? '');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?php echo htmlspecialchars($room['room_name']); ?> | Ocean Pearl Villa
    </title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">

    <style>
        .room-details {
            max-width: 1100px;
            margin: 60px auto;
            padding: 20px;
        }

        .room-details-container {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 45px;
            align-items: start;
        }

        .room-details-image {
            width: 100%;
            height: 430px;
            object-fit: cover;
            border-radius: 12px;
        }

        .room-details-info h1 {
            font-family: 'Playfair Display', serif;
            font-size: 38px;
            margin-bottom: 15px;
            color: #174b59;
        }

        .room-details-info p {
            line-height: 1.8;
            color: #555;
            margin-bottom: 15px;
        }

        .room-price {
            font-size: 28px;
            font-weight: bold;
            color: #c6a46a;
            margin: 25px 0;
        }

        .room-meta {
            margin: 20px 0;
            line-height: 2;
        }

        .facilities-list {
            padding-left: 20px;
            line-height: 2;
            color: #555;
        }

        .book-room-btn {
            display: inline-block;
            margin-top: 25px;
            padding: 14px 30px;
            background: #174b59;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: 0.3s;
        }

        .book-room-btn:hover {
            background: #c6a46a;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 25px;
            color: #174b59;
            text-decoration: none;
        }

        @media (max-width: 768px) {
            .room-details-container {
                grid-template-columns: 1fr;
            }

            .room-details-image {
                height: 280px;
            }

            .room-details-info h1 {
                font-size: 30px;
            }
        }
    </style>
</head>

<body>

    <section class="room-details">

        <a href="../index.php" class="back-link">
            ← Back to Home
        </a>

        <div class="room-details-container">

            <div>
                <img
                    src="<?php echo htmlspecialchars($image_path); ?>"
                    alt="<?php echo htmlspecialchars($room['room_name']); ?>"
                    class="room-details-image"
                >
            </div>

            <div class="room-details-info">

                <h1>
                    <?php echo htmlspecialchars($room['room_name']); ?>
                </h1>

                <p>
                    <?php echo htmlspecialchars($room['description']); ?>
                </p>

                <div class="room-price">
                    LKR <?php echo number_format($room['price_per_night']); ?>
                    <small style="font-size: 14px; color: #777;">
                        / per night
                    </small>
                </div>

                <div class="room-meta">

                    <p>
                        <strong>Room Type:</strong>
                        <?php echo htmlspecialchars($room['room_type']); ?>
                    </p>

                    <p>
                        <strong>Maximum Guests:</strong>
                        <?php echo htmlspecialchars($room['max_guests']); ?>
                    </p>

                </div>

                <h3>Room Facilities</h3>

                <ul class="facilities-list">

                    <?php foreach ($facilities as $facility): ?>

                        <?php if (trim($facility) !== ''): ?>

                            <li>
                                <?php echo htmlspecialchars(trim($facility)); ?>
                            </li>

                        <?php endif; ?>

                    <?php endforeach; ?>

                </ul>

                <a href="../bookings/availability.php?room_id=<?php echo $room['room_id']; ?>"
                   class="book-room-btn">
                    Book This Room
                </a>

            </div>

        </div>

    </section>

</body>
</html>