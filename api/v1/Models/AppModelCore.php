<?php
    class AppModelCore {
        // DataBase properties
        protected $DB_Connector;
        protected $SQL_Tables;
        protected $SQL_Conditions;
        protected $SQL_Order;
        protected $SQL_Limit;
        protected $SQL_Params;
        protected $SQL_Sentence;

        // Response Array *****************************************************
        protected $response = [
            'globalCount'   => -1, 
            'count'         => -1, 
            'data'          => NULL, 
            'msj'           => NULL
        ]; // Always return an Array, even on ERROR ***************************

        /**
         * Return ecoded JSON object
         * @return object
         */
        protected function buildSQLCriteria ( $queryString, $SearchCriteriaFieldsString ) {
            if (!empty($queryString)) {
                $SQLCriteria = [];
                try {
                    // BEGIN: Step 1 - Process queryString ----------------------------
                    foreach ($queryString AS $key => $value) {
                        if ($key == 'order')
                            $this->SQL_Order = $value;
                        else if ($key == 'limit')
                            $this->SQL_Limit = $value;
                        else if ($key == 'SearchCriteria') {
                            $SQLCriteria['conditions'][$key] = [
                                'type'      => 'AND', 
                                'field'     => $SearchCriteriaFieldsString, 
                                'operator'  => 'LIKE',
                                'value'     => '%'.$value.'%'
                            ];
                        } else if ($key == 'GoodsNotInCategoryId') {
                            $SQLCriteria['conditions'][$key] = [
                                'type'      => 'AND', 
                                'field'     => 't1.GoodCategoryId', 
                                'operator'  => '<>',
                                'value'     => $value
                            ];
                        } else {
                            $SQLCriteria['conditions'][$key] = [
                                'type'      => 'AND', 
                                'field'     => $key, 
                                'operator'  => '=',
                                'value'     => $value
                            ];
                        };
                    };
                    // END: Step 1 - Process queryString ------------------------------
                    // BEGIN: Step 2 - Load SQL conditions ----------------------------
                    if (isset($SQLCriteria['conditions'])) {
                        foreach ($SQLCriteria['conditions'] AS $identifier => $condition) {
                            $this->SQL_Params[':'.$identifier] = $condition['value'];
                            $this->SQL_Conditions .= ' '.$condition['type'].(isset($condition['begingroup']) ? ' (' : ' ').$condition['field'].' '.$condition['operator'].' :'.$identifier.(isset($condition['finishgroup']) ? ')' : '');
                        };
                    };
                    // END: Step 2 - Load SQL conditions ------------------------------
                } catch (Exception $ex) {
                    $this->response['msj'] = '['.get_class($this).'] SQL criteria error';
                    $this->response['error'] = $ex->getMessage();
                    return false; // Return FALSE on SQL criteria error
                };
            };
            return true;
        }

        // // SPECIAL FUNCTION: Validate criteria ********************************
        // private function JSON_isValidCriteria($json_criteria) {
        //     if (NULL != $json_criteria) {
        //         $array_criteria = json_decode($json_criteria, true);
        //         if (json_last_error() !== JSON_ERROR_NONE) {
        //             $this->response['msj'] = '['.get_class($this).'] JSON decodification error';
        //             return false; // Return FALSE on bad criteria JSON structure
        //         };
                
        //         if (count($array_criteria) < 1) { // If there isn't at least 1 index on the criteria
        //             $this->response['msj'] = '['.get_class($this).'] Criteria definition error';
        //             return false; // Return FALSE on bad criteria definition
        //         };

        //         if (isset($array_criteria['conditions'])) {
        //             foreach ($array_criteria['conditions'] AS $identifier => $condition) {
        //                 $this->SQL_Params[':'.$identifier] = $condition['value'];
        //                 $this->SQL_Conditions .= ' '.$condition['type'].(isset($condition['begingroup']) ? ' (' : ' ').$condition['field'].' '.$condition['operator'].' :'.$identifier.(isset($condition['finishgroup']) ? ')' : '');
        //             };
        //         };

        //         if (isset($array_criteria['order'])) {
        //             $this->SQL_Order = $array_criteria['order'];
        //         };

        //         if (isset($array_criteria['limit'])) {
        //             $this->SQL_Limit = $array_criteria['limit'];
        //         };
        //     };
        //     return true; // If no criteria defined or correctly defined, return TRUE
        // }

        // SPECIAL FUNCTION: Load necesary parameters depending on criteria ***
        protected function DB_loadParameters() {
            if (count($this->SQL_Params) > 0) {
                foreach ($this->SQL_Params AS $identifier => &$value) {
                    $this->SQL_Sentence->bindParam($identifier, $value, PDO::PARAM_STR);
                };
            };
        }

        // SPECIAL FUNCTION: Load response to be returned to the controller ***
        protected function DB_loadResponse($className = NULL) {
            $this->response['data'] = []; // Data Array to be included in the response
                while ($row_array = $this->SQL_Sentence->fetch(PDO::FETCH_ASSOC)) {
                    // Temporal implementation of the Good image **************
                    if ($className == 'Good')
                        $row_array['GoodImage'] = 'https://ip20soft.tech/JJ-POS-Backend/assets/images/' . $row_array['GoodId'] . '.png';
                    // Temporal implementation of the Good image **************
                    $this->response['data'][] = $row_array;
                };
            $this->response['count'] = count($this->response['data']); // Row count to be included in the response
        }

        // SPECIAL FUNCTION: Get global count for current SQL Query ***********
        protected function DB_getGlobalCount($SQL_GlobalQuery) {
            try {
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_GlobalQuery);
                $this->DB_loadParameters();
                $this->SQL_Sentence->execute();

                $this->response['globalCount'] = $this->SQL_Sentence->rowCount(); // Global Count
            }
            catch (PDOException $ex) {
                $this->response['globalCount'] = -1; // Means error
            };
        }
    }
?>