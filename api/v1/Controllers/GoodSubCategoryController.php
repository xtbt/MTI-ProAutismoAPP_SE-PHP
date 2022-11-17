<?php
    // REQUIRED MODULES
    require_once( './Controllers/APIController.php' );
    require_once( './Models/GoodSubCategory.php' );
    
    // USER CONTROLLER
    class GoodSubCategoryController extends APIController {

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

            $this->resourceObject = new GoodSubCategory();
        }

        public function processRequest() {
            switch ( $this->requestMethod ) {
                case 'GET':
                    if ( NULL !== $this->resourceId )
                        if ( 'statuses' === $this->resourceId )
                            $response = $this->getGoodsSubCategoriesStatuses();
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

        private function getGoodsSubCategoriesStatuses() {
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
            $result = $this->resourceObject->getGoodSubCategory($this->resourceId);
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
            if (!isset($this->requestBody['data']['GoodCategoryId'])
            || !isset($this->requestBody['data']['GoodSubCategoryName']))
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $GoodCategoryId = $this->requestBody['data']['GoodCategoryId'];
            $GoodSubCategoryName = $this->requestBody['data']['GoodSubCategoryName'];
            
            $result = $this->resourceObject->createGoodSubCategory($GoodCategoryId, $GoodSubCategoryName);

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
            if (!isset($this->requestBody['data']['GoodSubCategoryId']) 
            || !isset($this->requestBody['data']['GoodCategoryId']) 
            || !isset($this->requestBody['data']['GoodSubCategoryName']))
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $GoodSubCategoryId = $this->requestBody['data']['GoodSubCategoryId'];
            $GoodCategoryId = $this->requestBody['data']['GoodCategoryId'];
            $GoodSubCategoryName = $this->requestBody['data']['GoodSubCategoryName'];

            $result = $this->resourceObject->updateGoodSubCategory($GoodSubCategoryId, $GoodCategoryId, $GoodSubCategoryName);

            if ( $result['count'] < 1 ) {
                return $this->unprocessableEntityResponse($result);
            } else {
               return $this->okResponse($result);
            };
        }

        private function modifyRecord() {
            // DEBUG ***********
            //$result['debug'] = $this->requestBody;
            // DEBUG ***********
            if (!isset($this->requestBody['data']['GoodSubCategoryId']) 
            || !isset($this->requestBody['data']['Action']))
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $GoodSubCategoryId = $this->requestBody['data']['GoodSubCategoryId'];

            if ( $this->requestBody['data']['Action'] == 'Deactivate' )
                $result = $this->resourceObject->deactivateGoodSubCategory($GoodSubCategoryId);
            else
                $result = $this->resourceObject->reactivateGoodSubCategory($GoodSubCategoryId);
            
            if ( $result['count'] < 1 ) {
                return $this->unprocessableEntityResponse($result);
            } else {
                return $this->okResponse($result);
            };
        }
    }
?>