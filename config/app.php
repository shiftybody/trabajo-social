<?php
define('APP_NAME', $_ENV['APP_NAME']);
define('APP_URL', $_ENV['APP_URL']);
define('APP_ROOT', $_ENV['APP_ROOT']);
define('APP_SESSION_NAME', $_ENV['APP_SESSION_NAME']);
define('TOKEN_SECRET_KEY', $_ENV['TOKEN_SECRET_KEY']);
date_default_timezone_set($_ENV['APP_TIMEZONE']);
