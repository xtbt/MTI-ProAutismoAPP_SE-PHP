<?php
    require_once( './System/Database.php' );
    require_once( './Models/AppModelCore.php' );

    class Task extends AppModelCore {
        
        // Class properties
        public $TaskId;
        public $TaskType;
        public $TaskTitle;
        public $CreatedAt;
        public $TaskStatus;

        // Search criteria fields string
        private $SearchCriteriaFieldsString = 'CONCAT("[",COALESCE(TaskId,""),"]",COALESCE(TaskTitle,""))';

        // User contructor (DB Connection)
        public function __construct() {
            $this->DB_Connector = Database::getInstance()->getConnector(); // Get singleton DB connector
        }

        // Init DB properties -------------------------------------------------
        private function DB_initProperties() {
            $this->SQL_Tables = 'tblTasks';
            $this->SQL_Conditions = 'TRUE';
            $this->SQL_Order = 'TaskId';
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
                    TaskId AS TaskId, 
                    TaskType AS TaskType, 
                    TaskTitle AS TaskTitle, 
                    CreatedAt AS CreatedAt, 
                    TaskStatus AS TaskStatus 
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
                    $this->response['globalCount'] = 0; // Empty result
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
        public function getTask($TaskId) {
            $this->DB_initProperties();
            if (is_numeric($TaskId)) {
                $this->SQL_Conditions .= ' AND TaskId = :TaskId';
                $this->SQL_Limit = '0,1';
            }
            else {
                $this->response['msj'] = '['.get_class($this).'] Error: Invalid parameter';
                return $this->response;
            };
            
            try {
                $SQL_Query = 'SELECT 
                    TaskId AS TaskId, 
                    TaskType AS TaskType, 
                    TaskTitle AS TaskTitle, 
                    CreatedAt AS CreatedAt, 
                    TaskStatus AS TaskStatus 
                    FROM '
                    .$this->SQL_Tables.
                    ' WHERE '
                    .$this->SQL_Conditions.
                    (!is_null($this->SQL_Limit) ? ' LIMIT '.$this->SQL_Limit.';' : ';');
                
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':TaskId', $TaskId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                if ($this->SQL_Sentence->rowCount() < 1) {
                    $this->response['globalCount'] = 0; // No records found
                    $this->response['count'] = 0; // No records found
                    $this->response['msj'] = '['.get_class($this).'] No records found';
                    return $this->response; // Return response with no records
                };

                // If there is data, we build the response with DB info -------
                $this->response['data'][$TaskId] = $this->SQL_Sentence->fetch(PDO::FETCH_ASSOC);
                // Temporal implementation of the image *******************
                $this->response['data'][$TaskId]['TaskImage'] = ROOT_URL.'/assets/images/tasks/' . $TaskId . '.png';
                // Temporal implementation of the image *******************
                $this->updateProperties($this->response['data'][$TaskId]);
                $this->response['globalCount'] = 1; // Unique record
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
        public function createTask($TaskType, $TaskTitle) {
            $this->DB_initProperties();
            $TaskId = NULL; // NULL by default on new records
            $CreatedAt = date('Y-m-d H:i:s');
            $TaskStatus = 1; // 1(Active) by default on new records
            try {
                $SQL_Query = 'INSERT INTO tblTasks VALUES (
                    :TaskId, 
                    :TaskType, 
                    :TaskTitle, 
                    :CreatedAt, 
                    :TaskStatus)';
                  
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':TaskId', $TaskId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':TaskType', $TaskType, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':TaskTitle', $TaskTitle, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':CreatedAt', $CreatedAt, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':TaskStatus', $TaskStatus, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $TaskId = $this->DB_Connector->lastInsertId(); // Get newly created record ID
                    $this->response['globalCount'] = 1;
                    $this->response['count'] = 1;
                    $this->response['data'] = ['Id' => $TaskId];
                    $this->response['msj'] = '['.get_class($this).'] Ok: New record created successfully';
                }
                else {
                    $this->response['globalCount'] = 0;
                    $this->response['count'] = 0;
                    $this->response['msj'] = '['.get_class($this).'] Error: Cannot create new record';
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
        public function updateTask($TaskId, $TaskType, $TaskTitle) {
            $this->getTask($TaskId); // Get current record data from DB

            // Confirm changes on at least 1 field ----------------------------
            if ($this->TaskType == $TaskType && $this->TaskTitle == $TaskTitle) {
                $this->response['count'] = -2;
                $this->response['globalCount'] = -2;
                $this->response['data'] = ['Id' => $TaskId];
                $this->response['msj'] = '['.get_class($this).'] Warning: No modifications made on record';
                return $this->response; // Return 'no modification' response
            };
            // ----------------------------------------------------------------

            try {
                $SQL_Query = 'UPDATE tblTasks SET 
                    TaskType = :TaskType, 
                    TaskTitle = :TaskTitle 
                    WHERE 
                    TaskId = :TaskId';
                  
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':TaskType', $TaskType, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':TaskTitle', $TaskTitle, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':TaskId', $TaskId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $this->getTask( $TaskId ); // Update current object data with modified info
                    $this->response['msj'] = '['.get_class($this).'] Ok: Record updated successfully';
                }
                else {
                    $this->response['msj'] = '['.get_class($this).'] Error: Cannot update record';
                };
            }
            catch (PDOException $ex) {
                $this->response['msj'] = '['.get_class($this).'] Error: SQL Exception';
                $this->response['error'] = $ex->getMessage();
            };
            $this->response['data'] = ['Id' => $TaskId];
            return $this->response; // Return response Array
        }

        // ********************************************************************
        // (REACTIVATE) REACTIVATE RECORD ON DB *******************************
        // ********************************************************************
        public function reactivateTask($TaskId) {
            $this->getTask($TaskId); // Get current record data from DB
            $TaskStatus = 1; // Default active status (1)

            try {
                $SQL_Query = 'UPDATE tblTasks SET 
                    TaskStatus = :TaskStatus 
                    WHERE 
                    TaskId = :TaskId';

                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':TaskStatus', $TaskStatus, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':TaskId', $TaskId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $this->getTask($TaskId); // Update current object data after reactivation
                    $this->response['msj'] = '['.get_class($this).'] Ok: Record reactivated successfully';
                }
                else {
                    $this->response['msj'] = '['.get_class($this).'] Error: Cannot reactivate record';
                };
            }
            catch (PDOException $ex) {
                $this->response['msj'] = '['.get_class($this).'] Error: SQL Exception';
                $this->response['error'] = $ex->getMessage();
            };
            $this->response['data'] = ['Id' => $TaskId];
            return $this->response; // Return response Array
        }

        // ********************************************************************
        // (DEACTIVATE) DEACTIVATE RECORD ON DB *******************************
        // ********************************************************************
        public function deactivateTask($TaskId) {
            $this->getTask($TaskId); // Get current record data from DB
            $TaskStatus = 0; // Default inactive status (0)

            try {
                $SQL_Query = 'UPDATE tblTasks SET 
                    TaskStatus = :TaskStatus 
                    WHERE 
                    TaskId = :TaskId';

                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':TaskStatus', $TaskStatus, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':TaskId', $TaskId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $this->getTask($TaskId); // Update current object data after deactivation
                    $this->response['msj'] = '['.get_class($this).'] Ok: Record deactivated successfully';
                }
                else {
                    $this->response['msj'] = '['.get_class($this).'] Error: Cannot deactivate record';
                };
            }
            catch (PDOException $ex) {
                $this->response['msj'] = '['.get_class($this).'] Error: SQL Exception';
                $this->response['error'] = $ex->getMessage();
            };
            $this->response['data'] = ['Id' => $TaskId];
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
                        'TaskStatusId' => 0,
                        'TaskStatusValue' => 'Inactivo'
                    ),
                    array(
                        'TaskStatusId' => 1,
                        'TaskStatusValue' => 'Activo'
                    )
                ]; // Data Array to be included in the response
                
                $this->response['count'] = count($this->response['data']); // Row count to be included in the response
                $this->response['globalCount'] = $this->response['count'];
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