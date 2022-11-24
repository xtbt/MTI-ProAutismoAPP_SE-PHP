<?php
    // REQUIRED MODULES
    require_once( './Controllers/APIController.php' );
    require_once( './Models/TaskNode.php' );
    
    // USER CONTROLLER
    class TaskNodeController extends APIController {

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

            $this->resourceObject = new TaskNode();
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
            $result = $this->resourceObject->getTaskNode($this->resourceId);
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
            if (!isset( $this->requestBody['data']['TaskId'] )
            || !isset( $this->requestBody['data']['TaskNodeName'])
            || !isset( $this->requestBody['data']['TaskNodeFatherId'])
            || !isset( $this->requestBody['data']['TaskNodeDescription']))
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $TaskId = $this->requestBody['data']['TaskId'];
            $TaskNodeName = $this->requestBody['data']['TaskNodeName'];
            $TaskNodeFatherId = $this->requestBody['data']['TaskNodeFatherId'];
            $TaskNodeDescription = $this->requestBody['data']['TaskNodeDescription'];
            
            $result = $this->resourceObject->createTaskNode( $TaskId, $TaskNodeName, $TaskNodeFatherId, $TaskNodeDescription );

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
            if (!isset( $this->requestBody['data']['TaskNodeId'] ) 
            || !isset( $this->requestBody['data']['TaskId'] ) 
            || !isset( $this->requestBody['data']['TaskNodeName']) )
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $TaskNodeId = $this->requestBody['data']['TaskNodeId'];
            $TaskId = $this->requestBody['data']['TaskId'];
            $TaskNodeName = $this->requestBody['data']['TaskNodeName'];

            $result = $this->resourceObject->updateTaskNode( $TaskNodeId, $TaskId, $TaskNodeName );

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
            if (!isset( $this->requestBody['data']['TaskNodeId'] ) 
            || !isset( $this->requestBody['data']['Action']) )
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $TaskNodeId = $this->requestBody['data']['TaskNodeId'];

            if ( $this->requestBody['data']['Action'] == 'Deactivate' )
                $result = $this->resourceObject->deactivateTaskNode( $TaskNodeId );
            else
                $result = $this->resourceObject->reactivateTaskNode( $TaskNodeId );
            
            if ( $result['count'] < 1 ) {
                return $this->unprocessableEntityResponse($result);
            } else {
                return $this->okResponse($result);
            };
        }
    }
?>