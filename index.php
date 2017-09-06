<?php

$db_name = 'NAME OF DB';
$db_user = 'DB USERNAME';
$db_password = 'DB PASSWORD';

spl_autoload_register(function ($class) {
    require 'classes' . DIRECTORY_SEPARATOR . $class . '.php';
});

header('Content-Type: application/json');

try {
    $controller = new Controller(new DB($db_name, $db_user, $db_password));
    $method = ($_REQUEST['method'] ?? 'index') . 'Action';
    if (!method_exists($controller, $method)) {
        throw new Exception('Method "' . $method . '" not found');
    }
    echo $controller->$method();
} catch (Exception $e){
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
