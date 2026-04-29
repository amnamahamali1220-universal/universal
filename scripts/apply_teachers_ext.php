<?php
require_once 'core/db.php';

try {
    $sql = file_get_contents('sql/teachers_extension.sql');
    $pdo->exec($sql);
    echo "Database extension applied successfully.\n";
} catch (Exception $e) {
    echo "Error applying database extension: " . $e->getMessage() . "\n";
}
?>
