<?php

$password = 'JRRAKIbhasan75@2006#$.';

$hash = password_hash($password, PASSWORD_DEFAULT);

echo '<h2>Password Hash:</h2>';
echo '<p>' . htmlspecialchars($hash) . '</p>';