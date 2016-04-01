<?php

if (!class_exists('CloudApiService')) {

    class CloudApiService {

        private static $instance;
        private $cloudApiHelper;
        private $dbHelper;

        private function __construct() {
            if (!isset($this->cloudApiHelper)) {
                $this->cloudApiHelper = CloudApiHelper::getInstance();
                $this->dbHelper = WPDatabaseHelper::getInstance();
            }
        }

        public static function getInstance() {
            if (!isset(self::$instance)) {
                self::$instance = new CloudApiService();
            }

            return self::$instance;
        }

        public function loadMlsListingsToDB() {
            try {
                $soapClient = $this->cloudApiHelper->createSOAPClient();
                $loginContext = $this->cloudApiHelper->logIn($soapClient);
                $bid = $this->cloudApiHelper->getBoardIDs($soapClient, $loginContext);
                //$dbHelper = WPDatabaseHelper::getInstence();
                // 40675296
                //Absulate path of the csv data file,
                $csvFile = str_replace("\\", "/", WP_PLUGIN_DIR) . CloudApiConst::thisPluginRoot . "/data/data.csv";
                $fh = fopen($csvFile, 'w') or die("can't open file");
                $boardData = $this->cloudApiHelper->getFirst100Listing($soapClient, $loginContext, $bid);
                fwrite($fh, $boardData);
                //Collect data until less than 1000 data come in a call
                do {
                    $boardData = $this->cloudApiHelper->getListingAfter1000($soapClient, $loginContext, $bid);
                    fwrite($fh, $boardData['listings']);
                } while ($boardData['count'] >= 1000);

                fclose($fh);
                //Import the CSV data into mysql DB table
                $this->dbHelper->DumpData($csvFile);
                //$dir = plugin_basename( __FILE__ );//plugin_dir_path( "/data/data.csv" );
                // print_r($boardData, 0);
                $this->cloudApiHelper->logOff($soapClient, $loginContext);
                return 'Successfull';
            } catch (Exception $ex) {
                return 'Caught exception: ' . $ex->getMessage() . "\n";
            }
        }

        public function loadListingsPhotos() {
            try {
                $soapClient = $this->cloudApiHelper->createSOAPClient();
                $loginContext = $this->cloudApiHelper->logIn($soapClient);
                $bid = $this->cloudApiHelper->getBoardIDs($soapClient, $loginContext);
                $xmlFile = str_replace("\\", "/", WP_PLUGIN_DIR) . CloudApiConst::thisPluginRoot . "/data/photos.xml";

                $listingNumbers = $this->dbHelper->getAllLinstingsNumbers();

                if (!empty($listingNumbers)) {
                    $fh = fopen($xmlFile, 'w') or die("can't open file");

                    $photoData = $this->cloudApiHelper->getListingsPhotos($soapClient, $loginContext, $bid, $listingNumbers);
                    fwrite($fh, $photoData);

                    fclose($fh);
                    $this->dbHelper->DumpAllListingsPhotos($xmlFile);
                    //print_r($photoData, 0);
                }

                return 'Successfull';
            } catch (Exception $ex) {
                return 'Caught exception: ' . $ex->getMessage() . "\n";
            }
        }

        public function loadOpenHomeListingtoDB() {
            try {
                $soapClient = $this->cloudApiHelper->createSOAPClient();
                $loginContext = $this->cloudApiHelper->logIn($soapClient);
                $bid = $this->cloudApiHelper->getBoardIDs($soapClient, $loginContext);

                $csvFile = str_replace("\\", "/", WP_PLUGIN_DIR) . CloudApiConst::thisPluginRoot . "/data/openhomedata.csv";
                $fh = fopen($csvFile, 'w') or die("can't open file");
                $boardData = $this->cloudApiHelper->getOpenHomeInfoForBoard($soapClient, $loginContext, $bid);
                fwrite($fh, $boardData);
                //Collect data until less than 1000 data come in a call
                do {
                    $boardData = $this->cloudApiHelper->getListingAfter1000($soapClient, $loginContext, $bid);
                    fwrite($fh, $boardData['listings']);
                } while ($boardData['count'] >= 1000);

                fclose($fh);

                return 'Successfull';
            } catch (Exception $ex) {
                return 'Caught exception: ' . $ex->getMessage() . "\n";
            }
        }

        public function loadMLSIDxData() {
            try {
                $this->loadMlsListingsToDB();
                $this->loadListingsPhotos();
                //$this->loadOpenHomeListingtoDB();
                return "Successfull";
            } catch (Exception $ex) {
                return $ex->getMessage();
            }
        }

        public function LoadDataViaAjax() {
            $this->loadMLSIDxData();
            $result['type'] = "success";
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                $result = json_encode($result);
                echo $result;
            } else {
                header("Location: " . $_SERVER["HTTP_REFERER"]);
            }
            wp_die();
        }

    }

}
?>