<?php
    // REQUIRED MODULES
    require_once( './Controllers/APIController.php' );
    require_once( './Models/GoodCategory.php' );
    
    // USER CONTROLLER
    class GoodCategoryController extends APIController {

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

            $this->resourceObject = new GoodCategory();
        }

        public function processRequest() {
            switch ( $this->requestMethod ) {
                case 'GET':
                    if ( NULL !== $this->resourceId ) {
                        if ( 'statuses' === $this->resourceId )
                            $response = $this->getGoodsCategoriesStatuses();
                        else
                            $response = $this->getSingleRecord();
                    } else
                        $response = $this->getAllRecords();
                    break;
                case 'POST':
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

        private function getGoodsCategoriesStatuses() {
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
            $result = $this->resourceObject->getGoodCategory($this->resourceId);
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

        private function createRecord() {
            // DEBUG ***********
            //$result['debug'] = $this->requestBody;
            // DEBUG ***********
            if (!isset($this->requestBody['data']['GoodCategoryName']))
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $GoodCategoryName = $this->requestBody['data']['GoodCategoryName'];
            
            $result = $this->resourceObject->createGoodCategory($GoodCategoryName);

            if ( $result['count'] < 1 ) {
                return $this->unprocessableEntityResponse($result);
            } else {
               return $this->okResponse($result);
            };
        }

        private function updateRecord() {
            // DEBUG ***********
            //$result['debug'] = $this->requestBody;
            // DEBUG ***********
            if (!isset($this->requestBody['data']['GoodCategoryId']) || !isset($this->requestBody['data']['GoodCategoryName']))
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $GoodCategoryId = $this->requestBody['data']['GoodCategoryId'];
            $GoodCategoryName = $this->requestBody['data']['GoodCategoryName'];

            $result = $this->resourceObject->updateGoodCategory($GoodCategoryId, $GoodCategoryName);

            if ( $result['count'] < 1 ) {
                return $this->unprocessableEntityResponse($result);
            } else {
               return $this->okResponse($result);
            };
        }

        private function modifyRecord() {
            // DEBUG ***********
            $result['debug'] = $this->requestBody;
            // DEBUG ***********
            if (!isset($this->requestBody['data']['GoodCategoryId']) || !isset($this->requestBody['data']['Action']))
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $GoodCategoryId = $this->requestBody['data']['GoodCategoryId'];

            if ( $this->requestBody['data']['Action'] == 'Deactivate' )
                $result = $this->resourceObject->deactivateGoodCategory($GoodCategoryId);
            else
                $result = $this->resourceObject->reactivateGoodCategory($GoodCategoryId);
            
            if ( $result['count'] < 1 ) {
                return $this->unprocessableEntityResponse($result);
            } else {
                return $this->okResponse($result);
            };
        }
    }
?>