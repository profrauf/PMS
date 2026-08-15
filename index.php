<?php
require_once __DIR__ . '/config/app.php';
redirect(isLoggedIn() ? 'modules/dashboard/index.php' : 'modules/auth/login.php');
