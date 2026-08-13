<?php

echo "SMTP Host: " . getenv('WN_SMTP_HOST') . "<br>";
echo "SMTP User: " . getenv('WN_SMTP_USER') . "<br>";
echo "From Email: " . getenv('WN_FROM_EMAIL') . "<br>";

echo "<br>SMTP Password: ";
echo getenv('WN_SMTP_PASS') ? "Configured ✓" : "NOT CONFIGURED ✗";
echo "<br>SMTP Port: ";
echo getenv('WN_SMTP_PORT') ? "Configured ✓" : "NOT CONFIGURED ✗";