# Hotel Room Management System (PHP 8 + MySQL + PDO)

A small admin dashboard for managing hotel rooms: create, view, edit, delete and search.

## Project structure

```
hotel-management/
├── index.php          Dashboard, room table, search (Read)
├── create.php         Add room (Create)
├── edit.php           Edit room (Update)
├── delete.php         Delete room (Delete, POST only)
├── db.php             Config, session start, PDO connection
├── style.css          All styling
├── database.sql       Database + table + sample rooms
├── LEARNING_GUIDE.md  Explanations + interview questions
└── includes/
    ├── header.php     <head>, top bar, flash message
    ├── sidebar.php    Navigation
    ├── footer.php     Closing tags, icons, small JS
    ├── functions.php  e(), CSRF, flash, validation
    └── room_form.php  Add/Edit form shared by both pages
```

## Database schema

`hotel_db.rooms`

| Column      | Type                      | Notes                            |
|-------------|---------------------------|----------------------------------|
| id          | INT AUTO_INCREMENT, PK    | unique ID                        |
| room_number | VARCHAR(10) NOT NULL, UNIQUE | duplicates rejected by MySQL  |
| room_type   | VARCHAR(50) NOT NULL      | Standard / Deluxe / Suite        |
| price       | DECIMAL(10,2) NOT NULL    | price per night                  |
| status      | VARCHAR(20) DEFAULT 'Available' | Available / Occupied / Maintenance |
| created_at  | TIMESTAMP DEFAULT CURRENT_TIMESTAMP | creation time      |

## Setup

Requirements: PHP 8.0+ with the `pdo_mysql` extension, and MySQL or MariaDB.

1. **Create the database**
   - Terminal: `mysql -u root -p < database.sql`
   - or phpMyAdmin: open the SQL tab, paste `database.sql`, click Go.
2. **Check credentials** at the top of `db.php` (`DB_USER`, `DB_PASS`).
   XAMPP default: user `root`, empty password. MAMP default password: `root`.
3. **Run the app**
   - XAMPP/MAMP: copy the `hotel-management` folder into `htdocs`, then open
     `http://localhost/hotel-management/`
   - PHP built-in server: `cd hotel-management && php -S localhost:8000`, then open
     `http://localhost:8000/`


