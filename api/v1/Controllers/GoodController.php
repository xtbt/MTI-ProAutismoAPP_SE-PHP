<?php
    // REQUIRED MODULES
    require_once( './Controllers/APIController.php' );
    require_once( './Models/Good.php' );
    
    // USER CONTROLLER
    class GoodController extends APIController {

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

            $this->resourceObject = new Good();
        }

        public function processRequest() {
            switch ( $this->requestMethod ) {
                case 'GET':
                    if ( NULL !== $this->resourceId ) {
                        if ( 'statuses' === $this->resourceId )
                            $response = $this->getGoodsStatuses();
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

        private function getGoodsStatuses() {
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
            $result = $this->resourceObject->getGood($this->resourceId);
            if ( $result['count'] < 1 ) {
                return $this->notFoundResponse($result);
            } else {
                return $this->okResponse($result);
            };
        }

        private function getAllRecords() {
            $result = $this->resourceObject->getAll($this->queryString);
            // DEBUG ***********
            //$result['debug'] = $this->queryString;
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
            if (!isset($this->requestBody['data']['GoodCategoryId']) || !isset($this->requestBody['data']['GoodSubCategoryId'])
            || !isset($this->requestBody['data']['GoodBrandId']) || !isset($this->requestBody['data']['GoodName'])
            || !isset($this->requestBody['data']['GoodSalePrice']))
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $GoodCategoryId = $this->requestBody['data']['GoodCategoryId'];
            $GoodSubCategoryId = $this->requestBody['data']['GoodSubCategoryId'];
            $GoodBrandId = $this->requestBody['data']['GoodBrandId'];
            $GoodName = $this->requestBody['data']['GoodName'];
            $GoodSalePrice = $this->requestBody['data']['GoodSalePrice'];
            // Optional fields ------------------------------------------------
            $GoodDescription = isset($this->requestBody['data']['GoodDescription']) ? $this->requestBody['data']['GoodDescription'] : NULL;
            $GoodBarcode = isset($this->requestBody['data']['GoodBarcode']) ? $this->requestBody['data']['GoodBarcode'] : NULL;
            $GoodComboId = isset($this->requestBody['data']['GoodComboId']) ? $this->requestBody['data']['GoodComboId'] : 0;

            $result = $this->resourceObject->createGood($GoodCategoryId, $GoodSubCategoryId, $GoodBrandId, $GoodName, $GoodDescription, $GoodSalePrice, $GoodBarcode, $GoodComboId);

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
            if (!isset($this->requestBody['data']['GoodId']) || !isset($this->requestBody['data']['GoodCategoryId']) 
            || !isset($this->requestBody['data']['GoodSubCategoryId']) || !isset($this->requestBody['data']['GoodBrandId']) 
            || !isset($this->requestBody['data']['GoodName']) || !isset($this->requestBody['data']['GoodSalePrice']))
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $GoodId = $this->requestBody['data']['GoodId'];
            $GoodCategoryId = $this->requestBody['data']['GoodCategoryId'];
            $GoodSubCategoryId = $this->requestBody['data']['GoodSubCategoryId'];
            $GoodBrandId = $this->requestBody['data']['GoodBrandId'];
            $GoodName = $this->requestBody['data']['GoodName'];
            $GoodSalePrice = $this->requestBody['data']['GoodSalePrice'];
            // Optional fields ------------------------------------------------
            $GoodDescription = isset($this->requestBody['data']['GoodDescription']) ? $this->requestBody['data']['GoodDescription'] : NULL;
            $GoodBarcode = isset($this->requestBody['data']['GoodBarcode']) ? $this->requestBody['data']['GoodBarcode'] : NULL;
            $GoodComboId = isset($this->requestBody['data']['GoodComboId']) ? $this->requestBody['data']['GoodComboId'] : 0;

            $result = $this->resourceObject->updateGood($GoodId, $GoodCategoryId, $GoodSubCategoryId, $GoodBrandId, $GoodName, $GoodDescription, $GoodSalePrice, $GoodBarcode, $GoodComboId);

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
            if (!isset($this->requestBody['data']['GoodId']) || !isset($this->requestBody['data']['Action']))
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $GoodId = $this->requestBody['data']['GoodId'];

            if ( $this->requestBody['data']['Action'] == 'Deactivate' )
                $result = $this->resourceObject->deactivateGood($GoodId);
            else
                $result = $this->resourceObject->reactivateGood($GoodId);
            
            if ( $result['count'] < 1 ) {
                return $this->unprocessableEntityResponse($result);
            } else {
                return $this->okResponse($result);
            };
        }
    }
?>