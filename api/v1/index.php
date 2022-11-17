<?php
    // Main RESTful API script
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,PATCH,DELETE");
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    require_once( './config.php' );

    $requestMethod = NULL;
    $resourceType = NULL;
	$resourceId = NULL;
    $queryString = NULL;
    $requestBody = NULL;

    $apiResponse = [
        'statusCodeHeader' => 'HTTP/1.1 400 Bad Request', 
        'httpResponseCode' => 400, 
        'error' => 'Error: Bad request',
        'debug' => 'INITIALIZATION'
    ];

    $requestMethod = $_SERVER['REQUEST_METHOD'];

    $uri = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
    $uri = explode( '/', $uri );
    if (count($uri) > 5 && count($uri) < 8) {
        $resourceType = isset($uri[5]) ? $uri[5] : NULL;
        $resourceId = isset($uri[6]) ? $uri[6] : NULL;
    }
    parse_str($_SERVER['QUERY_STRING'], $queryString);
    $requestBody = $requestMethod == 'POST' || $requestMethod == 'PUT' || $requestMethod == 'PATCH' ? json_decode(file_get_contents('php://input'), true) : NULL;

    if ( $resourceType ) {
        switch  ( $resourceType ) {
            case 'users':
                require_once( './Controllers/UserController.php' );
                $controllerObject = new UserController( $requestMethod, $resourceId, $queryString, $requestBody );
                $apiResponse = $controllerObject->processRequest();
                break;
            case 'sales':
                require_once( './Controllers/SaleController.php' );
                $controllerObject = new SaleController( $requestMethod, $resourceId, $queryString, $requestBody );
                $apiResponse = $controllerObject->processRequest();
                break;
            default:
                $apiResponse = [
                    'statusCodeHeader' => 'HTTP/1.1 404 Not Found', 
                    'httpResponseCode' => 404, 
                    'error' => 'Error: Resource not found.',
                    'debug' => [
                        'requestMethod' => $requestMethod,
                        'resourceType' => $resourceType,
                        'resourceId' => $resourceId,
                        'queryString' => $queryString,
                        'requestBody' => $requestBody,
                        'requestURI' => $_SERVER['REQUEST_URI']
                    ]
                ];
        };
    };
    header( $apiResponse['statusCodeHeader'] );
    http_response_code( $apiResponse['httpResponseCode'] );
    echo json_encode ( $apiResponse );
?>