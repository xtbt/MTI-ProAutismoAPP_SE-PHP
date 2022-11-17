<?php
    // REQUIRED MODULES
    require_once( './Controllers/APIController.php' );
    require_once( './Models/Sale.php' );
    
    // USER CONTROLLER
    class SaleController extends APIController {

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

            $this->resourceObject = new Sale();
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
                    if ( 'doCheckout' == $this->resourceId )
                        $response = $this->doCheckout();
                    else
                        $response = $this->notAcceptableResponse('Incorrect use of resource');
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
            $result = $this->resourceObject->getSale($this->resourceId);
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

        private function doCheckout() {
            if ( !isset($this->requestBody['data']['ExitDate']) || !isset($this->requestBody['data']['UserId']) || 
                !isset($this->requestBody['data']['CustomerId']) || !isset($this->requestBody['data']['Details']) )
                return $this->notAcceptableResponse('Missing parameters');
            
            $ExitDate = $this->requestBody['data']['ExitDate'];
            $UserId = $this->requestBody['data']['UserId'];
            $CustomerId = $this->requestBody['data']['CustomerId'];
            $DetailsArray = $this->requestBody['data']['Details'];
            if ( count($DetailsArray) < 1 )
                return $this->notAcceptableResponse('No products present on cart');

            $result = $this->resourceObject->createSale( $ExitDate, $UserId, $CustomerId, $DetailsArray );

            if ( $result['count'] < 1 ) {
                return $this->unprocessableEntityResponse($result);
            } else {
                return $this->okResponse($result);
            };
        }

        // private function updateRecord() {
        //     if (!isset($this->requestBody['data']['ExitId']) || !isset($this->requestBody['data']['FirstName']) || 
        //         !isset($this->requestBody['data']['LastName']))
        //         return $this->notAcceptableResponse('Missing parameters');
            
        //     $ExitId = $this->requestBody['data']['ExitId'];
        //     $Email = isset($this->requestBody['data']['Email']) ? $this->requestBody['data']['Email'] : NULL;
        //     $PhoneNumber = isset($this->requestBody['data']['PhoneNumber']) ? $this->requestBody['data']['PhoneNumber'] : NULL;
        //     $FirstName = $this->requestBody['data']['FirstName'];
        //     $LastName = $this->requestBody['data']['LastName'];

        //     $result = $this->resourceObject->updateExit($ExitId, $Email, $PhoneNumber, $FirstName, $LastName);

        //     if ( $result['count'] < 1 ) {
        //         return $this->unprocessableEntityResponse($result);
        //     } else {
        //         return $this->okResponse($result);
        //     };
        // }
    }
?>