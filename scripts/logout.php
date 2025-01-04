<?php
session_start();
session_destroy();
header('Location: ../src/login.php');
exit;