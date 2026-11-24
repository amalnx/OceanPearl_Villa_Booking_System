
<?php
session_start();
require_once "config/database.php";

// Retrieve available rooms from database
$stmt = $pdo->query(
    "SELECT * FROM rooms
     WHERE room_status = 'available'
     ORDER BY room_id DESC
     LIMIT 3"
);

$rooms = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Ocean Pearl Villa | Hikkaduwa, Sri Lanka</title>

    <link rel="stylesheet" href="assets/css/style.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        .navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
        }

        .navbar nav {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 22px;
        }

        .navbar nav a {
            text-decoration: none;
        }

        .nav-account {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .nav-account a {
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
        }

        .nav-login {
            color: #174b59;
            padding: 10px 15px;
        }

        .nav-register {
            background: #174b59;
            color: white;
            padding: 11px 18px;
            border-radius: 4px;
        }

        .nav-my-bookings {
            color: #174b59;
            padding: 10px;
        }

        .nav-logout {
            color: #9a6b43;
            padding: 10px;
        }

        @media (max-width: 900px) {
            .navbar {
                justify-content: center;
            }

            .navbar nav {
                width: 100%;
            }

            .nav-account {
                justify-content: center;
            }
        }
    </style>

</head>

<body>

<!-- NAVIGATION -->

<header class="navbar">

    <div class="logo">
        OCEAN <span>PEARL</span>
        <small>VILLA · HIKKADUWA</small>
    </div>

    <nav>

        <a href="index.php" class="active">Home</a>

        <a href="#about">About</a>

        <a href="#rooms">Rooms</a>

        <a href="#gallery">Gallery</a>

        <a href="#contact">Contact</a>

    </nav>

    <div class="nav-account">

        <?php if (!isset($_SESSION["user_id"])): ?>

            <a href="auth/login.php" class="nav-login">
                LOGIN
            </a>

            <a href="auth/register.php" class="nav-register">
                REGISTER
            </a>

        <?php elseif ($_SESSION["role"] === "guest"): ?>

            <a href="bookings/my_bookings.php" class="nav-my-bookings">
                MY BOOKINGS
            </a>

            <a href="guest/dashboard.php" class="nav-login">
                DASHBOARD
            </a>

            <a href="auth/logout.php" class="nav-logout">
                LOGOUT
            </a>

        <?php elseif ($_SESSION["role"] === "admin"): ?>

            <a href="admin/dashboard.php" class="nav-login">
                ADMIN DASHBOARD
            </a>

            <a href="auth/logout.php" class="nav-logout">
                LOGOUT
            </a>

        <?php endif; ?>

    </div>

</header>


<!-- HERO SECTION -->

<section class="hero">

    <div class="hero-overlay"></div>

    <div class="hero-content">

        <p class="hero-subtitle">
            WELCOME TO PARADISE · HIKKADUWA, SRI LANKA
        </p>

        <h1>
            Escape to the<br>
            <span>Ocean's Embrace.</span>
        </h1>

        <p class="hero-description">
            Discover a world of tropical elegance, peaceful surroundings
            and unforgettable coastal experiences.
        </p>

        <a href="#rooms" class="primary-button">
            EXPLORE OUR ROOMS →
        </a>

    </div>

    <div class="hero-location">
        ✦ Hikkaduwa, Southern Coast of Sri Lanka
    </div>

</section>


<!-- INTRODUCTION -->

<section class="about-section" id="about">

    <div class="about-image">

        <img
            src="https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=1000"
            alt="Luxury tropical resort"
        >

    </div>

    <div class="about-content">

        <p class="section-label">A LITTLE ABOUT US</p>

        <h2>
            Your Private Escape<br>
            by the <span>Indian Ocean.</span>
        </h2>

        <p>
            Welcome to Ocean Pearl Villa, a tropical retreat nestled
            along the beautiful coastline of Hikkaduwa.
        </p>

        <p>
            Experience warm Sri Lankan hospitality, thoughtfully
            designed accommodation and the beauty of coastal living.
            Whether you seek adventure or relaxation, your perfect
            getaway begins here.
        </p>

        <a href="#rooms" class="text-link">
            DISCOVER OUR VILLA →
        </a>

    </div>

</section>


<!-- FACILITIES -->

<section class="facilities-section">

    <div class="section-heading">

        <p class="section-label">THE OCEAN PEARL EXPERIENCE</p>

        <h2>Little Luxuries, Lasting Memories</h2>

    </div>

    <div class="facilities-grid">

        <div class="facility">
            <span>🌊</span>
            <h3>Coastal Living</h3>
            <p>Experience the beauty of Hikkaduwa's coastline.</p>
        </div>

        <div class="facility">
            <span>🏊</span>
            <h3>Swimming Pool</h3>
            <p>Relax and unwind beside our refreshing pool.</p>
        </div>

        <div class="facility">
            <span>🍽️</span>
            <h3>Dining Experience</h3>
            <p>Enjoy delicious meals and local flavours.</p>
        </div>

        <div class="facility">
            <span>🛏️</span>
            <h3>Luxury Rooms</h3>
            <p>Comfortable accommodation designed for relaxation.</p>
        </div>

    </div>

</section>


<!-- ROOMS SECTION -->

<section class="rooms-section" id="rooms">

    <div class="section-heading">

        <p class="section-label">YOUR PERFECT STAY</p>

        <h2>Explore Our Accommodation</h2>

        <p>
            Find the perfect space for your coastal getaway.
        </p>

    </div>

    <div class="rooms-grid">

        <?php if (count($rooms) > 0): ?>

            <?php foreach ($rooms as $room): ?>

                <div class="room-card">

                    <div class="room-image">

                        <img
                            src="<?php
                                $image = $room['image'] ?: 'default-room.jpg';

                                echo htmlspecialchars(
                                    filter_var($image, FILTER_VALIDATE_URL)
                                        ? $image
                                        : 'assets/images/' . $image
                                );
                            ?>"
                            alt="<?php echo htmlspecialchars($room['room_name']); ?>"
                        >

                    </div>

                    <div class="room-info">

                        <p class="room-type">
                            <?php echo htmlspecialchars($room['room_type']); ?>
                        </p>

                        <h3>
                            <?php echo htmlspecialchars($room['room_name']); ?>
                        </h3>

                        <p>
                            <?php echo htmlspecialchars($room['description']); ?>
                        </p>

                        <div class="room-details">

                            <span>
                                👥 <?php echo (int)$room['max_guests']; ?> Guests
                            </span>

                            <span>
                                LKR <?php echo number_format($room['price_per_night']); ?> / night
                            </span>

                        </div>

                        <a
                            href="rooms/details.php?id=<?php echo (int)$room['room_id']; ?>"
                            class="room-button"
                        >
                            VIEW ROOM →
                        </a>

                    </div>

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <p>No rooms are currently available.</p>

        <?php endif; ?>

    </div>

</section>


<!-- GALLERY -->

<section class="gallery-section" id="gallery">

    <div class="section-heading">

        <p class="section-label">CAPTURE THE MOMENT</p>

        <h2>A Glimpse of Paradise</h2>

    </div>

    <div class="gallery-grid">

        <img src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=800" alt="Tropical beach">

        <img src="https://images.unsplash.com/photo-1519046904884-53103b34b206?w=800" alt="Coastal scenery">

        <img src="https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=800" alt="Resort">

    </div>

</section>


<!-- CONTACT -->

<section class="contact-section" id="contact">

    <p class="section-label">YOUR JOURNEY STARTS HERE</p>

    <h2>Ready for Your Coastal Escape?</h2>

    <p>
        Let the beauty of Hikkaduwa become part of your next adventure.
    </p>

    <a href="#rooms" class="primary-button">
        BOOK YOUR STAY →
    </a>

</section>


<!-- FOOTER -->

<footer class="footer">

    <div class="footer-logo">

        OCEAN PEARL VILLA

        <p>Where the Ocean Meets Luxury.</p>

    </div>

    <p>Hikkaduwa, Sri Lanka</p>

    <p>© <?php echo date("Y"); ?> Ocean Pearl Villa. All Rights Reserved.</p>

</footer>


<script src="assets/js/script.js"></script>

</body>

</html>
