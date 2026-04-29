<?php
require_once __DIR__ . '/config.php';

function generateDatabaseBackup() {
    $backupDir = APP_ROOT . '/uploads/backups/';
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d_H-i-s');
    $fileName = 'backup_' . DB_NAME . '_' . $timestamp . '.sql';
    $filePath = $backupDir . $fileName;
    
    // Set mysqldump path if necessary. Using relative or system path.
    // XAMPP default path for mysqldump on Windows:
    $mysqldumpPath = 'c:\\xampp\\mysql\\bin\\mysqldump.exe'; 
    if(!file_exists($mysqldumpPath)){
        $mysqldumpPath = 'mysqldump'; // Fallback to system path
    }

    $command = sprintf(
        '%s --user=%s --password=%s --host=%s %s > %s',
        escapeshellcmd($mysqldumpPath),
        escapeshellarg(DB_USER),
        escapeshellarg(DB_PASS),
        escapeshellarg(DB_HOST),
        escapeshellarg(DB_NAME),
        escapeshellarg($filePath)
    );
    
    // Execute command
    $output = [];
    $returnVar = NULL;
    exec($command, $output, $returnVar);
    
    if ($returnVar === 0 && file_exists($filePath)) {
        return [
            'success' => true,
            'file' => $fileName,
            'path' => $filePath,
            'size' => filesize($filePath)
        ];
    } else {
        return [
            'success' => false,
            'error' => 'Command execution failed with code ' . $returnVar
        ];
    }
}
?>
