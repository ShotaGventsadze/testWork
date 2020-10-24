<?php

    require_once("ApiControllers/Customer.php");

    //შემოწმება არის თუ არა მოთხოვნა POST
    if (strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0) {
        throw new Exception('არასწორი მოთხოვნის ტიპი');
    }

    //შემოწმება არის თუ არა მოთხოვნა application/json
    $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
    if (strpos($contentType, 'application/json') === false) {
        throw new Exception('არასწორი ფორმატი: application/json');
    }


    //json დამუშავება
    $content = trim(file_get_contents("php://input"));

    $decoded = json_decode($content, true);


    if (!is_array($decoded)) {
        throw new Exception('არასწორი ფორმატი JSON!');
    }

    $functionName = $decoded["apiMethod"];
    $controller = $decoded["apiController"];
    $functionPars = $decoded["parameters"];
    $helper = new $controller();
    $helper->post($functionName, $functionPars);

?>