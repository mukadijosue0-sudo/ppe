<?php
$user = "admin";
$pass = "admin";

// Génération du condensé SHA-1 encodé en Base64
$hashed_password = "{SHA}" . base64_encode(sha1($pass, true));

// Format à insérer dans le fichier .htpasswd
echo $user . ":" . $hashed_password;
?>