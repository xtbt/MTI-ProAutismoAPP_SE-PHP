<?php
    // REQUIRED MODULES
    require_once( './Controllers/APIController.php' );
    require_once( './Models/User.php' );
    
    // USER CONTROLLER
    class UserController extends APIController {

        private $requestMethod = NULL;
        private $resourceId = NULL;
        private $queryString = NULL;
        private $requestBody = NULL;
        private $response = NULL;

        private $resourceObject = NULL;

        // $result = [
        //     'requestMethod' => $this->requestMethod,
        //     'resourceId'    => $this->resourceId,
        //     'queryString'   => $this->queryString,
        //     'requestBody'   => $this->requestBody
        // ];
        // return $this->debugResponse($result);

        public function __construct( $requestMethod, $resourceId, $queryString, $requestBody ) {
            $this->requestMethod = $requestMethod;
            $this->resourceId = $resourceId;
            $this->queryString = $queryString;
            $this->requestBody = $requestBody;

            $this->resourceObject = new User();
        }

        public function processRequest() {
            switch ( $this->requestMethod ) {
                case 'GET':
                    if ( NULL !== $this->resourceId ) {
                        if ( 'verifyToken' == $this->resourceId ) {
                            $response = $this->verifyToken();
                        } else {
                            $response = $this->getSingleRecord();
                        };
                    } else
                        $response = $this->getAllRecords();
                    break;
                case 'POST':
                    if ( NULL !== $this->resourceId ) {
                        if ( 'doLogin' == $this->resourceId )
                            $response = $this->doLogin();
                        else if ( 'doLogoff' == $this->resourceId )
                            $response = $this->doLogoff();
                        else
                            $response = $this->notAcceptableResponse('Incorrect use of resource');
                    } else
                        $response = $this->createRecord();
                    break;
                case 'PUT':
                    if ( NULL !== $this->resourceId ) {
                        if ( 'changePassword' == $this->resourceId )
                            $response = $this->changePassword();
                        else
                            $response = $this->notAcceptableResponse('Incorrect use of resource');
                    } else
                        $response = $this->updateRecord(); // TODO: PUT Implementation
                    break;
                case 'DELETE':
                        $response = $this->deleteRecord(); // TODO: DELETE Implementation
                    break;
                case 'OPTIONS':
                        $response = $this->noContentResponse();
                    break;
                default:
                    $response = $this->methodNotAllowedResponse();
            };
            return $response;
        }

        private function verifyToken() {
            $UserId = isset($this->queryString['UserId']) ? $this->queryString['UserId'] : NULL;
            $Token = isset($this->queryString['Token']) ? $this->queryString['Token'] : NULL;
            $result = $this->resourceObject->isValidToken( $UserId, $Token );
            if ( $result['count'] < 1 )
                return $this->unauthorizedResponse($result);
            else
                return $this->okResponse($result);
        }

        private function doLogin() {
            if (!isset($this->requestBody['data']['Username']) || !isset($this->requestBody['data']['Password']))
                return $this->notAcceptableResponse('Missing parameters');
            $Username = $this->requestBody['data']['Username'];
            $Password = $this->requestBody['data']['Password'];
            $result = $this->resourceObject->login( $Username, $Password );
            if ( $result['count'] < 1 )
                return $this->unauthorizedResponse($result);
            else
                return $this->okResponse($result);
        }

        private function doLogoff() {
            if (!isset($this->requestBody['data']['UserId']))
                return $this->notAcceptableResponse('Missing parameters');
            $UserId = $this->requestBody['data']['UserId'];
            $result = $this->resourceObject->logoff( $UserId );
            if ( $result['count'] < 1 )
                return $this->unprocessableEntityResponse( $result );
            else
                return $this->okResponse( $result );
        }

        private function changePassword() {
            if ( !isset($this->requestBody['data']['UserId']) || !isset($this->requestBody['data']['NewPassword']) )
                return $this->notAcceptableResponse('Missing parameters');
            $UserId = $this->requestBody['data']['UserId'];
            $NewPassword = $this->requestBody['data']['NewPassword'];
            $result = $this->resourceObject->updatePassword( $UserId, $NewPassword );
            if ( $result['count'] < 1 )
                return $this->unprocessableEntityResponse( $result );
            else
                return $this->okResponse( $result );
        }

        private function getSingleRecord() {
            $result = $this->resourceObject->getUser($this->resourceId);
            if ( $result['count'] < 1 )
                return $this->notFoundResponse($result);
            else
                return $this->okResponse($result);
        }

        private function getAllRecords() {
            // TODO: CALL A FUNCTION TO PROCESS THE FULL QUERYSTRING AND BUILD SEARCH CRITERIA
            $jsonCriteria = NULL;

            $result = $this->resourceObject->getAll($jsonCriteria);
            
            if ( $result['count'] < 1 )
                return $this->notFoundResponse($result);
            else
                return $this->okResponse($result);
        }

        private function createRecord() {
            if (!isset($this->requestBody['data']['Username']) || !isset($this->requestBody['data']['Password']) || 
            !isset($this->requestBody['data']['FirstName']) || !isset($this->requestBody['data']['LastName']))
                return $this->notAcceptableResponse('Missing parameters');
            
            $Username = $this->requestBody['data']['Username'];
            $Password = $this->requestBody['data']['Password'];
            $Email = isset($this->requestBody['data']['Email']) ? $this->requestBody['data']['Email'] : NULL;
            $PhoneNumber = isset($this->requestBody['data']['PhoneNumber']) ? $this->requestBody['data']['PhoneNumber'] : NULL;
            $FirstName = $this->requestBody['data']['FirstName'];
            $LastName = $this->requestBody['data']['LastName'];

            $result = $this->resourceObject->createUser( $Username, $Password, $Email, $PhoneNumber, $FirstName, $LastName );
            
            if ( $result['count'] < 1 )
                return $this->unprocessableEntityResponse($result);
            else
                return $this->okResponse($result);
        }
    }
?>