<?php

class Controller {
    protected function view($name, $data = []) {
        // Automatically determine base_url
        // On Railway (production), the app is usually at the root '/'
        // On XAMPP (local), it's usually at '/thrift_pos'
        $scriptName = $_SERVER['SCRIPT_NAME']; // e.g., /index.php or /thrift_pos/public/index.php
        $scriptDir = dirname($scriptName);     // e.g., / or /thrift_pos/public
        
        // If we're serving from public/, the base_url is the parent directory
        $baseUrl = (strpos($scriptDir, '/public') !== false) ? dirname($scriptDir) : $scriptDir;
        $baseUrl = rtrim($baseUrl, '/'); // Remove trailing slash
        
        $data['base_url'] = $baseUrl;
        
        extract($data);
        require_once __DIR__ . "/../../../views/$name.php";
    }

    protected function json($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function redirect($url) {
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $scriptDir = dirname($scriptName);
        $baseUrl = (strpos($scriptDir, '/public') !== false) ? dirname($scriptDir) : $scriptDir;
        $baseUrl = rtrim($baseUrl, '/');
        
        // If the URL is relative to the project root (starts with /), prepend the base_url
        if (strpos($url, '/') === 0) {
            $url = $baseUrl . $url;
        }
        
        header("Location: $url");
        exit;
    }
}
