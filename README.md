
# UK Event Planner & Recommender Web App

## 🔧 Requirements
- PHP 7.4 or higher
- MySQL or MariaDB
- Apache or compatible web server

## 📁 Setup Instructions

1. **Import the SQL file into your MySQL database**:
   - Open phpMyAdmin (or a MySQL client).
   - Create a new database (e.g., `event_planner`).
   - Import the file located at `/sql/database.sql`.

2. **Configure the database connection**:
   - Open `config.php` in a code editor.
   - Update the `$servername`, `$username`, `$password`, and `$dbname` variables to match your local setup.

3. **Deploy the application**:
   - Copy the full project folder to your server's web root directory (e.g., `htdocs/` for XAMPP).
   - Access the site at `http://localhost/[your-folder]/index.php`.

## 📂 Project Structure

- `index.php` - Main landing page
- `php/` - PHP logic and routing files (e.g., `recommend.php`, `add_to_basket.php`)
- `css/` - Stylesheets
- `js/` - JavaScript for dynamic interactions
- `images/` - Event-related images and defaults
- `event_planner_db.sql` - SQL dump of your event database

## 📝 Notes
- Ensure you have populated your `events` table with real or sample event data.
- Thumbnails use default images mapped by category. Make sure default images are in the `images/` folder.
- If needed, update file permissions for image folders.
