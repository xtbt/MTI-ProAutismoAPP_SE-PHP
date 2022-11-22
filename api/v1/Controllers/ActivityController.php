<?php
    // REQUIRED MODULES
    require_once( './Controllers/APIController.php' );
    require_once( './Models/Activity.php' );
    
    // USER CONTROLLER
    class ActivityController extends APIController {

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

            $this->resourceObject = new Activity();
        }

        public function processRequest() {
            switch ( $this->requestMethod ) {
                case 'GET':
                    if ( NULL !== $this->resourceId ) {
                        if ( 'statuses' === $this->resourceId )
                            $response = $this->getRecordsStatuses();
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
            $result = $this->resourceObject->getActivity($this->resourceId);
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
            if (!isset($this->requestBody['data']['ActivityCategoryId']) || !isset($this->requestBody['data']['ActivitySubCategoryId'])
            || !isset($this->requestBody['data']['ActivityBrandId']) || !isset($this->requestBody['data']['ActivityName'])
            || !isset($this->requestBody['data']['ActivitySalePrice']))
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $ActivityCategoryId = $this->requestBody['data']['ActivityCategoryId'];
            $ActivitySubCategoryId = $this->requestBody['data']['ActivitySubCategoryId'];
            $ActivityBrandId = $this->requestBody['data']['ActivityBrandId'];
            $ActivityName = $this->requestBody['data']['ActivityName'];
            $ActivitySalePrice = $this->requestBody['data']['ActivitySalePrice'];
            // Optional fields ------------------------------------------------
            $ActivityDescription = isset($this->requestBody['data']['ActivityDescription']) ? $this->requestBody['data']['ActivityDescription'] : NULL;
            $ActivityBarcode = isset($this->requestBody['data']['ActivityBarcode']) ? $this->requestBody['data']['ActivityBarcode'] : NULL;
            $ActivityComboId = isset($this->requestBody['data']['ActivityComboId']) ? $this->requestBody['data']['ActivityComboId'] : 0;

            $result = $this->resourceObject->createActivity($ActivityCategoryId, $ActivitySubCategoryId, $ActivityBrandId, $ActivityName, $ActivityDescription, $ActivitySalePrice, $ActivityBarcode, $ActivityComboId);

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
            if (!isset($this->requestBody['data']['ActivityId']) || !isset($this->requestBody['data']['ActivityCategoryId']) 
            || !isset($this->requestBody['data']['ActivitySubCategoryId']) || !isset($this->requestBody['data']['ActivityBrandId']) 
            || !isset($this->requestBody['data']['ActivityName']) || !isset($this->requestBody['data']['ActivitySalePrice']))
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $ActivityId = $this->requestBody['data']['ActivityId'];
            $ActivityCategoryId = $this->requestBody['data']['ActivityCategoryId'];
            $ActivitySubCategoryId = $this->requestBody['data']['ActivitySubCategoryId'];
            $ActivityBrandId = $this->requestBody['data']['ActivityBrandId'];
            $ActivityName = $this->requestBody['data']['ActivityName'];
            $ActivitySalePrice = $this->requestBody['data']['ActivitySalePrice'];
            // Optional fields ------------------------------------------------
            $ActivityDescription = isset($this->requestBody['data']['ActivityDescription']) ? $this->requestBody['data']['ActivityDescription'] : NULL;
            $ActivityBarcode = isset($this->requestBody['data']['ActivityBarcode']) ? $this->requestBody['data']['ActivityBarcode'] : NULL;
            $ActivityComboId = isset($this->requestBody['data']['ActivityComboId']) ? $this->requestBody['data']['ActivityComboId'] : 0;

            $result = $this->resourceObject->updateActivity($ActivityId, $ActivityCategoryId, $ActivitySubCategoryId, $ActivityBrandId, $ActivityName, $ActivityDescription, $ActivitySalePrice, $ActivityBarcode, $ActivityComboId);

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
            if (!isset($this->requestBody['data']['ActivityId']) || !isset($this->requestBody['data']['Action']))
                return $this->notAcceptableResponse('Missing parameters');
            
            // Required fields ------------------------------------------------
            $ActivityId = $this->requestBody['data']['ActivityId'];

            if ( $this->requestBody['data']['Action'] == 'Deactivate' )
                $result = $this->resourceObject->deactivateActivity($ActivityId);
            else
                $result = $this->resourceObject->reactivateActivity($ActivityId);
            
            if ( $result['count'] < 1 ) {
                return $this->unprocessableEntityResponse($result);
            } else {
                return $this->okResponse($result);
            };
        }
    }
?>