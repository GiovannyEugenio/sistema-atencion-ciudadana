<?php
$password_plano = 'pan$part1d0';
$hash_seguro = password_hash($password_plano, PASSWORD_DEFAULT);

echo "Copia y usa este hash para crear tu usuario en la base de datos:<br><br>";
echo "<strong>" . $hash_seguro . "</strong>";
?>
