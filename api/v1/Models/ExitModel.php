<?php
    require_once( './System/Database.php' );

    class ExitModel {
        // DataBase properties
        private $DB_Connector = NULL;
        private $SQL_Tables = 'tblExits AS t1 LEFT JOIN 
                            tblExits_Inputs_Goods AS t2 USING(ExitId) LEFT JOIN 
                            tblInputs_Goods AS t3 USING(Input_GoodId) LEFT JOIN 
                            tblGoods AS t4 USING(GoodId)';
        private $SQL_Conditions = 'TRUE';
        private $SQL_Order = 't1.ExitId';
        private $SQL_Limit = NULL;
        private $SQL_Params = [];
        private $SQL_Sentence = NULL;

        // Class properties
        public $ExitId;
        public $ExitDate;
        public $UserId;
        public $CustomerId;
        public $Input_GoodId;           // tblExits_Inputs_Goods::Input_GoodId
        public $Quantity;               // tblExits_Inputs_Goods::Quantity
        public $SalePrice;              // tblExits_Inputs_Goods::SalePrice
        public $ExitStatus;

        // Response Array *****************************************************
        private $response = [
            'count' => -1, 
            'data' => NULL, 
            'msj' => NULL,
            'error' => NULL
        ]; // Always return an Array, even on ERROR ***************************

        // Customer contructor (DB Connection)
        public function __construct() {
            $this->DB_Connector = Database::getInstance()->getConnector(); // Get singleton DB connector
        }

        // Init DB properties -------------------------------------------------
        private function DB_initProperties() {
            $this->SQL_Tables = 'tblExits AS t1 LEFT JOIN 
                                tblExits_Inputs_Goods AS t2 USING(ExitId) LEFT JOIN 
                                tblInputs_Goods AS t3 USING(Input_GoodId) LEFT JOIN 
                                tblGoods AS t4 USING(GoodId)';
            $this->SQL_Conditions = 'TRUE';
            $this->SQL_Order = 't1.ExitId';
            $this->SQL_Limit = NULL;
            $this->SQL_Params = [];
            $this->SQL_Sentence = NULL;
            
            $this->response['count'] = -1;
            $this->response['data'] = NULL;
        }

        // SPECIAL FUNCTION: Validate criteria ********************************
        private function JSON_isValidCriteria($json_criteria) {
            if (NULL != $json_criteria) {
                $array_criteria = json_decode($json_criteria, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->response['msj'] = '['.get_class($this).'] JSON decodification error';
                    return false; // Return FALSE on bad criteria JSON structure
                };
                
                if (count($array_criteria) < 1) { // If there isn't at least 1 index on the criteria
                    $this->response['msj'] = '['.get_class($this).'] Criteria definition error';
                    return false; // Return FALSE on bad criteria definition
                };

                if (isset($array_criteria['conditions'])) {
                    foreach ($array_criteria['conditions'] AS $identifier => $condition) {
                        $this->SQL_Params[':'.$identifier] = $condition['value'];
                        $this->SQL_Conditions .= ' '.$condition['type'].(isset($condition['begingroup']) ? ' (' : ' ').$condition['field'].' '.$condition['operator'].' :'.$identifier.(isset($condition['finishgroup']) ? ')' : '');
                    };
                };

                if (isset($array_criteria['order'])) {
                    $this->SQL_Order = $array_criteria['order'];
                };

                if (isset($array_criteria['limit'])) {
                    $this->SQL_Limit = $array_criteria['limit'];
                };
            };
            return true; // If no criteria defined or correctly defined, return TRUE
        }

        // SPECIAL FUNCTION: Load necesary parameters depending on criteria ***
        private function DB_loadParameters() {
            if (count($this->SQL_Params) > 0) {
                foreach ($this->SQL_Params AS $identifier => &$value) {
                    $this->SQL_Sentence->bindParam($identifier, $value, PDO::PARAM_STR);
                };
            };
        }

        // SPECIAL FUNCTION: Load response to be returned to the controller ***
        private function DB_loadResponse() {
            $this->response['data'] = []; // Data Array to be included in the response
                while ($row_array = $this->SQL_Sentence->fetch(PDO::FETCH_ASSOC)) {
                    $this->response['data'][] = $row_array;
                };
            $this->response['count'] = count($this->response['data']); // Row count to be included in the response
        }

        // Function that gets all rows in the Database
        // If criteria was defined, it filters the result
        public function getAll($json_criteria = NULL) {
            $this->DB_initProperties();
            if (!$this->JSON_isValidCriteria($json_criteria))
                return $this->response; // Return criteria error
            
            try {
                $SQL_Query = 'SELECT 
                    t1.ExitId, 
                    t1.ExitDate, 
                    t1.UserId, 
                    t1.CustomerId, 
                    t1.ExitStatus 
                    FROM '
                    .$this->SQL_Tables.
                    ' WHERE '
                    .$this->SQL_Conditions.
                    ' ORDER BY '
                    .$this->SQL_Order.
                    (!is_null($this->SQL_Limit) ? ' LIMIT '.$this->SQL_Limit.';' : ';');
                
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->DB_loadParameters();
                $this->SQL_Sentence->execute();
                if ($this->SQL_Sentence->rowCount() < 1) {
                    $this->response['count'] = 0; // Empty result
                    $this->response['msj'] = '['.get_class($this).'] No records found';
                    return $this->response; // Returns response with no records
                };

                $this->DB_loadResponse(); // If records found, build response Array with DB info
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
        public function getCustomer($CustomerId) {
            $this->DB_initProperties();
            if (is_numeric($CustomerId)) {
                $this->SQL_Conditions .= ' AND CustomerId = :CustomerId';
                $this->SQL_Limit = '0,1';
            }
            else {
                $this->response['msj'] = '['.get_class($this).'] Error: Invalid parameter';
                return $this->response;
            };
            
            try {
                $SQL_Query = 'SELECT 
                        CustomerId, 
                        Email, 
                        PhoneNumber, 
                        FirstName, 
                        LastName, 
                        CustomerStatus 
                        FROM '
                        .$this->SQL_Tables.
                        ' WHERE '
                        .$this->SQL_Conditions.
                        (!is_null($this->SQL_Limit) ? ' LIMIT '.$this->SQL_Limit.';' : ';');
                
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':CustomerId', $CustomerId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                if ($this->SQL_Sentence->rowCount() < 1) {
                    $this->response['count'] = 0; // No records found
                    $this->response['msj'] = '['.get_class($this).'] No records found';
                    return $this->response; // Return response with no records
                };

                // If there is data, we build the response with DB info -------
                $this->response['data'][$CustomerId] = $this->SQL_Sentence->fetch(PDO::FETCH_ASSOC);
                $this->updateProperties($this->response['data'][$CustomerId]);
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
        public function createCustomer($Email, $PhoneNumber, $FirstName, $LastName) {
            $this->DB_initProperties();
            $CustomerId = NULL; // NULL by default on new records
            ## TODO: VALIDATION INSTRUCTIONS FOR PARAMETERS -------------------
            ## ... 
            // Meanwhile ......
            if (empty($Email) || empty($FirstName) || empty($LastName)) {
                $this->response['error'] = '['.get_class($this).'] Error: Main fields cannot be empty';
                return $this->response;
            };
            ## TODO: VALIDATION INSTRUCTIONS FOR PARAMETERS -------------------
            $CustomerStatus = 1; // 1(Active) by default on new records
            try {
                $SQL_Query = 'INSERT INTO tblCustomers VALUES (
                  :CustomerId, 
                  :Email, 
                  :PhoneNumber, 
                  :FirstName, 
                  :LastName, 
                  :CustomerStatus)';
                  
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':CustomerId', $CustomerId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':Email', $Email, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':PhoneNumber', $PhoneNumber, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':FirstName', $FirstName, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':LastName', $LastName, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':CustomerStatus', $CustomerStatus, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $CustomerId = $this->DB_Connector->lastInsertId(); // Get newly created record ID
                    $this->response = [
                        'count' => 1, 
                        'data' => ['id' => $CustomerId], // Return created ID
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
        public function updateCustomer($CustomerId, $Email, $PhoneNumber, $FirstName, $LastName) {
            $this->getCustomer($CustomerId); // Get current record data from DB

            // Confirm changes on at least 1 field ----------------------------
            if ($this->Email == $Email && $this->PhoneNumber == $PhoneNumber 
            && $this->FirstName == $FirstName && $this->LastName == $LastName) {
                $this->response['msj'] = '['.get_class($this).'] Warning: No modifications made on record';
                return $this->response; // Return 'no modification' response
            };
            // ----------------------------------------------------------------

            try {
                $SQL_Query = 'UPDATE tblCustomers SET 
                  Email = :Email, 
                  PhoneNumber = :PhoneNumber, 
                  FirstName = :FirstName, 
                  LastName = :LastName 
                  WHERE 
                  CustomerId = :CustomerId';
                  
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':Email', $Email, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':PhoneNumber', $PhoneNumber, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':FirstName', $FirstName, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':LastName', $LastName, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':CustomerId', $CustomerId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $this->response = [
                        'msj' => '['.get_class($this).'] Ok: Record updated successfully'
                    ];
                    $this->getCustomer($CustomerId); // Update current object data with modified info
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
        public function reactivateCustomer($CustomerId) {
            $this->getCustomer($CustomerId); // Get current record data from DB
            $CustomerStatus = 1; // Default active status (1)

            try {
                $SQL_Query = 'UPDATE tblCustomers SET 
                    CustomerStatus = :CustomerStatus 
                    WHERE 
                    CustomerId = :CustomerId';

                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':CustomerStatus', $CustomerStatus, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':CustomerId', $CustomerId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $this->response = [
                        'msj' => '['.get_class($this).'] Ok: Record reactivated successfully'
                    ];
                    $this->getCustomer($CustomerId); // Update current object data after reactivation
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
        public function deactivateCustomer($CustomerId) {
            $this->getCustomer($CustomerId); // Get current record data from DB
            $CustomerStatus = 0; // Default inactive status (0)

            try {
                $SQL_Query = 'UPDATE tblCustomers SET 
                    CustomerStatus = :CustomerStatus 
                    WHERE 
                    CustomerId = :CustomerId';

                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':CustomerStatus', $CustomerStatus, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':CustomerId', $CustomerId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $this->response = [
                        'msj' => '['.get_class($this).'] Ok: Record deactivated successfully'
                    ];
                    $this->getCustomer($CustomerId); // Update current object data after deactivation
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
    }
?>