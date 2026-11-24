
-- =====================================================
-- OCEAN PEARL VILLA
-- Villa Room Booking Management System
-- IT1208 - Web Technologies CA2
-- =====================================================

CREATE DATABASE IF NOT EXISTS oceanpearl_db;

USE oceanpearl_db;

-- =====================================================
-- 1. USERS TABLE
-- =====================================================

CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    contact_number VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    role ENUM('guest', 'admin') DEFAULT 'guest',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- 2. ROOMS TABLE
-- =====================================================

CREATE TABLE IF NOT EXISTS rooms (
    room_id INT AUTO_INCREMENT PRIMARY KEY,
    room_name VARCHAR(150) NOT NULL,
    room_type VARCHAR(100) NOT NULL,
    description TEXT,
    price_per_night DECIMAL(10,2) NOT NULL,
    max_guests INT NOT NULL,
    facilities TEXT,
    image VARCHAR(500),
    room_status ENUM('available', 'maintenance') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- 3. BOOKINGS TABLE
-- =====================================================

CREATE TABLE IF NOT EXISTS bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    room_id INT NOT NULL,
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    guests INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    booking_status ENUM('pending', 'confirmed', 'cancelled')
        DEFAULT 'pending',
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_booking_user
        FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON DELETE CASCADE,

    CONSTRAINT fk_booking_room
        FOREIGN KEY (room_id)
        REFERENCES rooms(room_id)
        ON DELETE CASCADE
);

-- =====================================================
-- 4. ROOM IMAGES TABLE
-- =====================================================

CREATE TABLE IF NOT EXISTS room_images (
    image_id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT NOT NULL,
    image_path VARCHAR(500) NOT NULL,

    CONSTRAINT fk_room_images
        FOREIGN KEY (room_id)
        REFERENCES rooms(room_id)
        ON DELETE CASCADE
);

-- =====================================================
-- 5. REVIEWS TABLE
-- =====================================================

CREATE TABLE IF NOT EXISTS reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    room_id INT NOT NULL,
    rating INT NOT NULL,
    comment TEXT,
    review_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_review_user
        FOREIGN KEY (user_id)
        REFERENCES users(user_id)
        ON DELETE CASCADE,

    CONSTRAINT fk_review_room
        FOREIGN KEY (room_id)
        REFERENCES rooms(room_id)
        ON DELETE CASCADE,

    CONSTRAINT chk_rating
        CHECK (rating BETWEEN 1 AND 5)
);

-- =====================================================
-- 6. SAMPLE ROOM DATA
-- =====================================================

INSERT INTO rooms
(room_name, room_type, description, price_per_night,
 max_guests, facilities, image, room_status)
VALUES

(
'Ocean View Deluxe',
'Deluxe',
'Enjoy a beautiful ocean view from this comfortable deluxe room.',
18500.00,
2,
'Air Conditioning, Wi-Fi, Private Bathroom, Ocean View',
'https://images.unsplash.com/photo-1611892440504-42a792e24d32',
'available'
),

(
'Tropical Garden Suite',
'Suite',
'Relax in a spacious suite surrounded by tropical greenery.',
24000.00,
3,
'Air Conditioning, Wi-Fi, Garden View, Mini Bar',
'https://images.unsplash.com/photo-1611892440504-42a792e24d32',
'available'
),

(
'Beachfront Family Room',
'Family',
'A spacious family room with a relaxing coastal atmosphere.',
32000.00,
4,
'Air Conditioning, Wi-Fi, Balcony, Private Bathroom',
'https://images.unsplash.com/photo-1566665797739-1674de7a421a',
'available'
),

(
'Coastal Standard Room',
'Standard',
'A comfortable and affordable room for a relaxing holiday.',
12000.00,
2,
'Air Conditioning, Wi-Fi, Private Bathroom',
'https://images.unsplash.com/photo-1611892440504-42a792e24d32',
'available'
);

-- =====================================================
-- END OF DATABASE STRUCTURE
-- =====================================================
