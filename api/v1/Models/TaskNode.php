<?php
    require_once( './System/Database.php' );
    require_once( './Models/AppModelCore.php' );

    class TaskNode extends AppModelCore {
        
        // Class properties
        public $TaskNodeId;
        public $TaskNodeType;
        public $TaskNodeTitle;
        public $CreatedAt;
        public $TaskNodeStatus;

        // Search criteria fields string
        private $SearchCriteriaFieldsString = 'CONCAT("[",COALESCE(TaskNodeId,""),"]",COALESCE(TaskNodeTitle,""))';

        // User contructor (DB Connection)
        public function __construct() {
            $this->DB_Connector = Database::getInstance()->getConnector(); // Get singleton DB connector
        }

        // Init DB properties -------------------------------------------------
        private function DB_initProperties() {
            $this->SQL_Tables = 'tblTaskNodes';
            $this->SQL_Conditions = 'TRUE';
            $this->SQL_Order = 'TaskNodeId';
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
                    TaskNodeId AS TaskNodeId, 
                    TaskNodeType AS TaskNodeType, 
                    TaskNodeTitle AS TaskNodeTitle, 
                    CreatedAt AS CreatedAt, 
                    TaskNodeStatus AS TaskNodeStatus 
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
        public function getTaskNode($TaskNodeId) {
            $this->DB_initProperties();
            if (is_numeric($TaskNodeId)) {
                $this->SQL_Conditions .= ' AND TaskNodeId = :TaskNodeId';
                $this->SQL_Limit = '0,1';
            }
            else {
                $this->response['msj'] = '['.get_class($this).'] Error: Invalid parameter';
                return $this->response;
            };
            
            try {
                $SQL_Query = 'SELECT 
                    TaskNodeId AS TaskNodeId, 
                    TaskNodeType AS TaskNodeType, 
                    TaskNodeTitle AS TaskNodeTitle, 
                    CreatedAt AS CreatedAt, 
                    TaskNodeStatus AS TaskNodeStatus 
                    FROM '
                    .$this->SQL_Tables.
                    ' WHERE '
                    .$this->SQL_Conditions.
                    (!is_null($this->SQL_Limit) ? ' LIMIT '.$this->SQL_Limit.';' : ';');
                
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':TaskNodeId', $TaskNodeId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                if ($this->SQL_Sentence->rowCount() < 1) {
                    $this->response['globalCount'] = 0; // No records found
                    $this->response['count'] = 0; // No records found
                    $this->response['msj'] = '['.get_class($this).'] No records found';
                    return $this->response; // Return response with no records
                };

                // If there is data, we build the response with DB info -------
                $this->response['data'][$TaskNodeId] = $this->SQL_Sentence->fetch(PDO::FETCH_ASSOC);
                $this->updateProperties($this->response['data'][$TaskNodeId]);
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
        public function createTaskNode($TaskNodeType, $TaskNodeTitle) {
            $this->DB_initProperties();
            $TaskNodeId = NULL; // NULL by default on new records
            $CreatedAt = date('Y-m-d H:i:s');
            $TaskNodeStatus = 1; // 1(Active) by default on new records
            try {
                $SQL_Query = 'INSERT INTO tblTaskNodes VALUES (
                    :TaskNodeId, 
                    :TaskNodeType, 
                    :TaskNodeTitle, 
                    :CreatedAt, 
                    :TaskNodeStatus)';
                  
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':TaskNodeId', $TaskNodeId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':TaskNodeType', $TaskNodeType, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':TaskNodeTitle', $TaskNodeTitle, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':CreatedAt', $CreatedAt, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':TaskNodeStatus', $TaskNodeStatus, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $TaskNodeId = $this->DB_Connector->lastInsertId(); // Get newly created record ID
                    $this->response['globalCount'] = 1;
                    $this->response['count'] = 1;
                    $this->response['data'] = ['Id' => $TaskNodeId];
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
        public function updateTaskNode($TaskNodeId, $TaskNodeType, $TaskNodeTitle) {
            $this->getTaskNode($TaskNodeId); // Get current record data from DB

            // Confirm changes on at least 1 field ----------------------------
            if ($this->TaskNodeType == $TaskNodeType && $this->TaskNodeTitle == $TaskNodeTitle) {
                $this->response['count'] = -2;
                $this->response['globalCount'] = -2;
                $this->response['data'] = ['Id' => $TaskNodeId];
                $this->response['msj'] = '['.get_class($this).'] Warning: No modifications made on record';
                return $this->response; // Return 'no modification' response
            };
            // ----------------------------------------------------------------

            try {
                $SQL_Query = 'UPDATE tblTaskNodes SET 
                    TaskNodeType = :TaskNodeType, 
                    TaskNodeTitle = :TaskNodeTitle 
                    WHERE 
                    TaskNodeId = :TaskNodeId';
                  
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':TaskNodeType', $TaskNodeType, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':TaskNodeTitle', $TaskNodeTitle, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':TaskNodeId', $TaskNodeId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $this->getTaskNode( $TaskNodeId ); // Update current object data with modified info
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
            $this->response['data'] = ['Id' => $TaskNodeId];
            return $this->response; // Return response Array
        }

        // ********************************************************************
        // (REACTIVATE) REACTIVATE RECORD ON DB *******************************
        // ********************************************************************
        public function reactivateTaskNode($TaskNodeId) {
            $this->getTaskNode($TaskNodeId); // Get current record data from DB
            $TaskNodeStatus = 1; // Default active status (1)

            try {
                $SQL_Query = 'UPDATE tblTaskNodes SET 
                    TaskNodeStatus = :TaskNodeStatus 
                    WHERE 
                    TaskNodeId = :TaskNodeId';

                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':TaskNodeStatus', $TaskNodeStatus, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':TaskNodeId', $TaskNodeId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $this->getTaskNode($TaskNodeId); // Update current object data after reactivation
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
            $this->response['data'] = ['Id' => $TaskNodeId];
            return $this->response; // Return response Array
        }

        // ********************************************************************
        // (DEACTIVATE) DEACTIVATE RECORD ON DB *******************************
        // ********************************************************************
        public function deactivateTaskNode($TaskNodeId) {
            $this->getTaskNode($TaskNodeId); // Get current record data from DB
            $TaskNodeStatus = 0; // Default inactive status (0)

            try {
                $SQL_Query = 'UPDATE tblTaskNodes SET 
                    TaskNodeStatus = :TaskNodeStatus 
                    WHERE 
                    TaskNodeId = :TaskNodeId';

                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':TaskNodeStatus', $TaskNodeStatus, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':TaskNodeId', $TaskNodeId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $this->getTaskNode($TaskNodeId); // Update current object data after deactivation
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
            $this->response['data'] = ['Id' => $TaskNodeId];
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
                        'TaskNodeStatusId' => 0,
                        'TaskNodeStatusValue' => 'Inactivo'
                    ),
                    array(
                        'TaskNodeStatusId' => 1,
                        'TaskNodeStatusValue' => 'Activo'
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