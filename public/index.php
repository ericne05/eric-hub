<?php
// Add CSP header to allow Google Drive iframe
header("Content-Security-Policy: frame-src 'self' https://drive.google.com https://*.google.com; script-src 'self' 'unsafe-inline' https://drive.google.com https://*.google.com; style-src 'self' 'unsafe-inline';");

// Simple router

require_once __DIR__ . '/../app/Controllers/TestController.php';
require_once __DIR__ . '/../app/Controllers/WritingController.php';

// Check if this is first visit (no skill parameter)
if (!isset($_GET['skill'])) {
    // Show welcome page
    require_once __DIR__ . '/../app/Views/welcome.php';
    exit;
}

$skill = $_GET['skill'];
$testId = isset($_GET['test']) ? (int)$_GET['test'] : 1;

// Use WritingController for writing skill
if ($skill === 'writing') {
    $controller = new WritingController();
} else {
    $controller = new TestController();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($skill === 'writing') {
        $controller->submit($testId);
    } else {
        $controller->submit($skill, $testId);
    }
} else {
    if ($skill === 'writing') {
        $controller->index($testId);
    } else {
        $controller->index($skill, $testId);
    }
}
