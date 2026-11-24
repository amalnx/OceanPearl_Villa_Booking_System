# Ocean Pearl Villa – Room Booking Management System

**IT1208 – Web Technologies | CA2 – 2026**

## 1. Project Overview

Ocean Pearl Villa is a dynamic web-based villa room booking management system developed for a coastal villa located in Hikkaduwa, Sri Lanka.

The system allows guests to explore available rooms, check room availability, make reservations, and manage their bookings. Administrators can manage rooms and handle guest reservations through an administrative dashboard.

**Tagline:** Where the Ocean Meets Luxury.

## 2. Technologies Used

- HTML5
- CSS3
- JavaScript
- PHP
- MySQL
- PDO
- WAMP Server
- phpMyAdmin

## 3. Main Features

### Guest Features
- Guest registration and login.
- Secure password hashing.
- View available rooms.
- View individual room details.
- Search room availability.
- Make room reservations.
- View personal bookings.
- Cancel pending bookings.
- Guest dashboard.

### Administrator Features
- Secure administrator login.
- Admin dashboard.
- View dashboard statistics.
- Add new rooms.
- Update room details.
- Upload room images.
- Delete rooms when permitted.
- View all guest reservations.
- Confirm pending bookings.
- Cancel reservations.
- Prevent overlapping confirmed bookings.

## 4. Database Structure

The system uses a MySQL database named `oceanpearl_db`.

Main database tables:

1. **users** – Stores guest and administrator information.
2. **rooms** – Stores room details, prices, facilities and availability.
3. **bookings** – Stores guest reservations and booking statuses.
4. **room_images** – Stores additional room image records.
5. **reviews** – Stores guest room reviews and ratings.

## 5. Installation Instructions

1. Install and start WAMP Server.
2. Copy the `OceanPearlVilla` project folder into:

   `C:\wamp64\www\`

3. Open phpMyAdmin.
4. Create a database named `oceanpearl_db`.
5. Import `database/db_structure.sql`.
6. Open `config/database.php`.
7. Check the database connection settings.
8. Open the project in your browser:

   `http://localhost/OceanPearlVilla/`

## 6. Administrator Account

To create an administrator account:

1. Register a new account through the registration page.
2. Open phpMyAdmin.
3. Select the `oceanpearl_db` database.
4. Open the SQL tab.
5. Execute the following query using the email address of the registered account:

   ```sql
   UPDATE users
   SET role = 'admin'
   WHERE email = 'your-admin-email@example.com';
   ```

6. Log in using the administrator account.

## 7. Security Features

- Password hashing using PHP `password_hash()`.
- Password verification using `password_verify()`.
- PDO prepared statements.
- Server-side input validation.
- Session-based authentication.
- Role-based access control.
- CSRF token validation for administrative booking actions.
- HTML output escaping.
- Server-side booking price calculation.
- Booking date and availability validation.

## 8. Project Folder Structure

```text
OceanPearlVilla/
│
├── admin/
├── auth/
├── bookings/
├── config/
├── database/
├── guest/
├── includes/
├── rooms/
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
│
├── index.php
└── README.md
```

## 9. AI Usage Declaration

AI tools were used as supporting resources during the development of this project. They assisted with understanding programming concepts, debugging errors, improving code structure, and preparing documentation.

The project was reviewed and tested by the developer.

## 10. Conclusion

Ocean Pearl Villa provides a simple and user-friendly platform for managing villa room reservations. It demonstrates the practical application of web development technologies, database management, server-side programming, and web security concepts.