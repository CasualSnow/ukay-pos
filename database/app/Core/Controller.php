<?php

class Controller {
    protected function view($name, $data = []) {
        $scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        $projectName = str_replace('\\', '/', dirname($scriptName));
        
        $data['base_url'] = $projectName;
        
        extract($data);
        require_once __DIR__ . "/../../../views/$name.php";
    }

    protected function json($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function redirect($url) {
        $scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        $projectName = str_replace('\\', '/', dirname($scriptName));
        
        // If the URL is relative to the project root, prepend the project name
        if (strpos($url, '/') === 0) {
            $url = $projectName . $url;
        }
        
        header("Location: $url");
        exit;
    }
}
