<?php
session_start();
echo "Session ID: " . session_id();
echo "<br>Session Data: ";
print_r($_SESSION);
?> 