
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<header class="site-header">
    <nav class="navbar">

        <a href="/OceanPearlVilla/index.php" class="logo">
            Ocean Pearl <span>Villa</span>
        </a>

        <div class="nav-links">
            <a href="/OceanPearlVilla/index.php">Home</a>
            <a href="/OceanPearlVilla/index.php#rooms">Our Rooms</a>
            <a href="/OceanPearlVilla/index.php#facilities">Facilities</a>
            <a href="/OceanPearlVilla/index.php#gallery">Gallery</a>
            <a href="/OceanPearlVilla/index.php#contact">Contact</a>

            <?php if (!isset($_SESSION["user_id"])): ?>

                <a href="/OceanPearlVilla/auth/login.php">Login</a>
                <a href="/OceanPearlVilla/auth/register.php" class="nav-register">
                    Register
                </a>

            <?php elseif ($_SESSION["role"] === "guest"): ?>

                <a href="/OceanPearlVilla/bookings/my_bookings.php">
                    My Bookings
                </a>

                <a href="/OceanPearlVilla/guest/dashboard.php">
                    Dashboard
                </a>

                <a href="/OceanPearlVilla/auth/logout.php">
                    Logout
                </a>

            <?php elseif ($_SESSION["role"] === "admin"): ?>

                <a href="/OceanPearlVilla/admin/dashboard.php">
                    Admin Dashboard
                </a>

                <a href="/OceanPearlVilla/auth/logout.php">
                    Logout
                </a>

            <?php endif; ?>

        </div>

    </nav>
</header>