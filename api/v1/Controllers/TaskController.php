<?php
    // REQUIRED MODULES
    require_once( './Controllers/APIController.php' );
    require_once( './Models/Task.php' );
    
    // USER CONTROLLER
    class TaskController extends APIController {

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

            $this->resourceObject = new Task();
        }

        public function processRequest() {
            switch ( $this->requestMethod ) {
                case 'GET':
                    if ( NULL !== $this->resourceId )
                        if ( 'statuses' === $this->resourceId )
                            $response = $this->getRecordsStatuses();
                        else
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
                case 'PATCH':
                        $response = $this->modifyRecord(); // TODO: PATCH Implementation
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

        private function getRecordsStatuses() {
            // DEBUG ***********
            //return $this->debugResponse($this->queryString);
            // DEBUG ***********
            $result = $this->resourceObject->getStatuses($this->queryString);
            if ( $result['count'] < 1 ) {
                return $this->notFoundResponse($result);
            } else {
                return $this->okResponse($result);
            };
        }

        private function getSingleRecord() {
            $result = $this->resourceObject->getTask($this->resourceId);
            if ( $result['count'] < 1 ) {
                return $this->notFoundResponse($result);
            } else {
                return $this->okResponse($result);
            };
        }

        private function getAllRecords() {
            // DEBUG ***********
            //return $this->debugResponse($this->queryString);
            // DEBUG ***********
            $result = $this->resourceObject->getAll($this->queryString);
            if ( $result['count'] < 1 ) {
                return $this->notFoundResponse($result);
            } else {
                return $this->okResponse($result);
            };
        }

        private function createRecord() {
            // DEBUG ***********
            //return $this->debugResponse($this->requestBody);
            // DEBUG ***********
            if (!isset( $this->requestBody['data']['TaskType'] )
            || !isset( $this->requestBody['data']['TaskTitle']) )
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $TaskType = $this->requestBody['data']['TaskType'];
            $TaskTitle = $this->requestBody['data']['TaskTitle'];
            
            $result = $this->resourceObject->createTask( $TaskType, $TaskTitle );

            if ( $result['count'] < 1 ) {
                return $this->unprocessableEntityResponse($result);
            } else {
               return $this->okResponse($result);
            };
        }

        private function updateRecord() {
            // DEBUG ***********
            //return $this->debugResponse($this->requestBody);
            // DEBUG ***********
            if (!isset( $this->requestBody['data']['TaskId'] ) 
            || !isset( $this->requestBody['data']['TaskType'] ) 
            || !isset( $this->requestBody['data']['TaskTitle']) )
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $TaskId = $this->requestBody['data']['TaskId'];
            $TaskType = $this->requestBody['data']['TaskType'];
            $TaskTitle = $this->requestBody['data']['TaskTitle'];

            $result = $this->resourceObject->updateTask( $TaskId, $TaskType, $TaskTitle );

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
            if (!isset( $this->requestBody['data']['TaskId'] ) 
            || !isset( $this->requestBody['data']['Action']) )
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $TaskId = $this->requestBody['data']['TaskId'];

            if ( $this->requestBody['data']['Action'] == 'Deactivate' )
                $result = $this->resourceObject->deactivateTask( $TaskId );
            else
                $result = $this->resourceObject->reactivateTask( $TaskId );
            
            if ( $result['count'] < 1 ) {
                return $this->unprocessableEntityResponse($result);
            } else {
                return $this->okResponse($result);
            };
        }
    }
?>