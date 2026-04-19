# -Listeners-lounge_Update
INT1059 Advanced Web - Music Album Collection

- Name: Chonlatorn Inthayat (Joon)
- Student ID: 14929
- Subject: INT1059 Advanced Web
- Teacher: Vivian Hormiz

## About
Listeners Lounge is a website where users can browse music albums, 
read and write reviews, and save their favourite albums to their account.

## Features
- Browse 26 music albums across 8 genres (
- Homepage shows 8 random albums every visit
- Browse and filter albums by genre
- Search albums by title or artist
- User registration and login system
- Write star ratings (1-5) and text reviews
- Edit and delete your own reviews
- Add and remove albums from favourites
- User account dashboard
- Update username, email and password
-------------------------------------------------------------
## Live Website
http://listenerslounge.lovestoblog.com/listeners_lounge/
-------------------------------------------------------------


## How to Run Locally
1. Install XAMPP
2. Import `database.sql` into phpMyAdmin
3. Copy all files into `xampp/htdocs/listeners_lounge/`
4. Start Apache and MySQL in XAMPP
5. Visit `http://localhost/listeners_lounge/`

## For local testing change confic.php to:
define('DB_HOST', 'sql310.infinityfree.com');
define('DB_USER', 'if0_41687360');
define('DB_PASS', 'joon36472');
define('DB_NAME', 'if0_41687360_listeners');

## GitHub Repository
https://github.com/Chonlatorn14929/-Listeners-lounge_Update

## Demo Login
- Username: testing1@gmail.com
- Password: testing123

## Built With
- PHP
- MySQL
- HTML and CSS
- JavaScript
- XAMPP for local development
- InfinityFree for hosting

## Project Structure
index.php | Homepage 
album.php | Album detail page 
auth.php | Login and register 
account.php | User account dashboard 
search.php | Search albums
genre.php | Browse by genre 
logout.php | Logout |
includes/config.php | Database connection 
includes/header.php | Navigation bar 
includes/footer.php | Footer 
assets/css/style.css | All CSS styling 
assets/js/main.js | JavaScript 
Update.database.sql | Database export 
