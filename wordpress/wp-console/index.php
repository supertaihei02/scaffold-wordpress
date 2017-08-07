<?php
define('LOGIN_KEY', password_hash( 'framelunch', PASSWORD_BCRYPT, array('cost'=>10)));
require_once '../wp-login.php';
