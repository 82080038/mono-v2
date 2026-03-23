<?php
// Simple test script
require_once 'api/batch-update-legacy.php';
$batch = new BatchUpdate();
echo 'Found: ' . count($batch->legacyFiles) . ' legacy files';
?>
