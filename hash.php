<?php
$password = 'seller123'; // password yang ingin dipakai
$hash = password_hash($password, PASSWORD_DEFAULT);
echo $hash;