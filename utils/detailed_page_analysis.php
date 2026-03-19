<?php
/**
 * Detailed Page Analysis - KSP Lam Gabe Jaya
 * Render all pages fully to identify missing elements and issues
 */

echo "🔍 DETAILED PAGE ANALYSIS\n";
echo "===========================\n\n";

$base_url = 'http://localhost/mono';

$pages = [
    'login' => '/login.html',
    'dashboard' => '/dashboard.html',
    'users' => '/users.html',
    'users_crud' => '/users_crud.html',
    'members' => '/members.html',
    'members_crud' => '/members_crud.html',
    'loans' => '/loans.html',
    'loans_crud' => '/loans_crud.html',
    'savings' => '/savings.html',
    'savings_crud' => '/savings_crud.html',
    'reports' => '/reports.html',
    'settings' => '/settings.html',
    'notifications' => '/notifications.html',
    'audit_logs' => '/audit_logs.html',
    'risk_assessment' => '/risk_assessment.html'
];

function analyzePage($url, $page_name) {
    global $base_url;
    
    echo "🔍 Analyzing: $page_name\n";
    echo str_repeat("-", 60) . "\n";
    
    $response = @file_get_contents($base_url . $url);
    
    if ($response === false) {
        echo "❌ Failed to load page\n\n";
        return [];
    }
    
    $issues = [];
    $missing_elements = [];
    
    // DOM Analysis
    $dom = new DOMDocument();
    @$dom->loadHTML($response);
    
    // Basic structure
    $title = $dom->getElementsByTagName('title')->item(0);
    $head = $dom->getElementsByTagName('head')->item(0);
    $body = $dom->getElementsByTagName('body')->item(0);
    
    echo "📋 Title: " . ($title ? $title->textContent : 'MISSING') . "\n";
    echo "🗂️ Head: " . ($head ? 'Present' : 'MISSING') . "\n";
    echo "👤 Body: " . ($body ? 'Present' : 'MISSING') . "\n";
    
    // Essential elements
    $forms = $dom->getElementsByTagName('form');
    $tables = $dom->getElementsByTagName('table');
    $buttons = $dom->getElementsByTagName('button');
    $inputs = $dom->getElementsByTagName('input');
    $selects = $dom->getElementsByTagName('select');
    $textareas = $dom->getElementsByTagName('textarea');
    
    echo "\n📝 Forms: " . $forms->length . "\n";
    echo "📊 Tables: " . $tables->length . "\n";
    echo "🔘 Buttons: " . $buttons->length . "\n";
    echo "📥 Inputs: " . $inputs->length . "\n";
    echo "📋 Selects: " . $selects->length . "\n";
    echo "📄 Textareas: " . $textareas->length . "\n";
    
    // Check for missing elements in CRUD pages
    if (strpos($page_name, 'crud') !== false) {
        if ($forms->length == 0) {
            $issues[] = "No forms found in CRUD page";
            $missing_elements[] = 'forms';
        }
        if ($tables->length == 0) {
            $issues[] = "No tables found in CRUD page";
            $missing_elements[] = 'tables';
        }
        if ($buttons->length < 3) {
            $issues[] = "Insufficient buttons in CRUD page";
            $missing_elements[] = 'buttons';
        }
    }
    
    // Check for navigation elements
    $nav_links = $dom->getElementsByTagName('a');
    $logout_links = 0;
    $dashboard_links = 0;
    
    foreach ($nav_links as $link) {
        $href = $link->getAttribute('href');
        if (strpos($href, 'login') !== false) $logout_links++;
        if (strpos($href, 'dashboard') !== false) $dashboard_links++;
    }
    
    echo "🔗 Navigation Links: " . $nav_links->length . "\n";
    echo "🚪 Logout Links: " . $logout_links . "\n";
    echo "🏠 Dashboard Links: " . $dashboard_links . "\n";
    
    if ($logout_links == 0 && $page_name !== 'login') {
        $issues[] = "No logout link found";
        $missing_elements[] = 'logout_link';
    }
    
    // Check for JavaScript dependencies
    $scripts = $dom->getElementsByTagName('script');
    $jquery_loaded = false;
    $bootstrap_loaded = false;
    
    foreach ($scripts as $script) {
        $src = $script->getAttribute('src');
        if ($src && strpos($src, 'jquery') !== false) $jquery_loaded = true;
        if ($src && strpos($src, 'bootstrap') !== false) $bootstrap_loaded = true;
    }
    
    echo "⚙️ Scripts: " . $scripts->length . "\n";
    echo "📦 jQuery: " . ($jquery_loaded ? 'Loaded' : 'MISSING') . "\n";
    echo "🎨 Bootstrap: " . ($bootstrap_loaded ? 'Loaded' : 'MISSING') . "\n";
    
    if (!$jquery_loaded && $page_name !== 'login') {
        $issues[] = "jQuery not loaded";
        $missing_elements[] = 'jquery';
    }
    
    if (!$bootstrap_loaded) {
        $issues[] = "Bootstrap not loaded";
        $missing_elements[] = 'bootstrap';
    }
    
    // Check for API endpoint issues
    if (strpos($response, '/api/') !== false && strpos($response, '/mono/api/') === false) {
        $issues[] = "API endpoints missing /mono/ prefix";
        $missing_elements[] = 'api_prefix';
    }
    
    if (strpos($response, '/login.html') !== false && strpos($response, '/mono/login.html') === false) {
        $issues[] = "Login redirects missing /mono/ prefix";
        $missing_elements[] = 'login_prefix';
    }
    
    // Check for modal elements
    $modals = [];
    foreach ($dom->getElementsByTagName('div') as $div) {
        $class = $div->getAttribute('class');
        if ($class && strpos($class, 'modal') !== false) {
            $modals[] = $class;
        }
    }
    
    echo "🪟 Modals: " . count($modals) . "\n";
    
    if (strpos($page_name, 'crud') !== false && count($modals) == 0) {
        $issues[] = "No modals found in CRUD page";
        $missing_elements[] = 'modals';
    }
    
    // Check for data loading functions
    $script_content = '';
    foreach ($scripts as $script) {
        if ($script->nodeValue) {
            $script_content .= $script->nodeValue;
        }
    }
    
    $has_load_function = strpos($script_content, 'function load') !== false;
    $has_ajax_calls = strpos($script_content, '$.ajax') !== false || strpos($script_content, 'fetch(') !== false;
    
    echo "🔄 Load Functions: " . ($has_load_function ? 'Present' : 'MISSING') . "\n";
    echo "🌐 AJAX Calls: " . ($has_ajax_calls ? 'Present' : 'MISSING') . "\n";
    
    if (strpos($page_name, 'crud') !== false && !$has_load_function) {
        $issues[] = "No data loading functions found";
        $missing_elements[] = 'load_functions';
    }
    
    if (strpos($page_name, 'crud') !== false && !$has_ajax_calls) {
        $issues[] = "No AJAX calls found";
        $missing_elements[] = 'ajax_calls';
    }
    
    // Report issues
    if (!empty($issues)) {
        echo "\n⚠️ Issues Found:\n";
        foreach ($issues as $issue) {
            echo "  ❌ $issue\n";
        }
    } else {
        echo "\n✅ No obvious structural issues detected\n";
    }
    
    echo "\n";
    
    return [
        'issues' => $issues,
        'missing_elements' => $missing_elements,
        'stats' => [
            'forms' => $forms->length,
            'tables' => $tables->length,
            'buttons' => $buttons->length,
            'scripts' => $scripts->length,
            'modals' => count($modals),
            'nav_links' => $nav_links->length
        ]
    ];
}

