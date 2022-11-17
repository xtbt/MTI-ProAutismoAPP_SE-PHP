<?php
    require_once( './System/Database.php' );

    class Sale {
        // DataBase properties
        private $DB_Connector = NULL;
        private $SQL_Tables = 'tblExits AS t1 LEFT JOIN 
                            tblUsers AS t2 USING(UserId) LEFT JOIN 
                            tblCustomers AS t3 USING(CustomerId)';
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
        // public $Input_GoodId;           // tblExits_Inputs_Goods::Input_GoodId
        // public $Quantity;               // tblExits_Inputs_Goods::Quantity
        // public $GoodSalePrice;              // tblExits_Inputs_Goods::GoodSalePrice
        // public $InputId;                // tblInputs_Goods::InputId
        // public $GoodId;                 // tblInputs_Goods::GoodId
        // public $GoodCategoryId;         // tblGoods::GoodCategoryId
        // public $GoodSubCategoryId;      // tblGoods::GoodSubCategoryId
        // public $GoodBrandId;            // tblGoods::GoodBrandId
        // public $GoodName;               // tblGoods::GoodName
        // public $GoodDescription;        // tblGoods::GoodDescription
        public $Details;                // Details inside main record
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
                                tblUsers AS t2 USING(UserId) LEFT JOIN 
                                tblCustomers AS t3 USING(CustomerId)';
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

        // SPECIAL FUNCTION: Load details corresponding to each main record ***
        private function DB_loadResponseDetails($ExitId = NULL) {
            $RecordIndex = $ExitId === NULL ? count($this->response['data']) - 1 : $ExitId;
            $ExitId = $ExitId === NULL ? $this->response['data'][$RecordIndex]['ExitId'] : $ExitId;

            $AllOkFlag = false;

            try {
                $SQL_Query = 'SELECT 
                    t2.GoodId AS GoodId, 
                    t3.GoodName AS GoodName, 
                    t1.Quantity AS Quantity, 
                    t1.SalePrice AS SalePrice, 
                    t1.ItemComboId AS ItemComboId 
                    FROM 
                    tblExits_Inputs_Goods AS t1 LEFT JOIN 
                    tblInputs_Goods AS t2 USING(Input_GoodId) LEFT JOIN 
                    tblGoods AS t3 USING(GoodId) 
                    WHERE 
                    t1.ExitId = :ExitId';
                $SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $SQL_Sentence->bindParam(':ExitId', $ExitId, PDO::PARAM_INT);
                $SQL_Sentence->execute();
                if ($SQL_Sentence->rowCount() < 1) {
                    $this->response['data'][$RecordIndex]['Details'] = [
                        'GoodId' => -1,
                        'GoodName' => 'Error: Can\'t read Database data',
                        'Quantity' => 0,
                        'SalePrice' => '0',
                        'ItemComboId' => 0
                    ];
                    $AllOkFlag = false;
                } else {
                    while ($DetailRow = $SQL_Sentence->fetch(PDO::FETCH_ASSOC)) {
                        $this->response['data'][$RecordIndex]['Details'][] = [
                            'GoodId' => $DetailRow['GoodId'],
                            'GoodName' => $DetailRow['GoodName'],
                            'Quantity' => $DetailRow['Quantity'],
                            'SalePrice' => $DetailRow['SalePrice'],
                            'ItemComboId' => $DetailRow['ItemComboId']
                        ];
                    };
                    $AllOkFlag = true;
                }

            } catch (PDOException $ex) {
                $this->response['data'][$RecordIndex]['Details'] = [
                    'GoodId' => -1,
                    'GoodName' => 'Error: PDO Exception', 
                    'Quantity' => 0,
                    'SalePrice' => '0',
                    'ItemComboId' => 0
                ];
                $this->response['error'] = $ex->getMessage();
                $AllOkFlag = false;
            };
            $this->response['msj'] = $AllOkFlag ? 'All data retrieved' : 'Error: Some details could\'t be read';
        }

        // SPECIAL FUNCTION: Load response to be returned to the controller ***
        private function DB_loadResponse() {
            $AllOKFlag = true;
            $this->response['data'] = []; // Data Array to be included in the response
            while ($row_array = $this->SQL_Sentence->fetch(PDO::FETCH_ASSOC)) {
                $this->response['data'][] = $row_array;
                $AllOkFlag = $AllOKFlag && $this->DB_loadResponseDetails();
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
                    t1.ExitId AS ExitId, 
                    t1.ExitDate AS ExitDate, 
                    t1.UserId AS UserId, 
                    t1.CustomerId AS CustomerId, 
                    t1.ExitStatus AS ExitStatus 
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
        public function getSale($ExitId) {
            $this->DB_initProperties();
            if (is_numeric($ExitId)) {
                $this->SQL_Conditions .= ' AND ExitId = :ExitId';
                $this->SQL_Limit = '0,1';
            }
            else {
                $this->response['msj'] = '['.get_class($this).'] Error: Invalid parameter';
                return $this->response;
            };
            
            try {
                $SQL_Query = 'SELECT 
                    t1.ExitId AS ExitId, 
                    t1.ExitDate AS ExitDate, 
                    t1.UserId AS UserId, 
                    t1.CustomerId AS CustomerId, 
                    t1.ExitStatus AS ExitStatus 
                    FROM '
                    .$this->SQL_Tables.
                    ' WHERE '
                    .$this->SQL_Conditions.
                    ' ORDER BY '
                    .$this->SQL_Order.
                    (!is_null($this->SQL_Limit) ? ' LIMIT '.$this->SQL_Limit.';' : ';');
                
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':ExitId', $ExitId, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();
                if ($this->SQL_Sentence->rowCount() < 1) {
                    $this->response['count'] = 0; // No records found
                    $this->response['msj'] = '['.get_class($this).'] No records found';
                    return $this->response; // Return response with no records
                };

                // If there is data, we build the response with DB info -------
                $this->response['data'][$ExitId] = $this->SQL_Sentence->fetch(PDO::FETCH_ASSOC);
                $this->updateProperties($this->response['data'][$ExitId]);
                $this->DB_loadResponseDetails($ExitId); // To build Details Array
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
        // START: Detail validation functions *********************************
        private function evaluateCombo( $Element ) {
            if ( empty($Element['GoodId']) || empty($Element['GoodName']) || empty($Element['Quantity']) || empty($Element['GoodSalePrice']) ) {
                $result = [
                    'GoodId' => $Element['GoodId'], 
                    'GoodName' => $Element['GoodName'], 
                    'isValid' => false, 
                    'Error' => 'One or more Combo fields are missing'
                ];
                return $result;
            };
            
            // Step 1: We get the Combo ID (To evaluate)
            ['GoodId' => $GoodId, 
            'GoodName' => $GoodName, 
            'Quantity' => $Quantity, 
            'GoodSalePrice' => $GoodSalePrice, 
            'ComboId' => $ComboId] = $Element;

            try {
                // Step 2: We get all the Goods inside that combo (To evaluate)
                $SQL_Query = 'SELECT 
                    t3.GoodId AS GoodId, 
                    t3.GoodName AS GoodName, 
                    t2.GoodQuantity AS Quantity, 
                    t3.GoodSalePrice AS GoodSalePrice 
                    FROM 
                    tblCombos AS t1 LEFT JOIN 
                    tblCombos_Goods AS t2 USING(ComboId) LEFT JOIN 
                    tblGoods AS t3 USING(GoodId) 
                    WHERE 
                    t1.ComboId = :ComboId';
                $SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $SQL_Sentence->bindParam(':ComboId', $ComboId, PDO::PARAM_INT);
                $SQL_Sentence->execute();
                
                if ($SQL_Sentence->rowCount() < 1) {
                    $result = [
                        'GoodId' => $GoodId, 
                        'GoodName' => $GoodName, 
                        'isValid' => false, 
                        'Error' => 'There aren\'t any Goods inside this combo'
                    ];
                } else {
                    $isValid = true;
                    while ($ComboRow = $SQL_Sentence->fetch(PDO::FETCH_ASSOC)) {
                        $item = $this->evaluateDetail( $ComboRow );
                        $isValid = $isValid && $item['isValid'];
                        $items[] = $item;
                    };
                    $result = [
                        'GoodId' => $GoodId, 
                        'GoodName' => $GoodName, 
                        'isValid' => $isValid, 
                        'Error' => !$isValid ? 'Some items can\'t be processed' : NULL,
                        'Items' => $items
                    ];
                };
            } catch (PDOException $ex) {
                $result = [
                    'GoodId' => $GoodId, 
                    'GoodName' => $GoodName, 
                    'isValid' => false, 
                    'Error' => $ex->getMessage()
                ];
            };
            return $result;
        }

        private function evaluateDetail( $Element ) {
            if ( empty($Element['GoodId']) || empty($Element['GoodName']) || empty($Element['Quantity']) || empty($Element['GoodSalePrice']) ) {
                $result = [
                    'GoodId' => $Element['GoodId'], 
                    'GoodName' => $Element['GoodName'], 
                    'isValid' => false, 
                    'Error' => 'One or more Details fields are missing'
                ];
                return $result;
            };
            
            ['GoodId' => $GoodId, 
            'GoodName' => $GoodName, 
            'Quantity' => $Quantity, 
            'GoodSalePrice' => $GoodSalePrice] = $Element;

            try {
                $SQL_Query = 'SELECT 
                    t1.GoodId AS GoodId, 
                    t1.GoodName AS GoodName, 
                    SUM(t2.Remain) AS Remain 
                    FROM 
                    tblGoods AS t1 LEFT JOIN tblInputs_Goods AS t2 USING(GoodId) 
                    WHERE 
                    t1.GoodId = :GoodId AND 
                    t2.Input_GoodId IS NOT NULL';
                $SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $SQL_Sentence->bindParam(':GoodId', $GoodId, PDO::PARAM_INT);
                $SQL_Sentence->execute();
                
                if ($SQL_Sentence->rowCount() < 1) {
                    $result = [
                        'GoodId' => $GoodId, 
                        'GoodName' => $GoodName, 
                        'isValid' => false, 
                        'Error' => 'No inputs for this item found'
                    ];
                } else {
                    $Row = $SQL_Sentence->fetch(PDO::FETCH_ASSOC);
                    if ($Row['Remain'] < $Quantity) {
                        $result = [
                            'GoodId' => $GoodId, 
                            'GoodName' => $GoodName, 
                            'isValid' => false, 
                            'Error' => 'Not enough stock for that quantity'
                        ];
                    } else {
                        $result = [
                            'GoodId' => $GoodId, 
                            'GoodName' => $GoodName, 
                            'isValid' => true, 
                            'Error' => NULL
                        ];
                    };
                };
            } catch (PDOException $ex) {
                $result = [
                    'GoodId' => $GoodId, 
                    'GoodName' => $GoodName, 
                    'isValid' => false, 
                    'Error' => $ex->getMessage()
                ];
            };
            return $result;
        }

        private function checkDetails( $DetailsArray ) {
            $OK_Flag = true; // If no errors on details, just go ahead
            foreach ( $DetailsArray AS $Index => $Element ) {
                if (empty($Element['ComboId']))
                    $evaluation = $this->evaluateDetail( $Element );
                else
                    $evaluation = $this->evaluateCombo( $Element );
                $results[] = $evaluation;
                $OK_Flag = $OK_Flag && $evaluation['isValid'];
            };
            if ( !$OK_Flag ) {
                $this->response['msj'] = '['.get_class($this).'] Error: One or more elements did not pass validation';
                $this->response['error'] = $results;
                return false;
            };
            return true;
        }
        // END: Detail validation functions ***********************************
        // ********************************************************************

        // ********************************************************************
        // START: Detail processing and registration **************************
        private function updateInputRemaining($InputRow, $ExitQuantity) {
            ['Input_GoodId' => $Input_GoodId, 
            'InputId' => $InputId, 
            'Remain' => $Remain] = $InputRow;

            try {
                $SQL_Query = 'UPDATE tblInputs_Goods SET 
                    Remain = Remain - :ExitQuantity 
                    WHERE 
                    Input_GoodId = :Input_GoodId';
                $SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $SQL_Sentence->bindParam(':ExitQuantity', $ExitQuantity, PDO::PARAM_STR);
                $SQL_Sentence->bindParam(':Input_GoodId', $Input_GoodId, PDO::PARAM_INT);
                $SQL_Sentence->execute();
                if ($SQL_Sentence->rowCount() < 1)
                    return false;
                else
                    return true;
            } catch (PDOException $ex) {
                return false;
            };
        }
        
        private function registerSaleDetail( $Element, $InputRow ) {
            ['GoodId' => $GoodId, 
            'GoodName' => $GoodName, 
            'Quantity' => $Quantity, 
            'GoodSalePrice' => $GoodSalePrice, 
            'SalePrice' => $SalePrice] = $Element;
            // If this item is part of a combo, we set ItemComboId
            $ItemComboId = isset($Element['ItemComboId']) ? $Element['ItemComboId'] : 0;

            ['Input_GoodId' => $Input_GoodId, 
            'InputId' => $InputId, 
            'Remain' => $Remain] = $InputRow;

            // Exit detail fields ---------------------------------------------
            $Exit_Input_GoodId = NULL;
            $ExitQuantity = $Remain >= $Quantity ? $Quantity : $Remain;
            $Status = 1;
            try {
                $SQL_Query = 'INSERT INTO tblExits_Inputs_Goods VALUES (
                    :Exit_Input_GoodId, 
                    :ExitId, 
                    :Input_GoodId, 
                    :Quantity, 
                    :SalePrice, 
                    :ItemComboId, 
                    :Status)';
                $SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $SQL_Sentence->bindParam(':Exit_Input_GoodId', $Exit_Input_GoodId, PDO::PARAM_INT);
                $SQL_Sentence->bindParam(':ExitId', $this->ExitId, PDO::PARAM_INT);
                $SQL_Sentence->bindParam(':Input_GoodId', $Input_GoodId, PDO::PARAM_INT);
                $SQL_Sentence->bindParam(':Quantity', $ExitQuantity, PDO::PARAM_STR);
                $SQL_Sentence->bindParam(':SalePrice', $SalePrice, PDO::PARAM_STR);
                $SQL_Sentence->bindParam(':ItemComboId', $ItemComboId, PDO::PARAM_INT);
                $SQL_Sentence->bindParam(':Status', $Status, PDO::PARAM_INT);
                $SQL_Sentence->execute();
                if ($SQL_Sentence->rowCount() < 1)
                    return false;
                else
                    return $this->updateInputRemaining($InputRow, $ExitQuantity);
            } catch (PDOException $ex) {
                return false;
            };
        }

        private function prepareSaleCombo( $Element ) {
            if ( empty($Element['GoodId']) || empty($Element['GoodName']) || empty($Element['Quantity']) || empty($Element['GoodSalePrice']) ) {
                $result = [
                    'GoodId' => $Element['GoodId'], 
                    'GoodName' => $Element['GoodName'], 
                    'isOK' => false, 
                    'Error' => 'One or more Combo fields are missing'
                ];
                return $result;
            };

            // Step 1: We get the Combo ID (To process)
            ['GoodId' => $GoodId, 
            'GoodName' => $GoodName, 
            'Quantity' => $Quantity, 
            'GoodSalePrice' => $GoodSalePrice, 
            'ComboId' => $ComboId] = $Element;

            try {
                // Step 2: We get all the Goods inside that combo (To process)
                $SQL_Query = 'SELECT 
                    t3.GoodId AS GoodId, 
                    t3.GoodName AS GoodName, 
                    t2.GoodQuantity AS Quantity, 
                    t3.GoodSalePrice AS GoodSalePrice, 
                    t1.ComboDiscount AS ComboDiscount 
                    FROM 
                    tblCombos AS t1 LEFT JOIN 
                    tblCombos_Goods AS t2 USING(ComboId) LEFT JOIN 
                    tblGoods AS t3 USING(GoodId) 
                    WHERE 
                    t1.ComboId = :ComboId';
                $SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $SQL_Sentence->bindParam(':ComboId', $ComboId, PDO::PARAM_INT);
                $SQL_Sentence->execute();

                if ($SQL_Sentence->rowCount() < 1) {
                    $result = [
                        'GoodId' => $GoodId, 
                        'GoodName' => $GoodName, 
                        'isOK' => false, 
                        'Error' => 'There aren\'t any Goods inside this combo'
                    ];
                } else {
                    $isOK = true;
                    while ($ComboRow = $SQL_Sentence->fetch(PDO::FETCH_ASSOC)) {
                        // START: PRICE DISCOUNT UPDATE ON EACH COMBO ITEMS
                        $ComboRow['ItemComboId'] = $ComboId; // Set the ComboId field to indicate that this particular item is part of a combo
                        $ComboRow['Quantity'] = $ComboRow['Quantity'] * $Quantity; // Get the absolute quantity of a particular item, based on the number of combos added to tha cart
                        $ComboRow['GoodSalePrice'] = $ComboRow['GoodSalePrice'] - ( $ComboRow['GoodSalePrice'] * $ComboRow['ComboDiscount'] / 100 ); // Get the correct price of a particular item based on the discount of the combo
                        // END: PRICE DISCOUNT UPDATE ON EACH COMBO ITEMS
                        $item = $this->prepareSaleDetail( $ComboRow );
                        $isOK = $isOK && $item['isOK'];
                        $items[] = $item;
                    };
                    $result = [
                        'GoodId' => $GoodId, 
                        'GoodName' => $GoodName, 
                        'isOK' => $isOK, 
                        'Error' => !$isOK ? 'Some items can\'t be processed' : NULL,
                        'Items' => $items
                    ];
                };
            } catch (PDOException $ex) {
                $result = [
                    'GoodId' => $GoodId, 
                    'GoodName' => $GoodName, 
                    'isOK' => false, 
                    'Error' => $ex->getMessage()
                ];
            };
            return $result;
        }

        private function prepareSaleDetail( $Element ) {
            $Element['SalePrice'] = $Element['Quantity'] * $Element['GoodSalePrice']; // Get the correct price of a particular item based on GoodSalePrice * Quantity
            ['GoodId' => $GoodId, 
            'GoodName' => $GoodName, 
            'Quantity' => $Quantity, 
            'GoodSalePrice' => $GoodSalePrice ] = $Element;

            try {
                $SQL_Query = 'SELECT 
                    Input_GoodId, 
                    InputId, 
                    GoodId, 
                    Remain 
                    FROM 
                    tblInputs_Goods 
                    WHERE 
                    GoodId = :GoodId AND 
                    Remain > 0 
                    ORDER BY 
                    Input_GoodId';
                $SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $SQL_Sentence->bindParam(':GoodId', $GoodId, PDO::PARAM_INT);
                $SQL_Sentence->execute();
                
                if ($SQL_Sentence->rowCount() < 1) {
                    $result = [
                        'GoodId' => $GoodId, 
                        'GoodName' => $GoodName, 
                        'isOK' => false, 
                        'Error' => 'No inputs for this item found'
                    ];
                } else {
                    // START: Detail registration loop ------------------------
                    while ( $InputRow = $SQL_Sentence->fetch(PDO::FETCH_ASSOC) ) {
                        if ($InputRow['Remain'] >= $Quantity) {
                            if ( $this->registerSaleDetail( $Element, $InputRow ) ) {
                                $Quantity = 0;
                                $result = [
                                    'GoodId' => $GoodId, 
                                    'GoodName' => $GoodName, 
                                    'isOK' => true, 
                                    'Error' => NULL
                                ];
                            }
                            else {
                                $result = [
                                    'GoodId' => $GoodId, 
                                    'GoodName' => $GoodName, 
                                    'isOK' => false, 
                                    'Error' => 'Can\'t register detail'
                                ];
                                break;
                            };
                        } else {
                            if ( $this->registerSaleDetail( $Element, $InputRow ) ) {
                                $Quantity = $Quantity - $InputRow['Remain'];
                            }
                            else {
                                $result = [
                                    'GoodId' => $GoodId, 
                                    'GoodName' => $GoodName, 
                                    'isOK' => false, 
                                    'Error' => 'Can\'t register detail'
                                ];
                                break;
                            };
                        };
                        if ( $Quantity == 0 ) break;
                    };
                    // END: Detail registration loop --------------------------
                };
            } catch (PDOException $ex) {
                $result = [
                    'GoodId' => $GoodId, 
                    'GoodName' => $GoodName, 
                    'isOK' => false, 
                    'Error' => $ex->getMessage()
                ];
            };
            return $result;
        }

        private function processSaleDetails( $DetailsArray ) {
            $OK_Flag = true; // If no errors on details, just go ahead
            foreach ( $DetailsArray AS $Index => $Element ) {
                if (empty($Element['ComboId']))
                    $saleDetailPreparation = $this->prepareSaleDetail( $Element );
                else
                    $saleDetailPreparation = $this->prepareSaleCombo( $Element );
                $results[] = $saleDetailPreparation;
                $OK_Flag = $OK_Flag && $saleDetailPreparation['isOK'];
                $this->response['data']['Details'] = $results;
            };
            if ( !$OK_Flag ) {
                $this->response['msj'] = '['.get_class($this).'] Error: One or more elements were not registered';
                $this->response['error'] = $results;
                return false;
            };
            return true; // All sale details are valid
        }
        // END: Detail processing and registration ****************************
        // ********************************************************************

        // ********************************************************************
        // (CREATE) CREATE NEW RECORD INTO DB *********************************
        // ********************************************************************
        public function createSale($ExitDate, $UserId, $CustomerId, $DetailsArray) {
            $this->DB_initProperties();
            ## TODO: VALIDATION INSTRUCTIONS FOR PARAMETERS -------------------
            ## ... 
            // Meanwhile ......
            if (empty($ExitDate) || empty($UserId) || empty($CustomerId)) {
                $this->response['msj'] = '['.get_class($this).'] Error: Main fields cannot be empty';
                $this->response['error'] = 'One or more of the main fields are empty';
                return $this->response;
            };
            ## TODO: VALIDATION INSTRUCTIONS FOR PARAMETERS -------------------
            $ExitStatus = 1; // 1(Active) by default on new records

            // STEP 1: Check DetailsArray -------------------------------------
            if ( !$this->checkDetails( $DetailsArray ) ) {
                return $this->response;
            }
            // ----------------------------------------------------------------
            
            // STEP 2: Create transaction and register Sale -------------------
            $ExitId = NULL; // Default for autoincremental
            $ExitStatus = 1; // Default for new registers
            try {
                $this->DB_Connector->beginTransaction();
                $SQL_Query = 'INSERT INTO tblExits VALUES (
                  :ExitId, 
                  :ExitDate, 
                  :UserId, 
                  :CustomerId, 
                  :ExitStatus)';
                  
                $this->SQL_Sentence = $this->DB_Connector->prepare($SQL_Query);
                $this->SQL_Sentence->bindParam(':ExitId', $ExitId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':ExitDate', $ExitDate, PDO::PARAM_STR);
                $this->SQL_Sentence->bindParam(':UserId', $UserId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':CustomerId', $CustomerId, PDO::PARAM_INT);
                $this->SQL_Sentence->bindParam(':ExitStatus', $ExitStatus, PDO::PARAM_INT);
                $this->SQL_Sentence->execute();

                if ($this->SQL_Sentence->rowCount() != 0) {
                    $ExitId = $this->DB_Connector->lastInsertId(); // Get newly created record ID
                    $this->response['data'] = [
                        'ExitId' => $ExitId,
                        'UserId' => $UserId
                    ];
                    $this->response['msj'] = '['.get_class($this).'] Ok: Main record created successfully';
                    // STEP 3 - BEGIN: SECOND QUERY FOR DETAILS ---------------
                    $this->ExitId = $ExitId; // Apply new created ID to object
                    if ($this->processSaleDetails( $DetailsArray )) {
                        $this->response['count'] = 1;
                        $this->response['msj'] = '['.get_class($this).'] Ok: Main record and details created successfully';
                        $this->DB_Connector->commit(); // Only if there were no errors during querys
                    }
                    else {
                        $this->DB_Connector->rollBack();
                    };
                    // STEP 3 - END: SECOND QUERY FOR DETAILS -----------------
                }
                else {
                    $this->response['msj'] = '['.get_class($this).'] Error: Cannot create new record';
                    $this->response['error'] = 'The main sale register was not created due to an error';
                    $this->DB_Connector->rollBack();
                };
            }
            catch (PDOException $ex) {
                $this->response['msj'] = '['.get_class($this).'] Error: SQL Exception';
                $this->response['error'] = $ex->getMessage();
                $this->DB_Connector->rollBack();
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
        // (AUXILIAR PROCEDURES) PRIVATE SECUNDARY PROCEDURES *****************
        // ********************************************************************
        
    }
?>