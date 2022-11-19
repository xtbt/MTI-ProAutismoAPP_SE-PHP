<?php
    // REQUIRED MODULES
    require_once( './Controllers/APIController.php' );
    require_once( './Models/UserProfile.php' );
    
    // USER CONTROLLER
    class UserProfileController extends APIController {

        private $requestMethod = NULL;
        private $resourceId = NULL;
        private $queryString = NULL;
        private $requestBody = NULL;
        private $response = NULL;

        private $resourceObject = NULL;

        public function __construct( $requestMethod, $resourceId, $queryString, $requestBody ) {
            $this->requestMethod = $requestMethod;
            $this->resourceId = $resourceId;
            $this->queryString = $queryString;
            $this->requestBody = $requestBody;

            $this->resourceObject = new UserProfile();
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
                    $response = $this->updateRecord();
                    break;
                case 'PATCH':
                    $response = $this->modifyRecord();
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
            $UserProfileId = isset($this->queryString['UserProfileId']) ? $this->queryString['UserProfileId'] : NULL;
            $Token = isset($this->queryString['Token']) ? $this->queryString['Token'] : NULL;
            $result = $this->resourceObject->isValidToken( $UserProfileId, $Token );
            if ( $result['count'] < 1 )
                return $this->unauthorizedResponse($result);
            else
                return $this->okResponse($result);
        }

        private function doLogin() {
            if (!isset($this->requestBody['data']['UserProfileId']))
                return $this->notAcceptableResponse('Missing parameters');
            $UserProfileId = $this->requestBody['data']['UserProfileId'];
            $result = $this->resourceObject->login( $UserProfileId );
            if ( $result['count'] < 1 )
                return $this->unauthorizedResponse($result);
            else
                return $this->okResponse($result);
        }

        private function doLogoff() {
            if (!isset($this->requestBody['data']['UserProfileId']))
                return $this->notAcceptableResponse('Missing parameters');
            $UserProfileId = $this->requestBody['data']['UserProfileId'];
            $result = $this->resourceObject->logoff( $UserProfileId );
            if ( $result['count'] < 1 )
                return $this->unprocessableEntityResponse( $result );
            else
                return $this->okResponse( $result );
        }

        private function getSingleRecord() {
            $result = $this->resourceObject->getUserProfile($this->resourceId);
            if ( $result['count'] < 1 )
                return $this->notFoundResponse($result);
            else
                return $this->okResponse($result);
        }

        private function getAllRecords() {
            $result = $this->resourceObject->getAll($this->queryString);
            if ( $result['count'] < 1 )
                return $this->notFoundResponse($result);
            else
                return $this->okResponse($result);
        }

        private function createRecord() {
            if (!isset($this->requestBody['data']['UserId']) || !isset($this->requestBody['data']['FirstName']) 
            || !isset($this->requestBody['data']['LastName']))
                return $this->notAcceptableResponse('Missing parameters');
            
            $UserId = $this->requestBody['data']['UserId'];
            $FirstName = $this->requestBody['data']['FirstName'];
            $LastName = $this->requestBody['data']['LastName'];

            $result = $this->resourceObject->createUserProfile( $UserId, $FirstName, $LastName );
            
            if ( $result['count'] < 1 )
                return $this->unprocessableEntityResponse($result);
            else
                return $this->okResponse($result);
        }

        private function updateRecord() {
            // DEBUG ***********
            //return $this->debugResponse($this->requestBody);
            // DEBUG ***********
            if (!isset($this->requestBody['data']['UserProfileId']) || !isset($this->requestBody['data']['FirstName']) 
            || !isset($this->requestBody['data']['LastName']))
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $UserProfileId = $this->requestBody['data']['UserProfileId'];
            $FirstName = $this->requestBody['data']['FirstName'];
            $LastName = $this->requestBody['data']['LastName'];

            $result = $this->resourceObject->updateUserProfile( $UserProfileId, $FirstName, $LastName );

            if ( $result['count'] < 1 ) {
                return $this->unprocessableEntityResponse($result);
            } else {
               return $this->okResponse($result);
            };
        }

        private function modifyRecord() {
            // DEBUG ***********
            //return $this->debugResponse($this->requestBody);
            // DEBUG ***********
            if (!isset($this->requestBody['data']['UserProfileId']) || !isset($this->requestBody['data']['Action']))
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $UserProfileId = $this->requestBody['data']['UserProfileId'];

            if ( $this->requestBody['data']['Action'] == 'Deactivate' )
                $result = $this->resourceObject->deactivateUserProfile( $UserProfileId );
            else
                $result = $this->resourceObject->reactivateUserProfile( $UserProfileId );
            
            if ( $result['count'] < 1 ) {
                return $this->unprocessableEntityResponse($result);
            } else {
                return $this->okResponse($result);
            };
        }
    }
?>