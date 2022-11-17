<?php
    // REQUIRED MODULES
    require_once( './Controllers/APIController.php' );
    require_once( './Models/Customer.php' );
    
    // USER CONTROLLER
    class CustomerController extends APIController {

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

            $this->resourceObject = new Customer();
        }

        public function processRequest() {
            switch ( $this->requestMethod ) {
                case 'GET':
                    if ( NULL !== $this->resourceId )
                        $response = $this->getSingleRecord();
                    else
                        $response = $this->getAllRecords();
                    break;
                case 'POST':
                        $response = $this->createRecord();
                    break;
                case 'PUT':
                        $response = $this->updateRecord();
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

        private function getSingleRecord() {
            $result = $this->resourceObject->getCustomer($this->resourceId);
            if ( $result['count'] < 1 ) {
                return $this->notFoundResponse($result);
            } else {
                return $this->okResponse($result);
            };
        }

        private function getAllRecords() {
            // TODO: CALL A FUNCTION TO PROCESS THE FULL QUERYSTRING AND BUILD SEARCH CRITERIA
            $jsonCriteria = NULL;

            $result = $this->resourceObject->getAll($jsonCriteria);
            
            if ( $result['count'] < 1 ) {
                return $this->notFoundResponse($result);
            } else {
                return $this->okResponse($result);
            };
        }

        private function createRecord() {
            if (!isset($this->requestBody['data']['FirstName']) || !isset($this->requestBody['data']['LastName']))
                return $this->notAcceptableResponse('Missing parameters');
            
            $Email = isset($this->requestBody['data']['Email']) ? $this->requestBody['data']['Email'] : NULL;
            $PhoneNumber = isset($this->requestBody['data']['PhoneNumber']) ? $this->requestBody['data']['PhoneNumber'] : NULL;
            $FirstName = $this->requestBody['data']['FirstName'];
            $LastName = $this->requestBody['data']['LastName'];

            $result = $this->resourceObject->createCustomer($Email, $PhoneNumber, $FirstName, $LastName);

            if ( $result['count'] < 1 ) {
                return $this->unprocessableEntityResponse($result);
            } else {
                return $this->okResponse($result);
            };
        }

        private function updateRecord() {
            if (!isset($this->requestBody['data']['CustomerId']) || !isset($this->requestBody['data']['FirstName']) || 
                !isset($this->requestBody['data']['LastName']))
                return $this->notAcceptableResponse('Missing parameters');
            
            $CustomerId = $this->requestBody['data']['CustomerId'];
            $Email = isset($this->requestBody['data']['Email']) ? $this->requestBody['data']['Email'] : NULL;
            $PhoneNumber = isset($this->requestBody['data']['PhoneNumber']) ? $this->requestBody['data']['PhoneNumber'] : NULL;
            $FirstName = $this->requestBody['data']['FirstName'];
            $LastName = $this->requestBody['data']['LastName'];

            $result = $this->resourceObject->updateCustomer($CustomerId, $Email, $PhoneNumber, $FirstName, $LastName);

            if ( $result['count'] < 1 ) {
                return $this->unprocessableEntityResponse($result);
            } else {
                return $this->okResponse($result);
            };
        }
    }
?>