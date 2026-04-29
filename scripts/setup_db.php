<?php
require_once '../core/db.php';

try {
    echo "<h1>Setting up Database...</h1>";
    
    $sql = file_get_contents('../sql/student_cms.sql');
    
    if (!$sql) {
        die("Error: Could not read sql/student_cms.sql");
    }

    // Split SQL by semicolons to execute statements individually if needed, 
    // but PDO::exec can handle multiple statements if emulation is on.
    // However, it's often safer/clearer to just run it.
    
    $pdo->exec($sql);
    
    echo "<h2>Database Schema Applied Successfully!</h2>";
    echo "<p>Tables created/verified.</p>";
    
} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}
?>
