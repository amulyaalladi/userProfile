<?php
// Copy this file to config.local.php and fill in your own values.
// config.local.php is git-ignored, so your real passwords are never committed.

define('DB_HOST', 'localhost');
define('DB_NAME', 'userProfile');
define('DB_USER', 'root');
define('DB_PASS', 'your_mysql_password');

// Remember to URL-encode special characters in the password (@ -> %40, $ -> %24)
define('MONGO_URI', 'mongodb+srv://USERNAME:PASSWORD@your-cluster.mongodb.net/');
define('MONGO_DB_NAME', 'userProfile');

define('REDIS_HOST', '127.0.0.1');
define('REDIS_PORT', 6379);
