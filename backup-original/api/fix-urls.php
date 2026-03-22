<?php
/**
 * Fix all relative URLs to absolute URLs in HTML files
 * Converts href="../something.html" to href="/mono-v2/something.html"
 */

$baseDir = __DIR__ . '/../pages/';
$fixedCount = 0;
$errorCount = 0;

function fixUrlsInFile($filePath, $baseDir) {
    global $fixedCount, $errorCount;
    
    $content = file_get_contents($filePath);
    if ($content === false) {
        echo "❌ Cannot read: $filePath\n";
        $errorCount++;
        return;
    }
    
    $originalContent = $content;
    $relativePath = str_replace($baseDir, '', $filePath);
    $depth = substr_count($relativePath, '/');
    
    // Get the folder path from file location
    // e.g., pages/admin/members.html -> /mono-v2/pages/admin/
    $folderParts = explode('/', $relativePath);
    array_pop($folderParts); // Remove filename
    $currentFolder = implode('/', $folderParts);
    
    // Fix patterns:
    // 1. href="../folder/file.html" -> href="/mono-v2/pages/folder/file.html"
    // 2. href="../../login.html" -> href="/mono-v2/login.html"
    // 3. href="./file.html" -> href="/mono-v2/pages/current/file.html"
    // 4. href="file.html" -> href="/mono-v2/pages/current/file.html"
    // 5. window.location.href = "..." same as above
    
    // Pattern 1: href="../something" (parent directory)
    $content = preg_replace_callback(
        '/href="\.\.\/(\.\.\/)?([^"]+)"/',
        function($matches) use ($currentFolder) {
            $upLevels = $matches[1] ? 2 : 1; // ../ or ../../
            $target = $matches[2];
            
            if ($upLevels === 2) {
                // ../../ goes to root
                return 'href="/mono-v2/' . $target . '"';
            } else {
                // ../ goes up one level from current folder
                $parentParts = explode('/', $currentFolder);
                array_pop($parentParts);
                $parentFolder = implode('/', $parentParts);
                return 'href="/mono-v2/' . ($parentFolder ? $parentFolder . '/' : '') . $target . '"';
            }
        },
        $content
    );
    
    // Pattern 2: href="./file.html" (current directory explicit)
    $content = preg_replace(
        '/href="\.\/([^"]+)"/',
        'href="/mono-v2/' . $currentFolder . '/$1"',
        $content
    );
    
    // Pattern 3: href="file.html" (current directory implicit) - but NOT href="http... or href="#
    $content = preg_replace(
        '/href="(?!\/|http|#|\.\.\/|\.\/)([^"]+)"/',
        'href="/mono-v2/' . $currentFolder . '/$1"',
        $content
    );
    
    // Same patterns for single quotes
    $content = preg_replace_callback(
        "/href='\.\.\/(\.\.\/)?([^']+)'/",
        function($matches) use ($currentFolder) {
            $upLevels = $matches[1] ? 2 : 1;
            $target = $matches[2];
            
            if ($upLevels === 2) {
                return "href='/mono-v2/" . $target . "'";
            } else {
                $parentParts = explode('/', $currentFolder);
                array_pop($parentParts);
                $parentFolder = implode('/', $parentParts);
                return "href='/mono-v2/" . ($parentFolder ? $parentFolder . '/' : '') . $target . "'";
            }
        },
        $content
    );
    
    $content = preg_replace(
        "/href='\.\/([^']+)'/",
        "href='/mono-v2/" . $currentFolder . "/$1'",
        $content
    );
    
    $content = preg_replace(
        "/href='(?!\/|http|#|\.\.\/|\.\/)([^']+)'/",
        "href='/mono-v2/" . $currentFolder . "/$1'",
        $content
    );
    
    // Fix window.location.href patterns
    $content = preg_replace(
        '/window\.location\.href\s*=\s*"(?!\/|http|#)([^"]+)"/',
        'window.location.href = "/mono-v2/$1"',
        $content
    );
    
    $content = preg_replace(
        "/window\.location\.href\s*=\s*'(?!\/|http|#)([^']+)'/",
        "window.location.href = '/mono-v2/$1'",
        $content
    );
    
    // Fix action="..." in forms (for PHP files)
    $content = preg_replace(
        '/action="(?!\/|http|#)([^"]+)\.php"/',
        'action="/mono-v2/$1.php"',
        $content
    );
    
    if ($content !== $originalContent) {
        if (file_put_contents($filePath, $content) !== false) {
            echo "✅ Fixed: $relativePath\n";
            $fixedCount++;
        } else {
            echo "❌ Failed to write: $relativePath\n";
            $errorCount++;
        }
    }
}

echo "========================================\n";
echo "FIXING URLs IN HTML FILES\n";
echo "========================================\n\n";

// Get all HTML files
$htmlFiles = [];
$directories = [
    'admin', 'analytics', 'audit', 'creator', 'loans', 
    'manager', 'member', 'members', 'owner', 'reports', 
    'roles', 'savings', 'settings', 'staff', 'super_admin', 
    'teller', 'transactions', 'users'
];

foreach ($directories as $dir) {
    $dirPath = $baseDir . $dir;
    if (is_dir($dirPath)) {
        $files = glob($dirPath . '/*.html');
        $htmlFiles = array_merge($htmlFiles, $files);
    }
}

// Add root HTML files
$rootFiles = glob($baseDir . '*.html');
$htmlFiles = array_merge($htmlFiles, $rootFiles);

echo "Found " . count($htmlFiles) . " HTML files\n\n";

foreach ($htmlFiles as $file) {
    fixUrlsInFile($file, $baseDir);
}

echo "\n========================================\n";
echo "SUMMARY:\n";
echo "========================================\n";
echo "Files fixed: $fixedCount\n";
echo "Errors: $errorCount\n";
echo "Total processed: " . count($htmlFiles) . "\n";
?>
