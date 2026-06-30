<?php
require_once 'db.php';
session_unset();
session_destroy();
header("Location: login.php?msg=logged_out");
exit();
