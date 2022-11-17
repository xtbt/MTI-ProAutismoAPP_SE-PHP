<?php
    require_once( './System/Database.php' );
    require_once( './Models/AppModelCore.php' );

    class Good extends AppModelCore {
        
        // Class properties
        public $GoodId;
        public $GoodCategoryId;
        public $GoodCategoryName;       // tblGoodsCategories:GoodCategoryName
        public $GoodSubCategoryId;
        public $GoodSubCategoryName;    // tblGoodsSubCategories:GoodSubCategoryName
        public $GoodBrandId;
        public $GoodBrandName;          // tblGoodsBrands:GoodBrandName
        public $GoodName;
        public $GoodDescription;
        public $GoodSalePrice;
        public $GoodBarcode;
        public $GoodComboId;
        public $GoodStatus;

        // Search criteria fields string
        private $SearchCriteriaFieldsString = 'CONCAT(COALESCE(GoodBrandName,""),"|",COALESCE(GoodName,""),"|",COALESCE(GoodDescription,""),"|",COALESCE(GoodBarcode,""))';

        // User contructor (DB Connection)
        public function __construct() {
            $this->DB_Connector = Database::getInstance()->getConnector(); // Get singleton DB connector
        }

        // Init DB properties -------------------------------------------------
        private function DB_initProperties() {
            $this->SQL_Tables = 'tblGoods AS t1 LEFT JOIN 
                                tblGoodsBrands AS t2 USING(GoodBrandId) LEFT JOIN 
                                tblGoodsCategories AS t3 USING(GoodCategoryId) LEFT JOIN 
                                tblGoodsSubCategories AS t4 USING(GoodSubCategoryId)';
            $this->SQL_Conditions = 'TRUE';
            $this->SQL_Order = 'GoodId';
            $this->SQL_Limit = NULL;
            $this->SQL_Params = [];
            $this->SQL_Sentence = NULL;
            
            $this->response['globalCount'] = -1;
            $this->response['count'] = -1;
            $this->response['data'] = NULL;
        }

        // Function that gets all rows in the Database
        // If criteria was defined, it filters the result
        public function getAll($queryString = NULL) {
            $this->DB_initProperties();
            if (!$this->buildSQLCriteria( $queryString, $this->SearchCriteriaFieldsString ))
                 return $this->response; // Return SQL criteria error
            
            try {
                $SQL_GlobalQuery = 'SELECT 
                    t1.GoodId AS GoodId, 
                    t1.GoodCategoryId AS GoodCategoryId, 
                    t3.GoodCategoryName AS GoodCategoryName, 
                    t1.GoodSubCategoryId AS GoodSubCategoryId, 
                    t4.GoodSubCategoryName AS GoodSubCategoryName, 
                    t1.GoodBrandId AS GoodBrandId, 
                    t2.GoodBrandName AS GoodBrandName, 
                    t1.GoodName AS GoodName, 
                    t1.GoodDescription AS GoodDescription, 
                    t1.GoodSalePrice AS GoodSalePrice, 
                    t1.GoodBarcode AS GoodBarcode, 
                    t1.GoodComboId AS GoodComboId, 
                    t1.GoodStatus AS GoodStatus 
                    FROM '
                    .$this->SQL_Tables.
                    ' WHERE '
                    .$this->SQL_Conditions.
                    ' ORDER BY '
                    .$this->SQL_Order;
                $SQL_Query = $SQL_GlobalQuery . (!is_null($this->SQL_Limit) ? ' LIMIT '.$this->SQL_Limit.';' : ';');
                
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->DB_loadParameters();
                $this->SQL_Sentence->execute();
                if ($this->SQL_Sentence->rowCount() < 1) {
                    $this->response['count'] = 0; // Empty result
                    $this->response['msj'] = '['.get_class($this).'] No records found';
                    return $this->response; // Returns response with no records
                };

                $this->DB_loadResponse(get_class($this)); // If records found, build response Array with DB info
                $this->DB_getGlobalCount($SQL_GlobalQuery); // Get global count of rows ignoring LIMIT
                return $this->response; // Return response with records
            }
            catch (PDOException $ex) {
                $this->response['msj'] = '['.get_class($this).'] Error: SQL Exception';
                $this->response['error'] = $ex->getMessage();
                return $this->response; // Return response with error
            };
        }

        // vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
        // CRUD FUNCTIONS START ***********************************************
        
        // Update class properties --------------------------------------------
        private function updateProperties($field_array) {
            foreach ($field_array AS $propertyName => $value) {
                $this->$propertyName = $value;
            };
        }

        // ********************************************************************
        // (READ) GET A SINGLE ROW ********************************************
        // ********************************************************************
        public function getGood($GoodId) {
            $this->DB_initProperties();
            if (is_numeric($GoodId)) {
                $this->SQL_Conditions .= ' AND GoodId = :GoodId';
                $this->SQL_Limit = '0,1';
            }
            else {
                $this->response['msj'] = '['.get_class($this).'] Error: Invalid parameter';
                return $this->response;
            };
            
            try {
                $SQL_Query = 'SELECT 
                    t1.GoodId AS GoodId, 
                    t1.GoodCategoryId AS GoodCategoryId, 
                    t3.GoodCategoryName AS GoodCategoryName, 
                    t1.GoodSubCategoryId AS GoodSubCategoryId, 
                    t4.GoodSubCategoryName AS GoodSubCategoryName, 
                    t1.GoodBrandId AS GoodBrandId, 
                    t2.GoodBrandName AS GoodBrandName, 
                    t1.GoodName AS GoodName, 
                    t1.GoodDescription AS GoodDescription, 
                    t1.GoodSalePrice AS GoodSalePrice, 
                    t1.GoodBarcode AS GoodBarcode, 
                    t1.GoodComboId AS GoodComboId, 
                    t1.GoodStatus AS GoodStatus 
                    FROM '
                    .$this->SQL_Tables.
                    ' WHERE '
                    .$this->SQL_Conditions.
                    (!is_null($this->SQL_Limit) ? ' LIMIT '.$this->SQL_Limit.';' : ';');
                
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':GoodId', $GoodId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                if ($this->SQL_Sentence->rowCount() < 1) {
                    $this->response['count'] = 0; // No records found
                    $this->response['msj'] = '['.get_class($this).'] No records found';
                    return $this->response; // Return response with no records
                };

                // If there is data, we build the response with DB info -------
                $this->response['data'][$GoodId] = $this->SQL_Sentence->fetch(PDO::FETCH_ASSOC);
                $this->updateProperties($this->response['data'][$GoodId]);
                $this->response['count'] = 1; // Unique record
                $this->response['globalCount'] = 1;
                // ------------------------------------------------------------

                return $this->response; // Return Array response
            }
            catch (PDOException $ex) {
                $this->response['msj'] = '['.get_class($this).'] Error: SQL Exception';
                $this->response['error'] = $ex->getMessage();
                return $this->response;
            };
        }

        // ********************************************************************
        // (CREATE) CREATE NEW RECORD INTO DB *********************************
        // ********************************************************************
        public function createGood($GoodCategoryId, $GoodSubCategoryId, $GoodBrandId, $GoodName, $GoodDescription, $GoodSalePrice, $GoodBarcode, $GoodComboId) {
            $this->DB_initProperties();
            $GoodId = NULL; // NULL by default on new records
            $GoodStatus = 1; // 1(Active) by default on new records

            ## TODO: VALIDATION INSTRUCTIONS FOR PARAMETERS -------------------
            ## ... 
            // Meanwhile ......
            if (empty($GoodCategoryId) || empty($GoodSubCategoryId) || empty($GoodBrandId) || empty($GoodName) || empty($GoodSalePrice)) {
                $this->response['error'] = '['.get_class($this).'] Error: Main fields cannot be empty';
                return $this->response;
            };
            $GoodComboId = $GoodComboId < 0 ? 0 : $GoodComboId; // Ensure unsigned integer
            ## TODO: VALIDATION INSTRUCTIONS FOR PARAMETERS -------------------
            try {
                $SQL_Query = 'INSERT INTO tblGoods VALUES (
                  :GoodId, 
                  :GoodCategoryId, 
                  :GoodSubCategoryId, 
                  :GoodBrandId, 
                  :GoodName, 
                  :GoodDescription, 
                  :GoodSalePrice, 
                  :GoodBarcode, 
                  :GoodComboId, 
                  :GoodStatus)';
                  
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':GoodId', $GoodId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':GoodCategoryId', $GoodCategoryId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':GoodSubCategoryId', $GoodSubCategoryId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':GoodBrandId', $GoodBrandId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':GoodName', $GoodName, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':GoodDescription', $GoodDescription, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':GoodSalePrice', $GoodSalePrice, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':GoodBarcode', $GoodBarcode, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':GoodComboId', $GoodComboId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':GoodStatus', $GoodStatus, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $GoodId = $this->DB_Connector->lastInsertId(); // Get newly created record ID
                    $this->response = [
                        'count' => 1, 
                        'data' => ['id' => $GoodId], // Return created ID
                        'msj' => '['.get_class($this).'] Ok: New record created successfully'
                    ];
                }
                else {
                    $this->response = [
                        'msj' => '['.get_class($this).'] Error: Cannot create new record'
                    ];
                };
            }
            catch (PDOException $ex) {
                $this->response['msj'] = '['.get_class($this).'] Error: SQL Exception';
                $this->response['error'] = $ex->getMessage();
            };
            return $this->response; // Return response
        }

        // ********************************************************************
        // (UPDATE) UPDATE RECORD ON DB ***************************************
        // ********************************************************************
        public function updateGood($GoodId, $GoodCategoryId, $GoodSubCategoryId, $GoodBrandId, $GoodName, $GoodDescription, $GoodSalePrice, $GoodBarcode, $GoodComboId) {
            $this->getGood($GoodId); // Get current record data from DB

            $GoodComboId = $GoodComboId < 0 ? 0 : $GoodComboId; // Ensure unsigned integer

            // Confirm changes on at least 1 field ----------------------------
            if ($this->GoodCategoryId == $GoodCategoryId    && $this->GoodSubCategoryId == $GoodSubCategoryId 
            && $this->GoodBrandId == $GoodBrandId           && $this->GoodName == $GoodName 
            && $this->GoodDescription == $GoodDescription   && $this->GoodSalePrice == $GoodSalePrice 
            && $this->GoodBarcode == $GoodBarcode           && $this->GoodComboId == $GoodComboId) {
                $this->response = [
                    'count' => -2,
                    'msj' => '['.get_class($this).'] Warning: No modifications made on record'
                ];
                return $this->response; // Return 'no modification' response
            };
            // ----------------------------------------------------------------

            try {
                $SQL_Query = 'UPDATE tblGoods SET 
                  GoodCategoryId = :GoodCategoryId, 
                  GoodSubCategoryId = :GoodSubCategoryId, 
                  GoodBrandId = :GoodBrandId, 
                  GoodName = :GoodName, 
                  GoodDescription = :GoodDescription, 
                  GoodSalePrice = :GoodSalePrice, 
                  GoodBarcode = :GoodBarcode, 
                  GoodComboId = :GoodComboId 
                  WHERE 
                  GoodId = :GoodId';
                  
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':GoodCategoryId', $GoodCategoryId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':GoodSubCategoryId', $GoodSubCategoryId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':GoodBrandId', $GoodBrandId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':GoodName', $GoodName, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':GoodDescription', $GoodDescription, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':GoodSalePrice', $GoodSalePrice, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':GoodBarcode', $GoodBarcode, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':GoodComboId', $GoodComboId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':GoodId', $GoodId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $this->response = [
                        'count' => 1,
                        'data' => ['id' => $GoodId],
                        'msj' => '['.get_class($this).'] Ok: Record updated successfully'
                    ];
                    $this->getGood($GoodId); // Update current object data with modified info
                }
                else {
                    $this->response = [
                        'count' => -1,
                        'msj' => '['.get_class($this).'] Error: Cannot update record'
                    ];
                };
            }
            catch (PDOException $ex) {
                $this->response = [
                    'count' => -1,
                    'msj' => '['.get_class($this).'] Error: SQL Exception',
                    'error' => $ex->getMessage()
                ];
            };
            return $this->response; // Return response Array
        }

        // ********************************************************************
        // (REACTIVATE) REACTIVATE RECORD ON DB *******************************
        // ********************************************************************
        public function reactivateGood($GoodId) {
            $this->getGood($GoodId); // Get current record data from DB
            $GoodStatus = 1; // Default active status (1)

            try {
                $SQL_Query = 'UPDATE tblGoods SET 
                    GoodStatus = :GoodStatus 
                    WHERE 
                    GoodId = :GoodId';

                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':GoodStatus', $GoodStatus, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':GoodId', $GoodId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $this->response = [
                        'msj' => '['.get_class($this).'] Ok: Record reactivated successfully'
                    ];
                    $this->getGood($GoodId); // Update current object data after reactivation
                }
                else {
                    $this->response = [
                        'msj' => '['.get_class($this).'] Error: Cannot reactivate record'
                    ];
                };
            }
            catch (PDOException $ex) {
                $this->response['msj'] = '['.get_class($this).'] Error: SQL Exception';
                $this->response['error'] = $ex->getMessage();
            };
            return $this->response; // Return response Array
        }

        // ********************************************************************
        // (DEACTIVATE) DEACTIVATE RECORD ON DB *******************************
        // ********************************************************************
        public function deactivateGood($GoodId) {
            $this->getGood($GoodId); // Get current record data from DB
            $GoodStatus = 0; // Default inactive status (0)

            try {
                $SQL_Query = 'UPDATE tblGoods SET 
                    GoodStatus = :GoodStatus 
                    WHERE 
                    GoodId = :GoodId';

                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':GoodStatus', $GoodStatus, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':GoodId', $GoodId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $this->response = [
                        'msj' => '['.get_class($this).'] Ok: Record deactivated successfully'
                    ];
                    $this->getGood($GoodId); // Update current object data after deactivation
                }
                else {
                    $this->response = [
                        'msj' => '['.get_class($this).'] Error: Cannot deactivate record'
                    ];
                };
            }
            catch (PDOException $ex) {
                $this->response['msj'] = '['.get_class($this).'] Error: SQL Exception';
                $this->response['error'] = $ex->getMessage();
            };
            return $this->response; // Return response Array
        }

// ****************************************************************************
// ******* AUXILIARY METHODS (NON-CRUD) ***************************************
// ****************************************************************************

        public function getStatuses ($queryString = NULL) {
            $this->DB_initProperties();
            $SQLCriteria = !empty($queryString) ? $this->buildSQLCriteria( $queryString, $this->SearchCriteriaFieldsString ) : NULL;
            // if (!$this->buildSQLCriteria($json_criteria))
            //     return $this->response; // Return criteria error

            try {
                // MANUAL STATIC RESPONSE *************************************
                $this->response['data'] = [
                    array(
                        'GoodStatusId' => 0,
                        'GoodStatusValue' => 'Inactivo'
                    ),
                    array(
                        'GoodStatusId' => 1,
                        'GoodStatusValue' => 'Activo'
                    )
                ]; // Data Array to be included in the response
                
                $this->response['count'] = count($this->response['data']); // Row count to be included in the response
                // MANUAL STATIC RESPONSE *************************************

                return $this->response; // Return response with records
            }
            catch (PDOException $ex) {
                $this->response['msj'] = '['.get_class($this).'] Error: SQL Exception';
                $this->response['error'] = $ex->getMessage();
                return $this->response; // Return response with error
            };
        }
    }
?>