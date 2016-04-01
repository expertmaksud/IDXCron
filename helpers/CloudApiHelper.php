<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */



/**
 * This is a helper class to access IDX listing using Ihomefinder cloud webservice 
 *
 * @author Maksud-Ul-Alam
 */
if (!class_exists("CloudApiHelper")) {

    class CloudApiHelper {

        private static $instance;

        private function __construct() {
            
        }

        public static function getInstance() {
            if (!isset(self::$instance)) {
                self::$instance = new CloudApiHelper();
            }

            return self::$instance;
        }

        // Create SOAP client and get a list of all methods.
        public function getAllMethods() {

            try {
                $client = new SoapClient(CloudApiConst::BULK_DATA_API, array('trace' => 1)); // trace on for debugging
                print_r($client->__getFunctions());  // get all methods
            } catch (SOAPFault $exception) {
                // Some standard code to see what went wrong. We'll use it on each method call.
                var_dump($exception);
                echo "Request :\n", $client->__getLastRequest(), "\n";
                echo "Response :\n", $client->__getLastResponse(), "\n";
            }
        }

        public function createSOAPClient() {
            try {
                $client = new SoapClient(CloudApiConst::BULK_DATA_API, array('trace' => 1));
                return $client;
            } catch (SoapFault $exception) {
                var_dump($exception);
            }
        }

        // Login and get context. Context is used in subsequent calls. 
        public function logIn(SoapClient $soapClient) {

            $credentials = array('username' => CloudApiConst::apiUserName, 'password' => CloudApiConst::apiPassword);
            try {
                $response = $soapClient->login($credentials);
                //print_r($response, 0);
                $context = (int) $response->return; // get the context as an integer
                return $context;
            } catch (SOAPFault $exception) {
                var_dump($exception);
                echo "Request :\n", $soapClient->__getLastRequest(), "\n";
                echo "Response :\n", $soapClient->__getLastResponse(), "\n";
            }
        }

        // Get board IDs. Response is an array of MLS System board IDs (bids).
        public function getBoardIDs(SoapClient $soapClient, $loginContext) {
            try {
                $bids = $soapClient->getBoards(array('context' => $loginContext));
                //echo 'Board Ids: ';
                //print_r($bids, 0);	
                // Get the first board id to use in rest of script
                if (sizeof($bids->return) == 1) {
                    $bid = (int) $bids->return; // if just 1 board, then a single bid is returned -- no array
                } else {
                    $bid = (int) $bids->return[0]; // if more than 1 board, then an array of bids is returned; use the first
                }
                return $bid;
            } catch (SOAPFault $exception) {
                var_dump($exception);
                echo "Request :\n", $soapClient->__getLastRequest(), "\n";
                echo "Response :\n", $soapClient->__getLastResponse(), "\n";
            }
        }

        // Get board IDX display and compliance data.
        public function getBoardData(SoapClient $soapClient, $loginContext, $bid) {

            try {
                $response = $soapClient->getBoardData(array('context' => $loginContext, 'boardID' => $bid));
                //print_r($response, 0);
                return $response;
            } catch (SOAPFault $exception) {
                var_dump($exception);
                echo "Request :\n", $soapClient->__getLastRequest(), "\n";
                echo "Response :\n", $soapClient->__getLastResponse(), "\n";
            }
        }

        // Get listing data headers.
        public function getHeader(SoapClient $soapClient, $context) {

            try {
                $headers = $soapClient->getHeaders(array('context' => $context));
                //print_r($headers, 0);
                return $headers->return;
            } catch (SOAPFault $exception) {
                var_dump($exception);
                echo "Request :\n", $soapClient->__getLastRequest(), "\n";
                echo "Response :\n", $soapClient->__getLastResponse(), "\n";
            }
        }

        // Get first 1000 listings in the board. Response is CSV encapsulated in XML.
        public function getFirst100Listing(SoapClient $soapClient, $loginContext, $bid) {

            ini_set('memory_limit', '5048M'); // there can be a lot of listings, so increase memory
            ini_set('max_execution_time', 3000);
            try {
                $response = $soapClient->getAllListings(array('context' => $loginContext, 'boardID' => $bid));

                $listingsXML = $response->return; // Get the XML-encapsulated listing data
                $listingsSimpleXML = new SimpleXMLElement($listingsXML);  // Create a simpleXML-formatted enclosure for the listing data
                $listingsCSV = (String) $listingsSimpleXML->Data;
                return $listingsCSV;
            } catch (SOAPFault $exception) {
                var_dump($exception);
                echo "Request :\n", $soapClient->__getLastRequest(), "\n";
                echo "Response :\n", $soapClient->__getLastResponse(), "\n";
            }
        }

        // Get next 1000 listings in the board. Response is CSV encapsulated in XML.
        public function getListingAfter1000(SoapClient $soapClient, $loginContext, $bid) {

            try {
                $response = $soapClient->getListingsSince(array('context' => $loginContext, 'boardID' => $bid));

                $listingsXML = $response->return; // Get the XML-encapsulated listing data
                $listingsSimpleXML = new SimpleXMLElement($listingsXML);  // Create a simpleXML-formatted enclosure for the listing data
                $listingsCSV = (String) $listingsSimpleXML->Data;
                $listingCount = (int) $listingsSimpleXML->Count;
                return array("listings" => $listingsCSV, "count" => $listingCount);
            } catch (SOAPFault $exception) {
                var_dump($exception);
                echo "Request :\n", $soapClient->__getLastRequest(), "\n";
                echo "Response :\n", $soapClient->__getLastResponse(), "\n";
            }
        }

        // Get listings updated since midnight yesterday.
        public function getUpdatedListingSinceDate(SoapClient $soapClient, $loginContext, $bid) {

            // $sinceDate must be a string of format YYYY-MM-DD HH:mm:SS sss GMT
            date_default_timezone_set(CloudApiConst::localTimeZone);  // replace with your local time zone
            $yesterday = mktime(0, 0, 0, date('m'), date('d') - 1, date('Y')); // midnight yesterday in your local time zone
            date_default_timezone_set('GMT'); // reset time zone to GMT; $yesterday is adjusted to GMT
            $sinceDate = date('Y-m-d H:i:s u T', $yesterday);  // format $sinceDate as required by web service
            try {
                $response = $soapClient->getListingsSince(array('context' => $loginContext, 'boardID' => $bid, 'sinceDate' => $sinceDate));
                return $response->return;
            } catch (SOAPFault $exception) {
                var_dump($exception);
                echo "Request :\n", $soapClient->__getLastRequest(), "\n";
                echo "Response :\n", $soapClient->__getLastResponse(), "\n";
            }
        }

        // Get photos for a listing. 
        public function getListingPhotos(SoapClient $soapClient, $loginContext, $bid, $listingID) {

            try {
                $response = $soapClient->getPhotos(array('context' => $loginContext, 'boardID' => $bid, 'listingNumber' => $listingID)); // listing # is in field 0 of listing
                //echo 'Response for listing photo: ';
                //print_r($response, 0);
                return $response->return;
            } catch (SOAPFault $exception) {
                var_dump($exception);
                echo "Request :\n", $soapClient->__getLastRequest(), "\n";
                echo "Response :\n", $soapClient->__getLastResponse(), "\n";
            }
        }

        // Get photos for multiple listings -- use the second & third listings from above.
        public function getListingsPhotos(SoapClient $soapClient, $loginContext, $bid, array $listingIDs) {

            try {
                $response = $soapClient->getPhotosForMultipleListings(array('context' => $loginContext,
                    'boardID' => $bid,
                    'listingNumbers' => $listingIDs));
                //echo "Photo for multipule listing: ";
                //print_r($response, 0);
                return $response->return;
            } catch (SOAPFault $exception) {
                var_dump($exception);
                echo "Request :\n", $soapClient->__getLastRequest(), "\n";
                echo "Response :\n", $soapClient->__getLastResponse(), "\n";
            }
        }

        public function getOpenHomeInfoForBoard(SoapClient $soapClient, $loginContext, $bid) {
            // Get all open homes information for a board. Note that sinceDate parameter is omitted, so all open homes are returned.
            try {
                $response = $soapClient->getOpenHomeInfoForBoard(array('context' => $loginContext, 'boardID' => $bid));


                //$openListingNumber = $openHome->ListingNumber; // save the last open home listing number for use below
                return $response->return;
            } catch (SOAPFault $exception) {
                var_dump($exception);
                echo "Request :\n", $soapClient->__getLastRequest(), "\n";
                echo "Response :\n", $soapClient->__getLastResponse(), "\n";
            }
        }

        // Get open home info for a specific listing.
        public function getOpenHomeInfoByListing(SoapClient $soapClient, $loginContext, $bid, $openListingNumber) {

            try {
                $response = $soapClient->getOpenHomeInfo(array('context' => $loginContext, 'boardID' => $bid, 'listingNumber' => $openListingNumber));

                return $response->return;
            } catch (SOAPFault $exception) {
                var_dump($exception);
                echo "Request :\n", $soapClient->__getLastRequest(), "\n";
                echo "Response :\n", $soapClient->__getLastResponse(), "\n";
            }
        }

        public function logOff(SoapClient $soapClient, $loginContext) {
            try {
                $response = $soapClient->logoff(array('context' => $loginContext));
                return $response;
            } catch (SOAPFault $exception) {
                var_dump($exception);
                echo "Request :\n", $soapClient->__getLastRequest(), "\n";
                echo "Response :\n", $soapClient->__getLastResponse(), "\n";
            }
        }

    }

}
?>