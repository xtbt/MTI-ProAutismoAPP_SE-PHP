<?php
    require_once( './System/Database.php' );
    require_once( './Models/AppModelCore.php' );

    class Activity extends AppModelCore {
        
        // Class properties
        public $ActivityId;
        public $UserProfileId;
        public $TaskId;
        public $TaskTitle;                  // tblTasks::TaskTitle
        public $ActivityDateTime;
        public $ActivityStart;
        public $ActivityEnd;
        public $ActivityResults;
        public $CreatedAt;
        public $ActivityStatus;

        // Search criteria fields string
        private $SearchCriteriaFieldsString = 'CONCAT(COALESCE(TaskTitle,""),"|",COALESCE(ActivityResults,""))';

        // User contructor (DB Connection)
        public function __construct() {
            $this->DB_Connector = Database::getInstance()->getConnector(); // Get singleton DB connector
        }

        // Init DB properties -------------------------------------------------
        private function DB_initProperties() {
            $this->SQL_Tables = 'tblActivities AS t1 LEFT JOIN 
                                tblTasks AS t2 USING(TaskId)';
            $this->SQL_Conditions = 'TRUE';
            $this->SQL_Order = 'ActivityId';
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
                    t1.ActivityId AS ActivityId, 
                    t1.UserProfileId AS UserProfileId, 
                    t1.TaskId AS TaskId, 
                    t2.TaskTitle AS TaskTitle, 
                    t1.ActivityDateTime AS ActivityDateTime, 
                    t1.ActivityStart AS ActivityStart, 
                    t1.ActivityEnd AS ActivityEnd, 
                    t1.ActivityResults AS ActivityResults, 
                    t1.CreatedAt AS CreatedAt, 
                    t1.ActivityStatus AS ActivityStatus 
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
        public function getActivity($ActivityId) {
            $this->DB_initProperties();
            if (is_numeric($ActivityId)) {
                $this->SQL_Conditions .= ' AND ActivityId = :ActivityId';
                $this->SQL_Limit = '0,1';
            }
            else {
                $this->response['msj'] = '['.get_class($this).'] Error: Invalid parameter';
                return $this->response;
            };
            
            try {
                $SQL_Query = 'SELECT 
                    t1.ActivityId AS ActivityId, 
                    t1.UserProfileId AS UserProfileId, 
                    t1.TaskId AS TaskId, 
                    t2.TaskTitle AS TaskTitle, 
                    t1.ActivityDateTime AS ActivityDateTime, 
                    t1.ActivityStart AS ActivityStart, 
                    t1.ActivityEnd AS ActivityEnd, 
                    t1.ActivityResults AS ActivityResults, 
                    t1.CreatedAt AS CreatedAt, 
                    t1.ActivityStatus AS ActivityStatus 
                    FROM '
                    .$this->SQL_Tables.
                    ' WHERE '
                    .$this->SQL_Conditions.
                    (!is_null($this->SQL_Limit) ? ' LIMIT '.$this->SQL_Limit.';' : ';');
                
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':ActivityId', $ActivityId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                if ($this->SQL_Sentence->rowCount() < 1) {
                    $this->response['globalCount'] = 0; // Empty result
                    $this->response['count'] = 0; // No records found
                    $this->response['msj'] = '['.get_class($this).'] No records found';
                    return $this->response; // Return response with no records
                };

                // If there is data, we build the response with DB info -------
                $this->response['data'][$ActivityId] = $this->SQL_Sentence->fetch(PDO::FETCH_ASSOC);
                $this->updateProperties($this->response['data'][$ActivityId]);
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
        public function createActivity($UserProfileId, $TaskId, $ActivityDateTime) {
            $this->DB_initProperties();
            $ActivityId = NULL; // NULL by default on new records
            $CreatedAt = date('Y-m-d H:i:s');
            $ActivityStatus = 1; // 1(Active) by default on new records

            ## TODO: VALIDATION INSTRUCTIONS FOR PARAMETERS -------------------
            ## ... 
            // Meanwhile ......
            if (empty($UserProfileId) || empty($TaskId) || empty($ActivityDateTime)) {
                $this->response['error'] = '['.get_class($this).'] Error: Main fields cannot be empty';
                return $this->response;
            };
            ## TODO: VALIDATION INSTRUCTIONS FOR PARAMETERS -------------------
            try {
                $SQL_Query = 'INSERT INTO tblActivities VALUES (
                  :ActivityId, 
                  :UserProfileId, 
                  :TaskId, 
                  :ActivityDateTime, 
                  :ActivityStart, 
                  :ActivityEnd, 
                  :ActivitySalePriceREM, 
                  :ActivityResults, 
                  :CreatedAt, 
                  :ActivityStatus)';
                  
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':ActivityId', $ActivityId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':UserProfileId', $UserProfileId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':TaskId', $TaskId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':ActivityDateTime', $ActivityDateTime, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':ActivityStart', $ActivityStart, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':ActivityEnd', $ActivityEnd, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':ActivitySalePriceREM', $ActivitySalePriceREM, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':ActivityResults', $ActivityResults, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':CreatedAt', $CreatedAt, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':ActivityStatus', $ActivityStatus, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $ActivityId = $this->DB_Connector->lastInsertId(); // Get newly created record ID
                    $this->response = [
                        'count' => 1, 
                        'data' => ['id' => $ActivityId], // Return created ID
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
        public function updateActivity($ActivityId, $UserProfileId, $ActivitySubCategoryIdREM, $ActivityBrandIdREM, $ActivityStart, $ActivityEnd, $ActivitySalePriceREM, $ActivityResults, $CreatedAt) {
            $this->getActivity($ActivityId); // Get current record data from DB

            $CreatedAt = $CreatedAt < 0 ? 0 : $CreatedAt; // Ensure unsigned integer

            // Confirm changes on at least 1 field ----------------------------
            if ($this->UserProfileId == $UserProfileId    && $this->ActivitySubCategoryIdREM == $ActivitySubCategoryIdREM 
            && $this->ActivityBrandIdREM == $ActivityBrandIdREM           && $this->ActivityStart == $ActivityStart 
            && $this->ActivityEnd == $ActivityEnd   && $this->ActivitySalePriceREM == $ActivitySalePriceREM 
            && $this->ActivityResults == $ActivityResults           && $this->CreatedAt == $CreatedAt) {
                $this->response = [
                    'count' => -2,
                    'msj' => '['.get_class($this).'] Warning: No modifications made on record'
                ];
                return $this->response; // Return 'no modification' response
            };
            // ----------------------------------------------------------------

            try {
                $SQL_Query = 'UPDATE tblActivities SET 
                  UserProfileId = :UserProfileId, 
                  ActivitySubCategoryIdREM = :ActivitySubCategoryIdREM, 
                  ActivityBrandIdREM = :ActivityBrandIdREM, 
                  ActivityStart = :ActivityStart, 
                  ActivityEnd = :ActivityEnd, 
                  ActivitySalePriceREM = :ActivitySalePriceREM, 
                  ActivityResults = :ActivityResults, 
                  CreatedAt = :CreatedAt 
                  WHERE 
                  ActivityId = :ActivityId';
                  
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':UserProfileId', $UserProfileId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':ActivitySubCategoryIdREM', $ActivitySubCategoryIdREM, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':ActivityBrandIdREM', $ActivityBrandIdREM, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':ActivityStart', $ActivityStart, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':ActivityEnd', $ActivityEnd, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':ActivitySalePriceREM', $ActivitySalePriceREM, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':ActivityResults', $ActivityResults, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':CreatedAt', $CreatedAt, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':ActivityId', $ActivityId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $this->response = [
                        'count' => 1,
                        'data' => ['id' => $ActivityId],
                        'msj' => '['.get_class($this).'] Ok: Record updated successfully'
                    ];
                    $this->getActivity($ActivityId); // Update current object data with modified info
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
        public function reactivateActivity($ActivityId) {
            $this->getActivity($ActivityId); // Get current record data from DB
            $ActivityStatus = 1; // Default active status (1)

            try {
                $SQL_Query = 'UPDATE tblActivities SET 
                    ActivityStatus = :ActivityStatus 
                    WHERE 
                    ActivityId = :ActivityId';

                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':ActivityStatus', $ActivityStatus, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':ActivityId', $ActivityId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $this->response = [
                        'msj' => '['.get_class($this).'] Ok: Record reactivated successfully'
                    ];
                    $this->getActivity($ActivityId); // Update current object data after reactivation
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
        public function deactivateActivity($ActivityId) {
            $this->getActivity($ActivityId); // Get current record data from DB
            $ActivityStatus = 0; // Default inactive status (0)

            try {
                $SQL_Query = 'UPDATE tblActivities SET 
                    ActivityStatus = :ActivityStatus 
                    WHERE 
                    ActivityId = :ActivityId';

                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':ActivityStatus', $ActivityStatus, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':ActivityId', $ActivityId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                
                if ($this->SQL_Sentence->rowCount() != 0) {
                    $this->response = [
                        'msj' => '['.get_class($this).'] Ok: Record deactivated successfully'
                    ];
                    $this->getActivity($ActivityId); // Update current object data after deactivation
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
                        'ActivityStatusId' => 0,
                        'ActivityStatusValue' => 'Inactivo'
                    ),
                    array(
                        'ActivityStatusId' => 1,
                        'ActivityStatusValue' => 'Activo'
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