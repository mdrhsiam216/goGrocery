<?php

$info = (object)[];

// destroy session
session_unset();
session_destroy();

$info->data_type = 'logout';
$info->message = 'Logged out successfully';
$info->logged_in = false;

echo json_encode($info);
