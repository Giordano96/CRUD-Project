<?php
session_start();
session_destroy();
header("Location: ../Login_Sign/login_sign.php");
exit;