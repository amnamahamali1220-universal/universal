<?php
require_once 'core/db.php';
$stmt = $pdo->query("SELECT * FROM teachers");
while($row = $stmt->fetch()) {
    print_r($row);
}
?>
