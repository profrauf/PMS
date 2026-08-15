<?php
require_once __DIR__ . '/../../config/app.php';
logout();
redirect('modules/auth/login.php');
