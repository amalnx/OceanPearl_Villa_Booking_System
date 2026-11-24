<?php
session_start();

require_once "../config/database.php";

// Admin access control
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../auth/login.php");
    exit;
}

// CSRF protection
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$message = "";
$message_type = "";

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (
        !isset($_POST["csrf_token"]) ||
        !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])
    ) {
        die("Invalid request. Please refresh the page and try again.");
    }

    $action = $_POST["action"] ?? "";

    // ADD OR UPDATE ROOM
    if ($action === "save_room") {

        $room_id = filter_input(INPUT_POST, "room_id", FILTER_VALIDATE_INT);

        $room_name = trim($_POST["room_name"] ?? "");
        $room_type = trim($_POST["room_type"] ?? "");
        $description = trim($_POST["description"] ?? "");
        $price = filter_var($_POST["price_per_night"] ?? "", FILTER_VALIDATE_FLOAT);
        $max_guests = filter_var($_POST["max_guests"] ?? "", FILTER_VALIDATE_INT);
        $facilities = trim($_POST["facilities"] ?? "");
        $room_status = $_POST["room_status"] ?? "available";
        $image = trim($_POST["image_url"] ?? "");

        if (
            $room_name === "" ||
            $room_type === "" ||
            $description === "" ||
            $price === false ||
            $price <= 0 ||
            $max_guests === false ||
            $max_guests < 1 ||
            !in_array($room_status, ["available", "maintenance"], true)
        ) {
            $message = "Please enter valid room details.";
            $message_type = "error";

        } else {

            // Upload a room image if selected
            if (
                isset($_FILES["room_image"]) &&
                $_FILES["room_image"]["error"] !== UPLOAD_ERR_NO_FILE
            ) {

                $file = $_FILES["room_image"];

                if ($file["error"] !== UPLOAD_ERR_OK) {

                    $message = "Image upload failed.";
                    $message_type = "error";

                } elseif ($file["size"] > 3 * 1024 * 1024) {

                    $message = "Image size must be less than 3 MB.";
                    $message_type = "error";

                } else {

                    $allowed_types = [
                        "image/jpeg" => "jpg",
                        "image/png" => "png",
                        "image/webp" => "webp"
                    ];

                    $mime = (new finfo(FILEINFO_MIME_TYPE))
                        ->file($file["tmp_name"]);

                    if (!isset($allowed_types[$mime])) {

                        $message = "Only JPG, PNG and WEBP images are allowed.";
                        $message_type = "error";

                    } else {

                        $filename = bin2hex(random_bytes(16))
                            . "." . $allowed_types[$mime];

                        $upload_directory = "../assets/images/";

                        if (!is_dir($upload_directory)) {
                            mkdir($upload_directory, 0755, true);
                        }

                        if (
                            move_uploaded_file(
                                $file["tmp_name"],
                                $upload_directory . $filename
                            )
                        ) {
                            $image = $filename;
                        } else {
                            $message = "Unable to save the uploaded image.";
                            $message_type = "error";
                        }
                    }
                }
            }

            if ($message_type !== "error") {

                if ($room_id) {

                    // Update existing room
                    $stmt = $pdo->prepare("
                        UPDATE rooms
                        SET room_name = ?,
                            room_type = ?,
                            description = ?,
                            price_per_night = ?,
                            max_guests = ?,
                            facilities = ?,
                            image = ?,
                            room_status = ?
                        WHERE room_id = ?
                    ");

                    $stmt->execute([
                        $room_name,
                        $room_type,
                        $description,
                        $price,
                        $max_guests,
                        $facilities,
                        $image,
                        $room_status,
                        $room_id
                    ]);

                    $message = "Room updated successfully.";

                } else {

                    // Add new room
                    $stmt = $pdo->prepare("
                        INSERT INTO rooms
                        (
                            room_name,
                            room_type,
                            description,
                            price_per_night,
                            max_guests,
                            facilities,
                            image,
                            room_status
                        )
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                    ");

                    $stmt->execute([
                        $room_name,
                        $room_type,
                        $description,
                        $price,
                        $max_guests,
                        $facilities,
                        $image,
                        $room_status
                    ]);

                    $message = "New room added successfully!";
                }

                $message_type = "success";
            }
        }
    }

    // DELETE ROOM
    elseif ($action === "delete_room") {

        $room_id = filter_var(
            $_POST["room_id"] ?? "",
            FILTER_VALIDATE_INT
        );

        if ($room_id) {

            // Check whether the room is linked to existing records
            $stmt = $pdo->prepare("
                SELECT
                    (SELECT COUNT(*) FROM bookings WHERE room_id = ?) AS bookings_count,
                    (SELECT COUNT(*) FROM reviews WHERE room_id = ?) AS reviews_count,
                    (SELECT COUNT(*) FROM room_images WHERE room_id = ?) AS images_count
            ");

            $stmt->execute([$room_id, $room_id, $room_id]);
            $references = $stmt->fetch();

            if (
                $references["bookings_count"] > 0 ||
                $references["reviews_count"] > 0 ||
                $references["images_count"] > 0
            ) {

                $message = "This room has existing bookings, reviews or images. "
                    . "You cannot delete it. Change its status to Maintenance instead.";

                $message_type = "error";

            } else {

                $stmt = $pdo->prepare("
                    DELETE FROM rooms
                    WHERE room_id = ?
                ");

                $stmt->execute([$room_id]);

                $message = "Room deleted successfully.";
                $message_type = "success";
            }

        } else {

            $message = "Invalid room selected.";
            $message_type = "error";
        }
    }
}

// Retrieve room for editing
$edit_room = null;

if (isset($_GET["edit"])) {

    $edit_id = filter_input(INPUT_GET, "edit", FILTER_VALIDATE_INT);

    if ($edit_id) {

        $stmt = $pdo->prepare("
            SELECT * FROM rooms
            WHERE room_id = ?
        ");

        $stmt->execute([$edit_id]);
        $edit_room = $stmt->fetch();
    }
}

// Retrieve all rooms
$rooms = $pdo->query("
    SELECT * FROM rooms
    ORDER BY room_id DESC
")->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Rooms | Ocean Pearl Villa</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <style>

        body {
            background: #f6f2eb;
        }

        .admin-header {
            background: #103943;
            color: white;
            padding: 22px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .admin-header h2 {
            margin: 0;
            font-family: 'Playfair Display', serif;
        }

        .admin-header a {
            color: white;
            text-decoration: none;
            margin-left: 15px;
        }

        .page-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
        }

        .page-title {
            color: #174b59;
            font-family: 'Playfair Display', serif;
            font-size: 32px;
        }

        .form-container,
        .rooms-container {
            background: white;
            padding: 28px;
            border-radius: 12px;
            margin-bottom: 35px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
        }

        .form-container h2,
        .rooms-container h2 {
            color: #174b59;
            margin-top: 0;
        }

        .room-form label {
            display: block;
            font-weight: 600;
            margin-bottom: 7px;
        }

        .room-form input,
        .room-form textarea,
        .room-form select {
            width: 100%;
            padding: 12px;
            margin-bottom: 18px;
            border: 1px solid #ddd;
            border-radius: 6px;
            box-sizing: border-box;
            font-family: inherit;
        }

        .room-form textarea {
            min-height: 100px;
            resize: vertical;
        }

        .save-button {
            background: #174b59;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 15px;
        }

        .save-button:hover {
            background: #103943;
        }

        .cancel-link {
            margin-left: 15px;
            color: #174b59;
        }

        .message {
            padding: 14px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .success {
            background: #d4edda;
            color: #155724;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            min-width: 850px;
            border-collapse: collapse;
        }

        th, td {
            padding: 14px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        th {
            background: #f6f2eb;
            color: #174b59;
        }

        .room-image {
            width: 100px;
            height: 70px;
            object-fit: cover;
            border-radius: 7px;
        }

        .status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .available {
            background: #d4edda;
            color: #155724;
        }

        .maintenance {
            background: #fff0c2;
            color: #856404;
        }

        .edit-button,
        .delete-button {
            padding: 7px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 13px;
        }

        .edit-button {
            background: #174b59;
            color: white;
        }

        .delete-button {
            background: #b63b3b;
            color: white;
        }

        .delete-form {
            display: inline;
        }

        @media(max-width: 600px) {
            .admin-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }

    </style>

</head>

<body>

<header class="admin-header">

    <h2>Ocean Pearl Villa | Admin</h2>

    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="../index.php">View Website</a>
        <a href="../auth/logout.php">Logout</a>
    </nav>

</header>


<main class="page-container">

    <h1 class="page-title">Room Management</h1>

    <p>Add, update and manage your villa rooms.</p>


    <?php if ($message): ?>

        <div class="message <?php echo e($message_type); ?>">
            <?php echo e($message); ?>
        </div>

    <?php endif; ?>


    <!-- ADD / EDIT ROOM FORM -->

    <section class="form-container">

        <h2>
            <?php echo $edit_room ? "Edit Room" : "Add New Room"; ?>
        </h2>

        <form method="POST"
              enctype="multipart/form-data"
              class="room-form">

            <input type="hidden" name="csrf_token"
                   value="<?php echo e($_SESSION["csrf_token"]); ?>">

            <input type="hidden" name="action" value="save_room">

            <input type="hidden" name="room_id"
                   value="<?php echo (int)($edit_room["room_id"] ?? 0); ?>">

            <label>Room Name</label>

            <input type="text" name="room_name"
                   value="<?php echo e($edit_room["room_name"] ?? ""); ?>"
                   required>


            <label>Room Type</label>

            <input type="text" name="room_type"
                   placeholder="Example: Deluxe, Suite, Family"
                   value="<?php echo e($edit_room["room_type"] ?? ""); ?>"
                   required>


            <label>Description</label>

            <textarea name="description" required><?php
                echo e($edit_room["description"] ?? "");
            ?></textarea>


            <label>Price Per Night (LKR)</label>

            <input type="number"
                   name="price_per_night"
                   min="1"
                   step="0.01"
                   value="<?php echo e($edit_room["price_per_night"] ?? ""); ?>"
                   required>


            <label>Maximum Guests</label>

            <input type="number"
                   name="max_guests"
                   min="1"
                   value="<?php echo e($edit_room["max_guests"] ?? ""); ?>"
                   required>


            <label>Facilities</label>

            <textarea name="facilities"
                      placeholder="Example: Wi-Fi, Air Conditioning, Sea View"><?php
                echo e($edit_room["facilities"] ?? "");
            ?></textarea>


            <label>Room Image URL (optional)</label>

            <input type="url"
                   name="image_url"
                   placeholder="Paste an image URL"
                   value="<?php
                       $currentImage = $edit_room["image"] ?? "";
                       echo e(
                           filter_var($currentImage, FILTER_VALIDATE_URL)
                               ? $currentImage
                               : ""
                       );
                   ?>">


            <label>Or Upload a Room Image</label>

            <input type="file"
                   name="room_image"
                   accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">


            <?php if (!empty($edit_room["image"])): ?>

                <p>Current image:</p>

                <img
                    src="<?php
                        $img = $edit_room["image"];

                        echo e(
                            filter_var($img, FILTER_VALIDATE_URL)
                                ? $img
                                : "../assets/images/" . $img
                        );
                    ?>"
                    alt="Current room image"
                    class="room-image"
                >

                <br><br>

            <?php endif; ?>


            <label>Room Status</label>

            <select name="room_status">

                <option value="available"
                    <?php
                    echo (($edit_room["room_status"] ?? "available") === "available")
                        ? "selected" : "";
                    ?>>
                    Available
                </option>

                <option value="maintenance"
                    <?php
                    echo (($edit_room["room_status"] ?? "") === "maintenance")
                        ? "selected" : "";
                    ?>>
                    Maintenance
                </option>

            </select>


            <button type="submit" class="save-button">

                <?php echo $edit_room ? "Update Room" : "Add Room"; ?>

            </button>

            <?php if ($edit_room): ?>

                <a href="rooms.php" class="cancel-link">
                    Cancel Editing
                </a>

            <?php endif; ?>

        </form>

    </section>


    <!-- ALL ROOMS TABLE -->

    <section class="rooms-container">

        <h2>All Rooms (<?php echo count($rooms); ?>)</h2>

        <div class="table-wrapper">

            <table>

                <thead>

                    <tr>
                        <th>Image</th>
                        <th>Room Name</th>
                        <th>Type</th>
                        <th>Price / Night</th>
                        <th>Max Guests</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($rooms as $room): ?>

                        <tr>

                            <td>

                                <?php if (!empty($room["image"])): ?>

                                    <img
                                        src="<?php
                                            echo e(
                                                filter_var($room["image"], FILTER_VALIDATE_URL)
                                                    ? $room["image"]
                                                    : "../assets/images/" . $room["image"]
                                            );
                                        ?>"
                                        alt="<?php echo e($room["room_name"]); ?>"
                                        class="room-image"
                                    >

                                <?php else: ?>

                                    No image

                                <?php endif; ?>

                            </td>

                            <td><?php echo e($room["room_name"]); ?></td>

                            <td><?php echo e($room["room_type"]); ?></td>

                            <td>
                                LKR <?php echo number_format((float)$room["price_per_night"], 2); ?>
                            </td>

                            <td><?php echo (int)$room["max_guests"]; ?></td>

                            <td>

                                <span class="status <?php echo e($room["room_status"]); ?>">
                                    <?php echo e($room["room_status"]); ?>
                                </span>

                            </td>

                            <td>

                                <a href="rooms.php?edit=<?php echo (int)$room["room_id"]; ?>"
                                   class="edit-button">
                                    Edit
                                </a>

                                <form method="POST"
                                      class="delete-form"
                                      onsubmit="return confirm('Are you sure you want to delete this room?');">

                                    <input type="hidden"
                                           name="csrf_token"
                                           value="<?php echo e($_SESSION["csrf_token"]); ?>">

                                    <input type="hidden"
                                           name="action"
                                           value="delete_room">

                                    <input type="hidden"
                                           name="room_id"
                                           value="<?php echo (int)$room["room_id"]; ?>">

                                    <button type="submit" class="delete-button">
                                        Delete
                                    </button>

                                </form>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    <?php if (count($rooms) === 0): ?>

                        <tr>
                            <td colspan="7">No rooms found.</td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </section>

</main>

</body>
</html>
