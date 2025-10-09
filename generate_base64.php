<?php
$path = 'Uploads/default-avatar.jpg';

$type = pathinfo($path, PATHINFO_EXTENSION);
$data = file_get_contents($path);
$base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

echo "Copy the entire string below:\n\n";
echo $base64;
