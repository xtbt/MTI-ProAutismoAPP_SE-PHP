<?php
    // REQUIRED MODULES
    require_once( './Controllers/APIController.php' );
    require_once( './Models/Combo.php' );
    
    // USER CONTROLLER
    class ComboController extends APIController {

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

            $this->resourceObject = new Combo();
        }

        public function processRequest() {
            switch ( $this->requestMethod ) {
                case 'GET':
                    if ( NULL !== $this->resourceId ) {
                        if ( 'available' == $this->resourceId )
                            $response = $this->getAvailableRecords();
                        else if ( 'statuses' === $this->resourceId )
                            $response = $this->getCombosStatuses();
                        else
                            $response = $this->getSingleRecord();
                    }
                    else
                        $response = $this->getAllRecords();
                    break;
                case 'POST':
                        $response = $this->createRecord(); // TODO: POST Implementation
                    break;
                case 'PUT':
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

        private function getCombosStatuses() {
            $result = $this->resourceObject->getStatuses($this->queryString);
            // DEBUG ***********
            //$result['debug'] = $this->queryString;
            // DEBUG ***********
            if ( $result['count'] < 1 ) {
                return $this->notFoundResponse($result);
            } else {
                return $this->okResponse($result);
            };
        }
        
        private function getSingleRecord() {
            $result = $this->resourceObject->getCombo($this->resourceId);
            // DEBUG ***********
            //$result['debug'] = $this->resourceId;
            // DEBUG ***********
            if ( $result['count'] < 1 ) {
                return $this->notFoundResponse($result);
            } else {
                return $this->okResponse($result);
            };
        }

        private function getAllRecords() {
            $result = $this->resourceObject->getAll($this->queryString);
            // DEBUG ***********
            $result['debug'] = $this->queryString;
            // DEBUG ***********
            if ( $result['count'] < 1 ) {
                return $this->notFoundResponse($result);
            } else {
                return $this->okResponse($result);
            };
        }

        private function getAvailableRecords() {
            $result = $this->resourceObject->getAvailableCombos();

            if ( $result['count'] < 1 ) {
                return $this->notFoundResponse($result);
            } else {
                return $this->okResponse($result);
            };
        }
    }
?>