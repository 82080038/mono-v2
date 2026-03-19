<?php
// Test password hash
$password = 'password123';
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "New hash: $hash\n";
echo "Verify: " . (password_verify($password, $hash) ? 'true' : 'false') . "\n";

// Test existing hash
$existingHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
echo "Existing hash verify: " . (password_verify($password, $existingHash) ? 'true' : 'false') . "\n";
?>