// Analyze all pages
$all_issues = [];
$page_stats = [];

foreach ($pages as $name => $url) {
    $analysis = analyzePage($url, $name);
    $all_issues[$name] = $analysis['issues'];
    $page_stats[$name] = $analysis['stats'];
}

// Summary report
echo "📊 SUMMARY REPORT\n";
echo "==================\n\n";

$total_issues = 0;
$pages_with_issues = 0;

foreach ($all_issues as $page => $issues) {
    $issue_count = count($issues);
    $total_issues += $issue_count;
    
    if ($issue_count > 0) {
        $pages_with_issues++;
        echo "🔍 $page: $issue_count issues\n";
        foreach ($issues as $issue) {
            echo "  - $issue\n";
        }
        echo "\n";
    }
}

echo "📈 Overall Statistics:\n";
echo "Total Pages: " . count($pages) . "\n";
echo "Pages with Issues: $pages_with_issues\n";
echo "Total Issues: $total_issues\n\n";

// Priority fixes needed
echo "🎯 PRIORITY FIXES NEEDED:\n";
echo "========================\n";

$common_issues = [];
foreach ($all_issues as $page => $issues) {
    foreach ($issues as $issue) {
        if (!isset($common_issues[$issue])) {
            $common_issues[$issue] = 0;
        }
        $common_issues[$issue]++;
    }
}

arsort($common_issues);
foreach ($common_issues as $issue => $count) {
    echo "🔧 $issue (affects $count pages)\n";
}

echo "\n🚀 Ready for systematic fixes!\n";
?>
