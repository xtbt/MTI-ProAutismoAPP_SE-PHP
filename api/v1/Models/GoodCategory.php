<?php
    require_once( './System/Database.php' );
    require_once( './Models/AppModelCore.php' );

    class GoodCategory extends AppModelCore {
        
        // Class properties
        public $GoodCategoryId;
        public $GoodCategoryName;
        public $GoodCategoryStatus;

        // Search criteria fields string
        private $SearchCriteriaFieldsString = 'CONCAT("[",COALESCE(GoodCategoryId,""),"]",COALESCE(GoodCategoryName,""))';
        
        // User contructor (DB Connection)
        public function __construct() {
            $this->DB_Connector = Database::getInstance()->getConnector(); // Get singleton DB connector
        }

        // Init DB properties -------------------------------------------------
        private function DB_initProperties() {
            $this->SQL_Tables = 'tblGoodsCategories';
            $this->SQL_Conditions = 'TRUE';
            $this->SQL_Order = 'GoodCategoryId';
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
                    GoodCategoryId, 
                    GoodCategoryName, 
                    GoodCategoryStatus 
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
        public function getGoodCategory($GoodCategoryId) {
            $this->DB_initProperties();
            if (is_numeric($GoodCategoryId)) {
                $this->SQL_Conditions .= ' AND GoodCategoryId = :GoodCategoryId';
                $this->SQL_Limit = '0,1';
            }
            else {
                $this->response['msj'] = '['.get_class($this).'] Error: Invalid parameter';
                return $this->response;
            };
            
            try {
                $SQL_Query = 'SELECT 
                    GoodCategoryId, 
                    GoodCategoryName, 
                    GoodCategoryStatus 
                    FROM '
                    .$this->SQL_Tables.
                    ' WHERE '
                    .$this->SQL_Conditions.
                    (!is_null($this->SQL_Limit) ? ' LIMIT '.$this->SQL_Limit.';' : ';');
                
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':GoodCategoryId', $GoodCategoryId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                if ($this->SQL_Sentence->rowCount() < 1) {
                    $this->response['count'] = 0; // No records found
                    $this->response['msj'] = '['.get_class($this).'] No records found';
                    return $this->response; // Return response with no records
                };

                // If there is data, we build the response with DB info -------
                $this->response['data'][$GoodCategoryId] = $this->SQL_Sentence->fetch(PDO::FETCH_ASSOC);
                $this->updateProperties($this->response['data'][$GoodCategoryId]);
                $this->response['count'] = 1; // Unique record
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
        public function createGoodCategory($GoodCategoryName) {
            $this->DB_initProperties();
            $GoodCategoryId = NULL; // NULL by default on new records
            $GoodCategoryStatus = 1; // 1(Active) by default on new records
            try {
                $SQL_Query = 'INSERT INTO tblGoodsCategories VALUES (
                  :GoodCategoryId, 
                  :GoodCategoryName, 
                  :GoodCategoryStatus)';
                  
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':GoodCategoryId', $GoodCategoryId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':GoodCategoryName', $GoodCategoryName, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':GoodCategoryStatus', $GoodCategoryStatus, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $GoodCategoryId = $this->DB_Connector->lastInsertId(); // Get newly created record ID
                    $this->response = [
                        'count' => 1, 
                        'data' => ['id' => $GoodCategoryId], // Return created ID
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
        public function updateGoodCategory($GoodCategoryId, $GoodCategoryName) {
            $this->getGoodCategory($GoodCategoryId); // Get current record data from DB

            // Confirm changes on at least 1 field ----------------------------
            if ($this->GoodCategoryName == $GoodCategoryName) {
                $this->response = [
                    'msj' => '['.get_class($this).'] Warning: No modifications made on record'
                ];
                return $this->response; // Return 'no modification' response
            };
            // ----------------------------------------------------------------

            try {
                $SQL_Query = 'UPDATE tblGoodsCategories SET 
                  GoodCategoryName = :GoodCategoryName 
                  WHERE 
                  GoodCategoryId = :GoodCategoryId';
                  
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':GoodCategoryName', $GoodCategoryName, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':GoodCategoryId', $GoodCategoryId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $this->response = [
                        'msj' => '['.get_class($this).'] Ok: Record updated successfully'
                    ];
                    $this->getGoodCategory($GoodCategoryId); // Update current object data with modified info
                }
                else {
                    $this->response = [
                        'msj' => '['.get_class($this).'] Error: Cannot update record'
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
        // (REACTIVATE) REACTIVATE RECORD ON DB *******************************
        // ********************************************************************
        public function reactivateGoodCategory($GoodCategoryId) {
            $this->getGoodCategory($GoodCategoryId); // Get current record data from DB
            $GoodCategoryStatus = 1; // Default active status (1)

            try {
                $SQL_Query = 'UPDATE tblGoodsCategories SET 
                    GoodCategoryStatus = :GoodCategoryStatus 
                    WHERE 
                    GoodCategoryId = :GoodCategoryId';

                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':GoodCategoryStatus', $GoodCategoryStatus, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':GoodCategoryId', $GoodCategoryId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $this->response = [
                        'msj' => '['.get_class($this).'] Ok: Record reactivated successfully'
                    ];
                    $this->getGoodCategory($GoodCategoryId); // Update current object data after reactivation
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
        public function deactivateGoodCategory($GoodCategoryId) {
            $this->getGoodCategory($GoodCategoryId); // Get current record data from DB
            $GoodCategoryStatus = 0; // Default inactive status (0)

            try {
                $SQL_Query = 'UPDATE tblGoodsCategories SET 
                    GoodCategoryStatus = :GoodCategoryStatus 
                    WHERE 
                    GoodCategoryId = :GoodCategoryId';

                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':GoodCategoryStatus', $GoodCategoryStatus, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':GoodCategoryId', $GoodCategoryId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $this->response = [
                        'msj' => '['.get_class($this).'] Ok: Record deactivated successfully'
                    ];
                    $this->getGoodCategory($GoodCategoryId); // Update current object data after deactivation
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
                        'GoodCategoryStatusId' => 0,
                        'GoodCategoryStatusValue' => 'Inactivo'
                    ),
                    array(
                        'GoodCategoryStatusId' => 1,
                        'GoodCategoryStatusValue' => 'Activo'
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