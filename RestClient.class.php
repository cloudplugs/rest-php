<?php
    /* <license>
    Copyright 2014 CloudPlugs Inc.

    Licensed to the Apache Software Foundation (ASF) under one
    or more contributor license agreements.  See the NOTICE file
    distributed with this work for additional information
    regarding copyright ownership.  The ASF licenses this file
    to you under the Apache License, Version 2.0 (the
    "License"); you may not use this file except in compliance
    with the License.  You may obtain a copy of the License at

      http://www.apache.org/licenses/LICENSE-2.0

    Unless required by applicable law or agreed to in writing,
    software distributed under the License is distributed on an
    "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
    KIND, either express or implied.  See the License for the
    specific language governing permissions and limitations
    under the License.
    </license>
    */


    namespace cloudplugs;

    /*!
    *  @brief     The PHP Client library is used to communicate with Cloudplugs platform with HTTP REST requests to the server.
    *  @details   This class contains basically two sets of methods:  a set of configuration functions used to configure the class's parameters, and a set of functions used to perform HTTP requests to Cloudplugs server.
    *             A set of configuration options are available: for example it is possible to force the library to return server response in a specific output (json, array, object) or run the library in debug mode (display debugging information).
    *             All HTTP requests to the server are synchronous. Many functions are provided in two different forms: an explicit parameters version and a compact parameter version (passed within an array or a JSON encoded string).
    */

    class RestClient {

        // consts
        const TIMEOUT = 60; //remote request timeout value (seconds)
        const BASEURL = "https://api.cloudplugs.com/iot/";
        const HTTP_STR = "http://";
        const HTTPS_STR = "https://";

        /* Clouplugs REST API: HTTP headers */
        const PLUG_AUTH_HEADER = "X-Plug-Auth: ";
        const PLUG_ID_HEADER = "X-Plug-Id: ";
        const PLUG_EMAIL_HEADER = "X-Plug-Email: ";
        const PLUG_MASTER_HEADER = "X-Plug-Master: ";

        const CONTENT_TYPE_JSON = "Content-type: application/json";

        /* Cloudplugs REST API: resource paths */
        const PATH_DATA = "data";
        const PATH_DEVICE = "device";
        const PATH_CHANNEL = "channel";

        /* Cloudplugs REST API: HTTP Body parameters */
        const CTRL = "ctrl";
        const HWID = "hwid";
        const NAME = "name";
        const MODEL = "model";
        const PASS = "pass";
        const PERM = "perm";
        const PROPS = "props";
        const STATUS = "status";
        const PROP_LINKS = "prop_links";
        const DATA = "data";
        const BEFORE = "before";
        const AFTER = "after";
        const OF = "of";
        const OFFSET = "offset";
        const LIMIT = "limit";
        const AUTH = "auth";
        const ID = "id";
        const AT = "at";
        const CHMASK = "channel_mask";

        const LOCATION = "location";
        const LONGITUDE = "x";
        const LATITUDE = "y";
        const ACCURACY = "r";
        const ALTITUDE = "z";
        const TIMESTAMP = "t";
        const MAX_LONGITUDE = 180.0;
        const MIN_LONGITUDE = -180.0;
        const MAX_LATITUDE = 90.0;
        const MIN_LATITUDE = -90.0;

        /* types */
        const TYPE_CTRL = 1;
        const TYPE_HWID = 2;
        const TYPE_NAME = 3;
        const TYPE_MODEL = 4;
        const TYPE_PASS = 5;
        const TYPE_PERM = 6;
        const TYPE_PROPS = 7;
        const TYPE_STATUS = 8;
        const TYPE_PROP_LINKS = 9;
        const TYPE_DATA = 10;
        const TYPE_BEFORE = 11;
        const TYPE_AFTER = 12;
        const TYPE_OF = 13;
        const TYPE_OFFSET = 14;
        const TYPE_LIMIT = 15;
        const TYPE_AUTH = 16;
        const TYPE_ID = 17;
        const TYPE_OID = 18;
        const TYPE_AT = 19;
        const TYPE_CHMASK = 20;
        const TYPE_LOCATION = 21;
        const TYPE_LONGITUDE = 22;
        const TYPE_LATITUDE = 23;
        const TYPE_ACCURACY = 24;
        const TYPE_ALTITUDE = 25;
        const TYPE_TIMESTAMP = 26;
        const TYPE_PLUG_ID = 27;
        const TYPE_PLUG_ID_ARR = 28;
        const TYPE_PLUG_ID_CSV = 29;
        const TYPE_TIMESTAMP_ARR = 30;
        const TYPE_TIMESTAMP_CSV = 31;
        const TYPE_OID_ARR = 32;
        const TYPE_OID_CSV = 33;

        //data formats
        const FORMAT_JSON = 0;
        const FORMAT_ARRAY = 1;
        const FORMAT_OBJECT = 2;

        //http methods
        public static $HTTP_METHODS =  array ( "GET", "POST", "PUT", "DELETE", "PATCH" );
        const HTTP_GET = 0;
        const HTTP_POST = 1;
        const HTTP_PUT = 2;
        const HTTP_DELETE = 3;
        const HTTP_PATCH = 4;

        //error codes
        const ERR_INTERNAL = -1;
        const ERR_OUT_OF_MEMORY = -2;
        const ERR_INVALID_SESSION = -3;
        const ERR_QUERY_IS_NOT_OBJECT = -4;
        const ERR_QUERY_INVALID_TYPE = -5;
        const ERR_HEADER_MUST_BE_STRING = -6;
        const ERR_INVALID_PARAMETER = -7;
        const ERR_INVALID_LOGIN = -8;
        const ERR_JSON_PARSE = -9;
        const ERR_JSON_ENCODE = -10;
        const ERR_INVALID_CONTENT_LENGTH = -11;
        const ERR_HTTP = -12;
        const ERR_NETWORK = -13;

        //HTTP results
        const HTTP_RES_OK = 200;
        const HTTP_RES_CREATED = 201;
        const HTTP_RES_MULTISTATUS = 207;
        const HTTP_RES_BAD_REQUEST = 400;
        const HTTP_RES_UNAUTHORIZED = 401;
        const HTTP_RES_PAYMENT_REQUIRED = 402;
        const HTTP_RES_FORBIDDEN = 403;
        const HTTP_RES_NOT_FOUND = 404;
        const HTTP_RES_NOT_ALLOWED =405;
        const HTTP_RES_NOT_ACCEPTABLE = 406;
        const HTTP_RES_SERVER_ERROR = 500;
        const HTTP_RES_NOT_IMPLEMENTED = 501;
        const HTTP_RES_BAD_GATEWAY = 502;
        const HTTP_RES_SERVICE_UNAVAILABLE = 503;

        //class parameters
        private $curl;
        private $baseUrl;
        private $timeout;   //seconds
        private $id;
        private $auth;
        private $isMaster;
        private $httpResult;
        private $error;
        private $debugMode;
        private $response;
        private $outputFormat;

        /**
        * @param    array $options (optional) an associative array (option => value). Supported options:\n
        *           \b "outputFormat" => "json" or "array" or "object" (forces following requests output to be returned in that format)\n
        *           \b "debugMode" => TRUE or FALSE (show/hide debug information)\n
        *           \b "timeout" => int (max number of milliseconds to wait for server response)\n
        *           \b "id" => String (set the authentication ID to use)\n
        *           \b "pass" => String (set the authentication password to use)\n
        *           \b "isMaster" => TRUE or FALSE (if authentication refers to a master password)\n
        *           \b "baseUrl" => String (the base URL to be used to perform remote HTTP requests)\n
        *           \b "enableSSL" => TRUE or FALSE (whether to enable SSL or not)\n
        */
        public function __construct($options = NULL) {
            $this->debugMode = FALSE;
            $this->timeout = self::TIMEOUT;
            $this->id = NULL;
            $this->auth = NULL;
            $this->isMaster = FALSE;
            $this->baseUrl = self::BASEURL;
            $this->httpResult = 0;
            $this->error = 0;
            $this->response = '';
            $this->outputFormat = NULL;
            $this->lastNetworkError = NULL;
            if($options) $this->setOptions($options);
        }


        public function __destruct() {
            $this->sessionShutdown();
        }

        /**
        * Sets class options.
        *
        * @param    array $optionsArray An associative array: "option name"=> value.\n
        *           Supported options:\n
        *
        *           \b "outputFormat" => "json" or "array" or "object" (forces following requests ouput to be returned in that format)\n
        *           \b "debugMode"=> TRUE or FALSE (show/hide debug information)\n
        *           \b "timeout"=> int (max number of milliseconds to wait for server response)\n
        *           \b "id"=> String (set the authentication ID for following request)\n
        *           \b "pass" => String (set the authentication password to use)\n
        *           \b "isMaster"=> TRUE or FALSE (if authentication password refers to a master password)\n
        *           \b "baseUrl"=> String (the base URL to be used to perform remote HTTP requests)\n
        *           \b "enableSSL" => TRUE or FALSE (whether to enable SSL or not)\n
        *
        * @return   TRUE if all parameters have been successfully set, FALSE otherwise.
        */
        public function setOptions($optionsArray) {
            $returnValue = TRUE;

            if (isset($optionsArray['outputFormat'])) {
                $returnValue &= $this->setOutputFormat($optionsArray['outputFormat']);
            }
            if (isset($optionsArray['debugMode'])) {
                $returnValue &= $this->setDebugMode($optionsArray['debugMode']);
            }
            if (isset($optionsArray['timeout'])) {
                $returnValue &= $this->setTimeout($optionsArray['timeout']);
            }
            if (isset($optionsArray['id']) || isset($optionsArray['pass']) || isset($optionsArray['isMaster'])) {
               $returnValue &= $this->setAuth(@$optionsArray['id'], @$optionsArray['pass'], @$optionsArray['isMaster']);
            }
            if (isset($optionsArray['baseUrl'])) {
                $returnValue &= $this->setBaseUrl($optionsArray['baseUrl']);
            }
            if (isset($optionsArray['enableSSL'])) {
                $returnValue &= $this->enableSSL($optionsArray['enableSSL']);
            }

            return $returnValue;
        }

        /**
        * Sets SSL on/off.
        *
        * @param    bool $enabled SSL operating mode value.
        * @return   TRUE if success, FALSE otherwise.
        */
        public function enableSSL($enabled) {
            $enabled = (bool) $enabled;
            $this->debugFunction('* Setting SSL to: ' . $enabled . " \n");

            if ($enabled && !$this->hasSSL()) {
                $url = self::HTTPS_STR . substr($this->getBaseUrl(), strlen(self::HTTP_STR));
                return $this->setBaseUrl ($url);
            }
            if (!$enabled && $this->hasSSL()) {
                $url = self::HTTP_STR . substr($this->getBaseUrl(), strlen(self::HTTPS_STR));
                return $this->setBaseUrl ($url);
            }
            return TRUE;
        }
        /**
        * Checks whether SSL is enabled or not.
        *
        * @return   TRUE id SSL enabled, FALSE otherwise.
        */
        public function hasSSL() {
            return strpos($this->getBaseUrl(), self::HTTPS_STR) === 0;
        }

        /**
        * Sets Authentication ID value.
        *
        * @param    string $id The new ID value.
        * @return   TRUE if success, FALSE otherwise.
        */
        public function setAuthId($id) {
            if (!$id || !is_string($id)) {
                $this->error = self::ERR_INVALID_PARAMETER;
                return FALSE;
            }
            $this->id = $id;
            return TRUE;

        }
        /**
        * Sets debug mode on/off.
        *
        * @param    bool $enabled The new debug mode value.
        * @return   TRUE.
        */
        public function setDebugMode($enabled) {
            $enabled = (bool) $enabled;
            $this->debugFunction('* Setting debug mode to: ' . $enabled . " \n");
            $this->debugMode = !! $enabled ;
            return TRUE;
        }
        /**
        * Forces all requests to return server output in a specific data format.
        *
        * @param    string $format The new output format value. Can be  "array", "object" or "json".
        * @return   TRUE
        */
        public function setOutputFormat ($format) {
            $this->debugFunction('* Setting default output format value to ' . $format . " \n");
            if (strcasecmp($format, 'json') == 0) $this->outputFormat = self::FORMAT_JSON;
            else if (strcasecmp($format, 'array') == 0) $this->outputFormat = self::FORMAT_ARRAY;
            else if (strcasecmp($format, 'object') == 0) $this->outputFormat = self::FORMAT_OBJECT;
            else $this->outputFormat = NULL;
            return TRUE;
        }
        /**
        * Gets the HTTP response status code of the last request.
        *
        * @return   The last HTTP status code.
        */
        public function getLastHttpResultCode() {
            return $this->httpResult;
        }
        /**
        * Switches the HTTP response status code of the last request into a human readable message.
        *
        * @return   The last HTTP status message.
        */
        public function getLastHttpResultString() {

            switch($this->httpResult) {
                case self::HTTP_RES_OK:
                    return  "Ok";
                case self::HTTP_RES_CREATED:
                    return  "Created";
                case self::HTTP_RES_MULTISTATUS:
                    return  "Multi-Status";
                case self::HTTP_RES_BAD_REQUEST:
                    return  "Bad Request";
                case self::HTTP_RES_UNAUTHORIZED:
                    return  "Unauthorized";
                case self::HTTP_RES_PAYMENT_REQUIRED:
                    return  "Payment Required";
                case self::HTTP_RES_FORBIDDEN:
                    return  "Forbidden";
                case self::HTTP_RES_NOT_FOUND:
                    return  "Not found";
                case self::HTTP_RES_NOT_ALLOWED:
                    return  "Method Not Allowed";
                case self::HTTP_RES_NOT_ACCEPTABLE:
                    return  "Not Acceptable";
                case self::HTTP_RES_SERVER_ERROR:
                    return  "Internal Server Error";
                case self::HTTP_RES_NOT_IMPLEMENTED:
                    return  "Not Implemented";
                case self::HTTP_RES_BAD_GATEWAY:
                    return  "Bad Gateway";
                case self::HTTP_RES_SERVICE_UNAVAILABLE:
                    return  "Service Unavailable";
                default:
                    return  "Unknown Result";
            }
        }

        /**
        * Switches the last encountered error code into a human readable message.
        *
        * @return   The error message.
        */
        public function getLastErrorString() {

            switch($this->error) {
                case 0:
                    return "No error";
                case self::ERR_INTERNAL:
                    return "Internal Library Error";
                case self::ERR_OUT_OF_MEMORY:
                    return "Out of memory";
                case self::ERR_INVALID_SESSION:
                    return "Invalid session";
                case self::ERR_QUERY_IS_NOT_OBJECT:
                    return "Query is not an object";
                case self::ERR_QUERY_INVALID_TYPE:
                    return "Query contain invalid type";
                case self::ERR_HEADER_MUST_BE_STRING:
                    return "Header value must be a string";
                case self::ERR_INVALID_PARAMETER:
                    return "Invalid parameter";
                case self::ERR_INVALID_LOGIN:
                    return "Invalid login";
                case self::ERR_JSON_PARSE:
                    return "JSON parse error";
                case self::ERR_JSON_ENCODE:
                    return "JSON encode error";
                case self::ERR_HTTP:
                    return "HTTP error";
                case self::ERR_NETWORK:
                    return "Network Error: " . $this->lastNetworkError;
                default:
                    return "Unknown error";
            }
        }


        /**
        * get the last error code ancountered.
        *
        * @return   The last error code.
        */
        public function getLastErrorCode() {
            return $this->error;
        }
        /**
        * Sets the base REST server URL.
        *
        * @param    string $url the base URL value.
        * @return   TRUE if success, FALSE otherwise.
        */
        public function setBaseUrl($url) {
            $this->debugFunction("* Base Url update \n");
            if (strpos($url, self::HTTP_STR) === 0 || strpos($url, self::HTTPS_STR) === 0) {
                if (substr($url, -1) !== '/') $url .= '/';
                $this->baseUrl = $url;
                $this->debugFunction("\t New base Url value: " . $this->getBaseUrl() . " \n");
                //if a connection has already been initialized attempt to load certificate
                if ($this->hasSSL() && $this->curl) $this->setCACert();
                return TRUE;
            }
            $this->debugFunction("\t Update failed: '" . $url . "' is not a valid url! \n");
            $this->error = self::ERR_INVALID_PARAMETER;
            return FALSE;

        }

        /**
        * Gets the base REST server URL.
        *
        * @return   the base URL value.
        */
        public function getBaseUrl() {
            return $this->baseUrl;
        }

        /**
        * Sets the timeout value for REST requests.
        *
        * @param    int $seconds the maximum waiting time (in seconds) for requests.
        * @return   TRUE if success, FALSE otherwise.
        */
        public function setTimeout($seconds) {
            if ($seconds >= 0) {
                $this->timeout = $seconds > 0 ? $seconds : self::TIMEOUT;
                $this->debugFunction('* Setted timeout value to: ' . $this->timeout . " seconds\n");
                return TRUE;
            }
            $this->debugFunction("\t set timeout failed: '" . $seconds . "' is not a valid value! \n");
            $this->error = self::ERR_INVALID_PARAMETER;
            return FALSE;

        }

        /**
        * get the timeout value.
        *
        * @return   The timeout value (in seconds).
        */
        public function getTimeout() {
            return $this->timeout;
        }


        /**
        * Initializes a new session and sets the cURL handle for following requests
        *
        * @return   TRUE if success, FALSE otherwise.
        */
        private  function sessionInit() {
            if($this->curl) return TRUE;
            $this->debugFunction("* Initializing connection... \n");
            if (($this->curl = curl_init()) === FALSE) {
                return FALSE;
            }
            curl_setopt ($this->curl, CURLOPT_FOLLOWLOCATION, true); //follow redirection
            curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true); // to return the transfer as a string of the return value of curl_exec() instead of outputting it out directly.
            if ($this->hasSSL()) $this->setCACert();
            $this->debugFunction("\t Connection initialized!\n");
            return TRUE;
        }

        /**
         * Attempts to load an external certificate to verify the peer with when running over SSL
         *
         * @param string $certPath The path to the certificate file
         * @return bool
         */

        public function setCACert($certPath = NULL) {

            if (!$this->curl) {
                $this->debugFunction("* Connection not initialized... \n");
                $this->error = self::ERR_INTERNAL;
                return FALSE;
            }

            if ($certPath === NULL) $certPath = dirname(__FILE__) . "/cacert.pem";
            if (!file_exists($certPath)) {
                $this->debugFunction("* Certificate not found... \n");
                $this->error = self::ERR_INTERNAL;
                return FALSE;
            }
            //tell curl to load Cloudplugs certificate
            $this->debugFunction ("\t Loading certificate...\n");
            $SSLoptions = array(
                    CURLOPT_SSL_VERIFYHOST  => '2',             //1 to check the existence of a common name in the SSL peer certificate. 2 to check the existence of a common name and also verify that it matches the hostname provided. In production environments the value of this option should be kept at 2 (default value).
                    CURLOPT_SSL_VERIFYPEER  => TRUE,            //FALSE to stop cURL from verifying the peer's certificate. Alternate certificates to verify against can be specified with the CURLOPT_CAINFO option or a certificate directory can be specified with the CURLOPT_CAPATH option.
                    CURLOPT_CAINFO        => $certPath,         //The name of a file holding one or more certificates to verify the peer with
             );
            curl_setopt_array($this->curl, $SSLoptions);
            return TRUE;
        }


        /**
        * Cleans up resources (curl session handle)
        *
        * @return   TRUE
        */
        private function sessionShutdown() {
            if(!$this->curl) return TRUE;
            curl_close($this->curl);
            $this->debugFunction("* Connection shut down!\n");
            return TRUE;
        }

        /**
        * Set the authentication credentials to be included in the requests headers.
        *
        * @param    string $id the id value.
        * @param    string $pass password value.
        * @param    bool $is_master specifies if the requests are performed as a master user or not.
        * @return   TRUE if success, FALSE otherwise.
        */
        public function setAuth($id, $pass, $is_master = FALSE) {
            $this->debugFunction("* Setting new authentication... \n");
            if(func_num_args() < 2 || !$id  || !is_string($id) || !$pass  || !is_string($pass)) {
                $this->debugFunction("\t Set auth failed: '$id', '$pass': Invalid Parameters! \n");
                $this->error = self::ERR_INVALID_PARAMETER;
                return FALSE;
            }

            $this->setAuthId($id);
            $this->auth = $pass;
            $this->isMaster = !!$is_master;
            $this->debugFunction("\t New Authentication set to: Id='" . $this->id . "' Pass='" . $this->auth . "' \n");
            return ($this->id !== NULL && $this->auth !== NULL );
        }



        /**
        * get authentication ID value.
        *
        * @return   The ID value.
        */
        public function getAuthId() {
            return $this->id;
        }
        /**
        * get authentication password value.
        *
        * @return   The password value.
        */
        public function getAuthPass() {
            return $this->auth;
        }
        /**
        * Returns true if the requests will be performed as a master user, false otherwise
        *
        * @return   TRUE / FALSE
        */
        public function isAuthMaster() {
            return $this->isMaster;
        }
        /**
        * returns the server response output of the last HTTP REST request
        *
        * @return   The server response output
        */
        public function getServerResponse() {
            return $this->response;
        }
        /**
        * Validates a value against a specific data type.
        *
        * @param    mixed $data the value to be validated.
        * @param    string $type the data type the value should be compliant to
        * @return   TRUE if  compliant, FALSE otherwise.
        */
        private function validate($data, $type) {

            switch($type) {
                case self::TYPE_PLUG_ID:
                    //PLUG_ID
                    return is_string($data) && (strlen($data) == 28);
                case self::TYPE_PLUG_ID_ARR:
                    //PLUG_ID ARRAY
                    return TRUE;
                case self::TYPE_PLUG_ID_CSV:
                    //PLUG_ID_CSV
                    return TRUE;
                case self::TYPE_CTRL:
                    //string
                    return is_string($data);
                case self::TYPE_HWID:
                    //string or string_CSV
                    return is_string($data) && (strlen($data) >= 1);
                case self::TYPE_NAME:
                    //string
                    return is_string($data) && (strlen($data) >= 1);
                case self::TYPE_MODEL:
                    //PLUG_ID
                    return $this->validate($data, self::TYPE_PLUG_ID);
                case self::TYPE_PASS:
                    //string
                    return is_string($data);
                case self::TYPE_PERM:
                    //PERM_FILTER
                    return  TRUE;
                case self::TYPE_PROPS:
                    //complex object
                    return  TRUE;
                case self::TYPE_STATUS:
                    //STATUS: "ok" or "disabled" or "reactivate"
                    return is_string($data);
                case self::TYPE_PROP_LINKS:
                    //complex object
                    return TRUE;
                case self::TYPE_DATA:
                    //complex object
                    return TRUE;
                case self::TYPE_BEFORE:
                    //TIMESTAMP or OID or PLUG_ID
                    return is_string($data);
                    break;
                case self::TYPE_AFTER:
                    //TIMESTAMP or OID or PLUG_ID
                    return is_string($data);
                case self::TYPE_OF:
                    //PLUG_ID or "PLUG_ID_CSV" or ["PLUG_ID",...]
                    return  TRUE;
                case self::TYPE_OFFSET:
                    //positive integer including 0
                    return is_int($data) && ($data >= 0);
                case self::TYPE_LIMIT:
                    //positive integer including 0
                    return is_int($data) && ($data >= 0);
                case self::TYPE_AUTH:
                    //string
                    return is_string($data);
                case self::TYPE_ID:
                    //PLUG_ID, OID, OID_CSV or [OID,...],
                    return TRUE;
                case self::TYPE_AT:
                    //TIMESTAMP or TIMESTAMP_CSV or [TIMESTAMP,...],
                    return  TRUE;
                case self::TYPE_CHMASK:
                    //string
                    return is_string($data) && (strlen($data) >= 1);
                case self::TYPE_OID:
                    //string
                    return is_string($data) && (strlen($data) == 24);
                case self::TYPE_LOCATION:
                    return TRUE;
                case self::TYPE_LONGITUDE:
                    return TRUE;
                case self::TYPE_LATITUDE:
                    return TRUE;
                case self::TYPE_ACCURACY:
                    return TRUE;
                case self::TYPE_ALTITUDE:
                    return TRUE;
                case self::TYPE_TIMESTAMP:
                   return TRUE;
                case self::TYPE_TIMESTAMP_ARR:
                    return TRUE;
                case self::TYPE_TIMESTAMP_CSV:
                    return TRUE;
                case self::TYPE_OID_ARR:
                    return TRUE;
                case self::TYPE_OID_CSV:
                    return TRUE;

                default:
                    return  FALSE;
            }
        }

        /**
        * Debugs an information object according to the internal logic
        *
        * @param    mixed $obj debug info
        */
        private function debugFunction($obj) {

            if ($this->debugMode) {
                if (is_string($obj)) echo $obj;
            }
        }

        /**
        * Formats a generic output accordingly to the default output data format
        *
        * @param    mixed $output The output to be formatted
        * @return   mixed Formatted output
        */
        private function formatOutput($output) {
            //output is initially a json encoded string
            switch($this->outputFormat) {

                case self::FORMAT_ARRAY:
                    $val = json_decode($output, true);
                    return  $val != NULL ? $val : $output;
                case self::FORMAT_OBJECT:
                    $val = json_decode($output);
                    return  $val != NULL ? $val : $output;
                case self::FORMAT_JSON:
                case NULL:
                default:
                    return $output;
            }
        }

        /**
        * Encoding function for URL strings
        *
        * @param    string $str The string to be encoded
        * @return   string Encoded string
        */
        private function encodeParam($str) {
            $str = str_replace('?', '%3F', str_replace('#', '%23', $str));
            return $str;

        }

        /**
        *  Utility function to generate formatted output
        *
        * @param    bool $result outcome of a request
        * @param    string $outputFormat  he expected output format (will be overwritten if default outputFormat value is set)
        * @return   Formatted output on success,  false otherwise.
        */
        private function retFunction($result, $outputFormat) {
            if (!$result) return FALSE;

            if ($this->outputFormat != NULL) return $this->formatOutput($this->response);

            if ($outputFormat == 'array' ) {
                $arr = json_decode($this->response, true);
                return ($arr != NULL ? $arr : $this->response);
            }
            return $this->response;
        }

       /**
        * Performs a generic HTTP request to the server.
        * This function is invoked by any public REST request function of the library.
        * The response output and the HTTP response code are stored.
        *
        * @param    bool $authRequested specifies whether authentication info are required for that request
        * @param    int $httpMethod the index representing the  HTTP method of the request
        * @param    string $path the path to be appended to the base url
        * @param    array $headers the headers of the HTTP request
        * @param    string $query a JSON encoded string representing the parameters to be appended in the query string {param: value, ...}
        * @param    string $body the HTTP body. Usually a JSON encoded string
        * @return   TRUE on  success, false otherwise.
        */
        private function requestExec($authRequested, $httpMethod, $path, $headers, $query, $body) {

            $this->debugFunction ("\t Executing request to the server...\n");

            if(func_num_args() != 6) {
                $this->error = self::ERR_INTERNAL;
                return FALSE;
            }

            //method validation
            if (!is_int($httpMethod) || $httpMethod<0 || $httpMethod > 8) {
                $this->debugFunction( "\t Wrong HTTP Method\n");
                $this->error = self::ERR_INTERNAL;
                return FALSE;
            }
            //auth validation
            if($authRequested && $this->auth === NULL) {
                $this->debugFunction( "\t Login Info not found (Authorization Required)\n");
                $this->error = self::ERR_INVALID_LOGIN;
                return FALSE;
            }

            //start connection
            $this->sessionInit();

            //set timeout
            curl_setopt($this->curl, CURLOPT_TIMEOUT, $this->timeout);

            //create full Url
            $fullUrl = $this->baseUrl . $path;
            $queryString = (isset($query) && is_array($query))  ? http_build_query($query) : FALSE;
            //querystring
            if ($queryString) $fullUrl .= "?" . $queryString;
            $this->debugFunction( "\t URL: " . $fullUrl . "\n");
            curl_setopt ($this->curl, CURLOPT_URL, $fullUrl);


            //set http method
            curl_setopt ($this->curl, CURLOPT_CUSTOMREQUEST, self::$HTTP_METHODS[$httpMethod]);
            $this->debugFunction("\t Method: " . self::$HTTP_METHODS[$httpMethod] . "\n");

            /* HEADERS */
            $headers =  (array) $headers;
            if($this->id && $this->auth) {
                $idHeader = strpos($this->id,'@') !== false ? self::PLUG_EMAIL_HEADER . $this->id : self::PLUG_ID_HEADER . $this->id;
                $authHeader = $this->isMaster ? self::PLUG_MASTER_HEADER . $this->auth : self::PLUG_AUTH_HEADER . $this->auth;
                array_push($headers, $idHeader, $authHeader);
            }
            array_push($headers, self::CONTENT_TYPE_JSON);//append CONTENT_TYPE_JSON all'header
            curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);
            $this->debugFunction("\t Headers:  " . implode ( ", " , $headers) . "\n");

            /* BODY */
            if ($body) curl_setopt($this->curl, CURLOPT_POSTFIELDS, $body);
            $this->debugFunction( "\t Body: " . $body . "\n");

            //execute the request
            $curl_response = curl_exec($this->curl);
            $info = curl_getinfo($this->curl);
            $this->httpResult = $info['http_code'];
            if ($curl_response === false) {

                $this->error = self::ERR_NETWORK;
                $this->lastNetworkError = curl_error($this->curl)  . "(HTTP code: " . $info['http_code'] ." SSL verification: "  . $info['ssl_verify_result'] . ")";
                $this->debugFunction("\t Request failed! Message:" . $this->lastNetworkError . "\n");
                return FALSE;
            }

            // save responses
            $this->debugFunction("\t Request executed succesfully with HTTP Status: " . $this->getLastHttpResultCode() . " (" . $this->getLastHttpResultString() . ")\n");
            $this->response = $curl_response;

            return TRUE;

        }

        /**
        *  This function performs an HTTP request to the server to enroll a new production device
        *  (explicit parameters version). After request auth credentials are automatically set to enrolled device.
        *
        * @param    string $model @ref details_PLUG_ID the model of this device
        * @param    string $hwid  @ref details_HWID the serial number
        * @param    string $pass password value
        * @param    array $props (optional), to initialize the custom properties
        * @param    array|object $propLinks (optional) @ref details_PROP_LINKS to inizialize the properties links (not supported yet, present only for future use)
        * @return   Formatted server output on success, false otherwise.
        */
        public function enrollProductEx ($model , $hwid, $pass, $props = NULL, $propLinks = NULL) {
            //mandatory fields
            if(func_num_args() < 3 || isset($model) || isset($hwid)  || isset($pass)) { $this->error = self::ERR_INVALID_PARAMETER; return FALSE; }
            $data = array(self::MODEL => $model,
                          self::HWID => $hwid,
                          self::PASS => $pass);
            //optional fields
            if (isset($props)) $data[self::PROPS] = $props;
            if (isset($propLinks)) $data[self::PROP_LINKS] = $propLinks;
            //invoke compact version
            return $this->enrollProduct ($data, 'JSON');
        }
        /**
        *  This function performs an HTTP request to the server to  enroll a new production device
        *  (compact parameters version). After request auth credentials are authomatically set to enrolled device.
        *
        * @param    array|string $data array or json encoded string containing the following parameters:\n
        *               \b "model" => @ref details_PLUG_ID \n
                        \b "hwid"  => @ref details_HWID \n
                        \b "pass"  => String \n
                        \b "props" => array, optional, to initializa custom properties \n
                        \b "prop_links" => @ref details_PROP_LINKS optional, to inizialize the properties links (not supported yet, present only for future use) \n
        * @param    string $outputFormat  (optional) the expected output format (will be overwritten if default outputFormat value is set)
        * @return   Formatted server output on success, false otherwise.
        */
        public function enrollProduct ($data, $outputFormat = NULL) {
            $this->debugFunction("* Request: Enroll new production device\n");

            /* Input validation logic */
            if (is_array($data)) { //input is array

                if ($outputFormat === NULL ) $outputFormat = 'array'; //return an array

                if (!isset ($data[self::MODEL]) || !isset ($data[self::HWID]) || !isset ($data[self::PASS])) { $this->error = self::ERR_INVALID_PARAMETER; return FALSE; }

                if (!$this->validate($data[self::MODEL], self::TYPE_PLUG_ID) || !$this->validate($data[self::HWID], self::TYPE_HWID) || !$this->validate($data[self::PASS], self::TYPE_PASS)) {
                    $this->error = self::ERR_INVALID_PARAMETER;
                    $this->debugFunction("\t Invalid input\n");
                    return FALSE;
                }
                if (    isset($data[self::PROPS]) && !$this->validate($data[self::PROPS], self::TYPE_PROPS) ||
                        isset($data[self::PROP_LINKS]) && !$this->validate($data[self::PROP_LINKS], self::TYPE_PROP_LINKS)) {
                    $this->error = self::ERR_INVALID_PARAMETER;
                    $this->debugFunction("\t Invalid input\n");
                    return FALSE;
                }

                $data = json_encode($data); //encode json notation

            } else if (!is_string($data)) {
                $this->error = self::ERR_INVALID_PARAMETER;
                $this->debugFunction("\t Invalid input\n");
                return FALSE;
            }

            /* Authentication logic */
            $authRequested = FALSE;

            /* HTTP logic */
            $headers = NULL;
            $query = NULL;
            $body = $data;
            $path = self::PATH_DEVICE;
            /* EXECUTE REQUEST */
            $result = $this->requestExec($authRequested, self::HTTP_POST, $path, $headers, $query, $body);

            //IF THE REQUEST WAS SUCCESFULL SET THE AUTH INFO TO THE CONTROLLER
            if(!!$result) {
                $arr = json_decode($this->response, true);
                $this->setAuth($arr[self::ID], $data[self::PASS], FALSE);
            }

            //return
            return $this->retFunction($result, $outputFormat);
        }


        /**
        *  This function performs an HTTP request to the server to  create a new (prototype) device (X-Plug-Master header required)
        *  (explicit parameters version)
        *
        * @param    string $name
        * @param    string $pass (optional), if absent set as the X-Plug-Master of the company
        * @param    string $hwid  @ref details_HWID the serial number
        * @param    array $perm (optional) @ref details_PERM_FILTER if absent permit all
        * @param    array $props (optional)  to initialize the custom properties
        * @param    array $propLinks (optional) @ref details_PROP_LINKS to initialize properties links (not supported yet, present only for future use)
        * @return   Formatted server output on success, false otherwise.
        */
        public function enrollPrototypeEx ($name, $pass = NULL, $hwid = NULL, $perm = NULL, $props = NULL, $propLinks = NULL) {
            //mandatory fields
            if(func_num_args() < 1 || !$name ) { $this->error = self::ERR_INVALID_PARAMETER; $this->debugFunction("\t Invalid input\n"); return FALSE; }
            $data = array(self::NAME => $name);
            //optional fields
            if ($hwid !== NULL) $data[self::HWID] = $hwid;
            if ($pass !== NULL) $data[self::PASS] = $pass;
            if ($perm !== NULL) $data[self::PERM] = $perm;
            if ($props !== NULL) $data[self::PROPS] = $props;
            if ($propLinks !== NULL) $data[self::PROP_LINKS] = $propLinks;
            //invoke compact version
            return $this->enrollPrototype ($data, 'JSON');
        }
        /**
        *  This function performs an HTTP request to the server to creates a new (prototype) device (X-Plug-Master header required. Compact parameters version)
        *
        * @param    array|string $data array or json encoded string containing the following parameters:\n
        *               \b "name" => String \n
                        \b "pass"  => String  (optional) if absent set as the X-Plug-Master of the company \n
                        \b "hwid"  => @ref details_HWID (optional) if absent it will be set as a random unique string \n
                        \b "perm" => @ref details_PERM_FILTER (optional) if absent permit all  \n
                        \b "props" => array, optional, to initializa custom properties \n
                        \b "props_links" => @ref details_PROP_LINKS optional, to inizialize the properties links (not supported yet, present only for future use) \n

        * @param    string $outputFormat  (optional) the expected output format (will be overwritten if default outputFormat value is set)
        * @return   Formatted server output on success, false otherwise.
        */
        public function enrollPrototype ($data = NULL, $outputFormat = NULL) {
            $this->debugFunction("* Request: Enroll new prototype\n");

            /* Input validation logic */
            if (is_array($data)) { //input is array
                if ($outputFormat === NULL ) $outputFormat = 'array';
                if (!isset($data[self::NAME]) || !$this->validate($data[self::NAME], self::TYPE_NAME)) { $this->error = self::ERR_INVALID_PARAMETER; $this->debugFunction("\t Invalid input\n"); return FALSE; }
                if (    isset($data[self::HWID]) && !$this->validate($data[self::HWID], self::TYPE_HWID) ||
                        isset($data[self::PASS]) && !$this->validate($data[self::PASS], self::TYPE_PASS) ||
                        isset($data[self::PERM]) && !$this->validate($data[self::PERM], self::TYPE_PERM) ||
                        isset($data[self::PROPS]) && !$this->validate($data[self::PROPS], self::TYPE_PROPS) ||
                        isset($data[self::PROP_LINKS]) && !$this->validate($data[self::PROP_LINKS], self::TYPE_PROP_LINKS)
                    ) {
                    $this->error = self::ERR_INVALID_PARAMETER;
                    $this->debugFunction("\t Invalid input\n");
                    return FALSE;
                }
                $data = json_encode($data);
            } else if (!is_string($data)) {
                $this->error = self::ERR_INVALID_PARAMETER;
                $this->debugFunction("\t Invalid input\n");
                return FALSE;
            }

            /* Authentication logic */
            if (!$this->isMaster) { $this->error =self::ERR_INVALID_LOGIN; $this->debugFunction("\t Invalid login: Master authentication required\n"); return FALSE; }
            $authRequested = TRUE;

            /* HTTP logic */
            $headers = NULL;
            $query = NULL;
            $body = $data;
            $path = self::PATH_DEVICE;
            /* EXECUTE REQUEST */
            $result = $this->requestExec($authRequested, self::HTTP_POST, $path, $headers, $query, $body);

            //return
            return $this->retFunction($result, $outputFormat);
        }

        /**
        *   This function performs an HTTP request to the server to read device's information and properties
        *
        * @param    string $plugid @ref details_PLUG_ID
        * @param    string $outputFormat  (optional) the expected output format (will be overwritten if default outputFormat value is set)
        * @return   Formatted server output on success, false otherwise.
        */
        public function getDevice ($plugid = NULL, $outputFormat = NULL) {
            $this->debugFunction("* Request: Get  device\n");
            $id = isset($plugid) ? $plugid : $this->id;
            if (!$this->validate($id, self::TYPE_PLUG_ID)) {
                    $this->error = self::ERR_INVALID_PARAMETER;
                    $this->debugFunction("\t Invalid id \n");
                    return FALSE;
                }

            /* Authentication logic */
            $authRequested = TRUE;

            /* HTTP logic */
            $headers = NULL;
            $query = NULL;
            $body = NULL;
            $path = self::PATH_DEVICE . '/' . $id;
            /* EXECUTE REQUEST */
            $result = $this->requestExec($authRequested, self::HTTP_GET, $path, $headers, $query, $body);

            //return
            return $this->retFunction($result, $outputFormat);
        }


        /**
        *  This function performs an HTTP request to the server to set device location property
        *  (explicit parameters version)
        *
        * @param    string $plugid @ref details_PLUG_ID
        * @param    double $longitude
        * @param    double $latitude
        * @param    double $altitude
        * @param    double $accuracy
        * @param    string|number $timestamp @ref details_TIMESTAMP
        * @return   Formatted server output on success, false otherwise.
        */
        public function setDeviceLocationEx ($plugid, $longitude, $latitude, $altitude, $accuracy, $timestamp) {
            //mandatory fields
            if(func_num_args() < 6 || !$longitude  || !$latitude   || !$altitude  || !$accuracy  || !$timestamp  ) {
                $this->error = self::ERR_INVALID_PARAMETER;
                $this->debugFunction("\t Wrong input\n");
                return FALSE;
            }

            $data = array(self::ID => $plugid,
                          self::LONGITUDE => $longitude,
                          self::LATITUDE => $latitude,
                          self::ALTITUDE => $altitude,
                          self::ACCURACY => $accuracy,
                          self::TIMESTAMP => $timestamp);
            //optional fields

            //invoke compact version
            return $this->setDeviceLocation ($data, 'JSON');
        }

        /**
        *  This function performs an HTTP request to the server to set device location property
        *  (compact parameters version)
        *
        * @param    array|string $data array or JSON string containing the following parameters:\n
        *           \b "id" => @ref details_PLUG_ID \n
        *           \b "x" => double (longitude)\n
        *           \b "y" => double (latitude)\n
        *           \b "r" => double (accuracy)\n
        *           \b "z" => double (altitude)\n
        *           \b "t" => string|int @ref details_TIMESTAMP  \n
        * @param    string $outputFormat  (optional) the expected output format (will be overwritten if default outputFormat value is set)
        * @return   Formatted server output on success, false otherwise.
        */
        public function setDeviceLocation ($data, $outputFormat = NULL) {
            $this->debugFunction("* Request: Set device location\n");

            /* Input validation logic */
            if (is_array($data)) { //input is array
                if ($outputFormat === NULL ) $outputFormat = 'array';

                $id = isset($data[self::ID]) ? $data[self::ID] : NULL;
                unset($data[self::ID]);
                if (!$this->validate($data[self::LONGITUDE], self::TYPE_LONGITUDE)
                    || !$this->validate($data[self::LATITUDE], self::TYPE_LATITUDE)
                    || !$this->validate($data[self::ALTITUDE], self::TYPE_ALTITUDE)
                    || !$this->validate($data[self::ACCURACY], self::TYPE_ACCURACY)
                    || !$this->validate($data[self::TIMESTAMP], self::TYPE_TIMESTAMP)
                    ) { $this->error = self::ERR_INVALID_PARAMETER; $this->debugFunction("\t Invalid input 1\n"); return FALSE; }

                if ($data[self::LONGITUDE] > self::MAX_LONGITUDE || $data[self::LONGITUDE] < self::MIN_LONGITUDE  || $data[self::LATITUDE] > self::MAX_LATITUDE || $data[self::LATITUDE] < self::MIN_LATITUDE) {
                    $this->error = self::ERR_INVALID_PARAMETER;
                    $this->debugFunction("\t Invalid input \n");
                    return FALSE;
                }
                $data = json_encode($data);
            } else if (is_string($data)) { //input is expected to be a json encoded string
                    $obj = json_decode($data);
                    if($obj === null) {
                        $this->debugFunction("\t Invalid input\n");
                        $this->error = self::ERR_INVALID_PARAMETER;
                        return FALSE;
                    }
                    $id = isset($obj->{self::ID}) ? $obj->{self::ID} : NULL;
            } else {$this->error = self::ERR_INVALID_PARAMETER;
                    $this->debugFunction("\t Invalid input \n");
                    return FALSE;
                    }
            if ($id === NULL ) $id = $this->id;
            if (!$this->validate($id, self::TYPE_PLUG_ID)) {
                        $this->error = self::ERR_INVALID_PARAMETER;
                        $this->debugFunction("\t Invalid id \n");
                        return FALSE;
            }

            /* Authentication logic */
            $authRequested = TRUE;

            /* HTTP logic */
            $headers = NULL;
            $query = NULL;
            $body = $data;
            $path = self::PATH_DEVICE . '/' . $id . '/'. self::LOCATION;
            /* EXECUTE REQUEST */
            $result = $this->requestExec($authRequested, self::HTTP_PATCH, $path, $headers, $query, $body);

            //return
            return $this->retFunction($result, $outputFormat);
        }
        /**
        *  This function performs an HTTP request to the server to get device location property
        *  (explicit parameters version)
        *
        * @param    string $plugid (optional) @ref details_PLUG_ID the device id
        * @return   Formatted server output on success, false otherwise.
        */
        public function getDeviceLocationEx ($plugid = NULL) {
            //mandatory fields
            $data = array();
            //optional fields
            if ($plugid) $data[self::ID] = $plugid;
            //invoke compact version
            return $this->getDeviceLocation ($data, 'JSON');
        }
        /**
        *  This function performs an HTTP request to the server to get device location property
        *  (compact parameters version)
        *
        * @param    array|string $data array or JSON containing the following parameters:\n
        *           \b "id" => @ref details_PLUG_ID\n
        * @param    string $outputFormat  (optional) the expected output format (will be overwritten if default outputFormat value is set)
        * @return   Formatted server output on success, false otherwise.
        */
        public function getDeviceLocation ($data = NULL, $outputFormat = NULL) {
            $this->debugFunction("* Request: Get  device Location\n");

            /* Input validation logic */

            $id = NULL;

            if (is_array($data)) { //input is array
                if ($outputFormat === NULL ) $outputFormat = 'array';
                $id = isset($data[self::ID]) ? $data[self::ID] : NULL;
                 unset($data[self::ID]);
            } else if (is_string($data)) { //input is a json encoded string
                $obj = json_decode($data);
                if($obj === null) {
                    $this->error = self::ERR_INVALID_PARAMETER;
                    $this->debugFunction("\t Invalid input 2\n");
                    return FALSE;
                }
                $id = isset($obj->{self::ID}) ? $obj->{self::ID} : NULL;

            }
            if ($id === NULL) $id = $this->id;
            if (!$this->validate($id, self::TYPE_PLUG_ID)) { $this->error = self::ERR_INVALID_PARAMETER; $this->debugFunction("\t Invalid input\n"); return FALSE; }

            /* Authentication logic */
            $authRequested = TRUE;

            /* HTTP logic */
            $headers = NULL;
            $query = NULL;
            $body = NULL;
            $path = self::PATH_DEVICE . '/' . $id . '/' . self::LOCATION;
            /* EXECUTE REQUEST */
            $result = $this->requestExec($authRequested, self::HTTP_GET, $path, $headers, $query, $body);

            //return
            return $this->retFunction($result, $outputFormat);
        }
        /**
        *  This function performs an HTTP request to the server to write or delete device properties
        *  It can accept a single property ($prop = property name, $value = property value) or
        *  a set of properties ($prop = null, $value = object or array of all name=>value to write)
        *
        * @param    string $plugid (optional) @ref details_PLUG_ID
        * @param    string $prop (optional), property name
        * @param    string|object|array $value properties values
        * @param    string $outputFormat  (optional) the expected output format (will be overwritten if default outputFormat value is set)
        * @return   Formatted server output on success, false otherwise.
        */
        public function setDeviceProp ($plugid = NULL, $prop = NULL , $value , $outputFormat = NULL) {
            $this->debugFunction("* Request: Set device properties\n");
            //mandatory fields
            if(func_num_args() < 3 || !$value ) {
                $this->error = self::ERR_INVALID_PARAMETER;
                $this->debugFunction("\t missing propertiy(es) value(s) \n");
                return FALSE;
            }

            $id = isset($plugid) ? $plugid : $this->id;

            if (!$this->validate($id, self::TYPE_PLUG_ID)) {
                $this->error = self::ERR_INVALID_PARAMETER;
                $this->debugFunction("\t Invalid login value\n");
                return FALSE;
            }

            if (is_array($value)) { //input is array
                if ($outputFormat === NULL ) $outputFormat = 'array';
                $value = json_encode($value);
            }



            /* Authentication logic */
            $authRequested = TRUE;

            /* HTTP logic */
            $headers = NULL;
            $query = NULL;
            $body = $value;
            $path = isset($prop) ? self::PATH_DEVICE . '/' . $id . '/'. $this->encodeParam($prop) : self::PATH_DEVICE . '/' . $id . '/';
            /* EXECUTE REQUEST */
            $result = $this->requestExec($authRequested, self::HTTP_PATCH, $path, $headers, $query, $body);

            //return
            return $this->retFunction($result, $outputFormat);
        }
        /**
        *  This function performs an HTTP request to the server to remove a device's property
        *
        * @param    string $plugid @ref details_PLUG_ID
        * @param    string $prop property name
        * @param    string $outputFormat  (optional) the expected output format (will be overwritten if default outputFormat value is set)
        * @return   Formatted server output on success, false otherwise.
        */
        public function removeDeviceProp ($plugid, $prop, $outputFormat = NULL) {
            $this->debugFunction("* Request: Remove device properties\n");
            //mandatory fields
            if(func_num_args() < 2 || !$prop  ) {
                $this->error = self::ERR_INVALID_PARAMETER;
                $this->debugFunction("\t Invalid input\n");
                return FALSE;
            }

            $id = isset($plugid) ? $plugid : $this->id;

            if (!$this->validate($id, self::TYPE_PLUG_ID)) {
                $this->error = self::ERR_INVALID_PARAMETER;
                $this->debugFunction("Invalid login value\n");
                return FALSE;
            }

            /* Authentication logic */
            $authRequested = TRUE;

            /* HTTP logic */
            $headers = NULL;
            $query = NULL;
            $body = NULL;
            $path = self::PATH_DEVICE . '/' . $id . '/'. $this->encodeParam($prop);
            /* EXECUTE REQUEST */
            $result = $this->requestExec($authRequested, self::HTTP_DELETE, $path, $headers, $query, $body);

            //return
            return $this->retFunction($result, $outputFormat);
        }
        /**
        * This function performs an HTTP request to the server to read a device's properties (if "prop" is specified) or get all device's properties (if "prop" is not specified)
        *
        * @param    string $plugid (optional) @ref details_PLUG_ID the device id
        * @param    string $prop property name
        * @param    string $outputFormat  (optional) the expected output format (will be overwritten if default outputFormat value is set)
        * @return   Formatted server output on success, false otherwise.
        */
        public function getDeviceProp ($plugid = NULL, $prop = NULL, $outputFormat = NULL) {
            $this->debugFunction("* Request: Get device properties\n");
            $id = isset($plugid) ? $plugid : $this->id;

            if (!$this->validate($id, self::TYPE_PLUG_ID)) {
                $this->error = self::ERR_INVALID_PARAMETER;
                $this->debugFunction("\t Invalid login value\n");
                return FALSE;
            }

            /* Authentication logic */
            $authRequested = TRUE;

            /* HTTP logic */
            $headers = NULL;
            $query = NULL;
            $body = NULL;
            $path = isset($prop) ? self::PATH_DEVICE . '/' . $id . '/'. $this->encodeParam($prop) : self::PATH_DEVICE . '/' . $id . '/';
            /* EXECUTE REQUEST */
            $result = $this->requestExec($authRequested, self::HTTP_GET, $path, $headers, $query, $body);

            //return
            return $this->retFunction($result, $outputFormat);
        }

        /**
        *  This function performs an HTTP request to the server to publish data on the platform
        *
        * @param    array|string $data array or json encoded string containing the following keys: \n
                        [\n
                        [	\b "id"        => @ref details_OBJECT_ID		(optional), OID of the published data to update\n
                                \b "channel"   => @ref details_CHANNEL,	(optional), to override the channel in the url\n
                                \b "data"      => any valid JSON encodable value,\n
                                \b "at"        => @ref details_TIMESTAMP,(optional) \n
                                \b "of"        => @ref details_PLUG_ID, (optional), check if the X-Plug-Id is authorized for setting this field \n
                                \b "expire_at" => @ref details_TIMESTAMP,	// (optional), expire date of this data entry\n
                                \b "ttl"       => int	(optional), how many \b seconds this data entry will live (if "expire_at" is present, then this field is ignored)\n
                        ],\n
                        ... ]\n
                        \b Note: the outer array is optional!

        * @param    string $channel (optional) if absent "channel" parameter is required in the data
        * @param    string $outputFormat  (optional) the expected output format (will be overwritten if default outputFormat value is set)
        * @return   Formatted server output on success, false otherwise.
        */
        public function publishData ($data, $channel = NULL, $outputFormat = NULL) {
            $this->debugFunction("* Request: Publish data\n");
            //mandatory fields
            if(func_num_args() < 1 || !isset($data) ||  $data == '' ) {
                $this->error = self::ERR_INVALID_PARAMETER;
                $this->debugFunction("\t Invalid input\n");
                return FALSE;
            }
            if (is_array($data)) { //input is array

                if ($outputFormat === NULL ) $outputFormat = 'array';
                $data = json_encode($data);
            } else if (!is_string($data)) {
                $this->error = self::ERR_INVALID_PARAMETER;
                $this->debugFunction("\t Invalid input\n");
                return FALSE;
            }

            /* Authentication logic */
            $authRequested = TRUE;

            /* HTTP logic */
            $headers = NULL;
            $query = NULL;
            $body = $data;
            $path = isset($channel) ? self::PATH_DATA . '/' . $this->encodeParam($channel) : self::PATH_DATA ;
            /* EXECUTE REQUEST */
            $result = $this->requestExec($authRequested, self::HTTP_PUT, $path, $headers, $query, $body);

            //return
            return $this->retFunction($result, $outputFormat);
        }

        /**
        *  This function performs an HTTP request to the server to delete data ( \b Note: at least one of the following param is required: id, before, after or at)
        *  (explicit parameters version)
        *
        * @param    string $channelMask @ref details_CHMASK
        * @param    string|array $id  (optional) @ref details_OBJECT_ID_CSV or [@ref details_OBJECT_ID,...]
        * @param    int|string $before (optional) @ref details_TIMESTAMP or @ref details_OBJECT_ID	(OID of published data)
        * @param    int|string $after (optional) @ref details_TIMESTAMP or @ref details_OBJECT_ID	(OID of published data)
        * @param    int|string|array $at (optional) @ref details_TIMESTAMP_CSV or [@ref details_TIMESTAMP,...]
        * @param    string|array $of (optional) @ref details_PLUG_ID_CSV or [@ref details_PLUG_ID,...]
        * @return   Formatted server output on success, false otherwise.
        */
        public function removeDataEx ($channelMask, $id = NULL, $before = NULL, $after = NULL, $at = NULL, $of = NULL) {
            //mandatory fields
            if(func_num_args() < 2 || !$channelMask || ($id === NULL && $before === NULL && $after === NULL && $at === NULL)) {
                $this->error = self::ERR_INVALID_PARAMETER;
                $this->debugFunction("\t Invalid input\n");
                return FALSE;
            }

            $data[self::CHMASK] = $channelMask;
            //optional fields
            if ($id !== NULL) $data[self::ID] = $id;
            if ($before !== NULL) $data[self::BEFORE] = $before;
            if ($after !== NULL) $data[self::AFTER] = $after;
            if ($at !== NULL) $data[self::AT] = $at;
            if ($of !== NULL) $data[self::OF] = $of;

            //invoke compact version
            return $this->removeData ($data, 'JSON');
        }
        /**
        *  This function performs an HTTP request to the server to delete data ( \b Note: at least one of the following param is required: id, before, after or at)
        *  (compact parameters version)
        *
        * @param    array|string $data array or JSON string containing the following parameters:\n
        *           \b "channel_mask" =>  @ref details_CHMASK \n
        *           \b "id" => (optional) @ref details_OBJECT_ID_CSV or  [@ref details_OBJECT_ID,...] \n
        *           \b "before" => (optional) @ref details_TIMESTAMP or @ref details_OBJECT_ID	(OID of published data) \n
        *           \b "after" => (optional) @ref details_TIMESTAMP or @ref details_OBJECT_ID	(OID of published data) \n
        *           \b "at" => (optional) @ref details_TIMESTAMP_CSV or [@ref details_TIMESTAMP,...] \n
        *           \b "of" => (optional) @ref details_PLUG_ID_CSV or [@ref details_PLUG_ID,...] \n
        *
        * @param    string $outputFormat  (optional) the expected output format (will be overwritten if default outputFormat value is set)
        * @return   Formatted server output on success, false otherwise.
        */
        public function removeData ($data, $outputFormat = NULL) {

            $this->debugFunction("* Request: remove data\n");

            /* Input validation logic */
            if (is_array($data)) { //input is array
                if(!isset($data[self::CHMASK]) || (!isset($data[self::ID]) && !isset($data[self::BEFORE]) && !isset($data[self::AFTER]) && !isset($data[self::AT]))) {
                    $this->error = self::ERR_INVALID_PARAMETER;
                    $this->debugFunction("\t Invalid input\n");
                    return FALSE;
                }
                $channelMask = $data[self::CHMASK];
                unset ($data[self::CHMASK]);
                if (isset($data[self::ID]) && !$this->validate($data[self::ID], self::TYPE_OID_CSV) && !$this->validate($data[self::ID], self::TYPE_OID_ARR)){ $this->error = self::ERR_INVALID_PARAMETER; $this->debugFunction("\t Invalid input 'id'\n"); return FALSE; }
                if (isset($data[self::BEFORE]) && !$this->validate($data[self::BEFORE], self::TYPE_TIMESTAMP) && !$this->validate($data[self::BEFORE], self::TYPE_OID)){ $this->error = self::ERR_INVALID_PARAMETER; $this->debugFunction("\t Invalid input 'before' \n"); return FALSE; }
                if (isset($data[self::AFTER]) && !$this->validate($data[self::AFTER], self::TYPE_AFTER) && !$this->validate($data[self::BEFORE], self::TYPE_OID)) { $this->error = self::ERR_INVALID_PARAMETER; $this->debugFunction("\t Invalid input 'after'\n"); return FALSE; }
                if (isset($data[self::AT]) && !$this->validate($data[self::AT], self::TYPE_TIMESTAMP_CSV) && !$this->validate($data[self::AT], self::TYPE_TIMESTAMP_ARR)) { $this->error = self::ERR_INVALID_PARAMETER; $this->debugFunction("\t Invalid input 'at'\n"); return FALSE; }
                if (isset($data[self::OF]) && !$this->validate($data[self::OF], self::TYPE_PLUG_ID_CSV) && !$this->validate($data[self::OF], self::TYPE_PLUG_ID_ARR)) { $this->error = self::ERR_INVALID_PARAMETER; $this->debugFunction("\t Invalid input 'at'\n"); return FALSE; }

                if ($outputFormat === NULL ) $outputFormat = 'array';
                $data = json_encode($data);
            } else if (is_string($data)) { //input is a json encoded string
                $obj = json_decode($data);
                if($obj === NULL) {
                    $this->debugFunction("\t Invalid input\n");
                    $this->error = self::ERR_INVALID_PARAMETER;
                    return FALSE;
                }
                $channelMask = isset($obj->{self::CHMASK}) ? $obj->{self::CHMASK} : NULL;
            } else {
                $this->error = self::ERR_INVALID_PARAMETER;
                $this->debugFunction("\t Invalid input\n");
                return FALSE;
            }

            if (!$this->validate($channelMask, self::TYPE_CHMASK)){ $this->error = self::ERR_INVALID_PARAMETER; $this->debugFunction("\t Invalid input 'chmask'\n"); return FALSE; }

            /* Authentication logic */
            $authRequested = TRUE;

            /* HTTP logic */
            $headers = NULL;
            $query = NULL;
            $body = $data;
            $path = self::PATH_DATA . '/' . $this->encodeParam($channelMask);

            /* EXECUTE REQUEST */
            $result = $this->requestExec($authRequested, self::HTTP_DELETE, $path, $headers, $query, $body);

            //return
            return $this->retFunction($result, $outputFormat);
        }

        /**
        *  This function performs an HTTP request to the server to read published data
        *  (explicit parameters version)
        *
        * @param    string $channelMask (see: @ref details_CHMASK)
        * @param    int|string $before (optional) @ref details_TIMESTAMP or @ref details_OBJECT_ID	(OID of published data)
        * @param    int|string $after (optional) @ref details_TIMESTAMP or @ref details_OBJECT_ID	(OID of published data)
        * @param    int|string $at (optional) @ref details_TIMESTAMP_CSV
        * @param    string $of (optional) @ref details_PLUG_ID_CSV
        * @param    int $offset (optional) Number: positive integer (including 0)
        * @param    int $limit (optional) Number: positive integer (including 0)
        * @return   Formatted server output on success, false otherwise.
        */
        public function retrieveDataEx ($channelMask, $before = NULL, $after = NULL, $at = NULL, $of = NULL, $offset = NULL, $limit = NULL) {
            //mandatory fields
            if(func_num_args() < 1 || !$channelMask) {
                $this->error = self::ERR_INVALID_PARAMETER;
                $this->debugFunction("\t Invalid input\n");
                return FALSE;
            }
            $data[self::CHMASK] = $channelMask;

            //optional fields
            if ($before !== NULL) $data[self::BEFORE] = $before;
            if ($after !== NULL) $data[self::AFTER] = $after;
            if ($at !== NULL) $data[self::AT] = $at;
            if ($of !== NULL) $data[self::OF] = $of;
            if ($offset !== NULL) $data[self::OFFSET] = $offset;
            if ($limit !== NULL) $data[self::LIMIT] = $limit;

            //invoke compact version
            return $this->retrieveData ($data, 'JSON');
        }
        /**
        *  This function performs an HTTP request to the server to read published data
        *  (compact parameters version)
        *
        * @param    array|string $data array or JSON string containing the following parameters:\n
        *           \b "channel_mask" => @ref details_CHMASK \n
        *           \b "before" => (optional) @ref details_TIMESTAMP or @ref details_OBJECT_ID	(OID of published data) \n
        *           \b "after" => (optional) @ref details_TIMESTAMP or @ref details_OBJECT_ID	(OID of published data) \n
        *           \b "at" => (optional) @ref details_TIMESTAMP_CSV \n
        *           \b "of" => (optional) @ref details_PLUG_ID_CSV \n
        *           \b "offset" => (optional) Number: positive integer (including 0) \n
        *           \b "limit" => (optional) Number: positive integer (including 0) \n
        * @param    string $outputFormat  (optional) the expected output format (will be overwritten if default outputFormat value is set)
        * @return   Formatted server output on success, false otherwise.
        */
        public function retrieveData ($data , $outputFormat = NULL) {
            $this->debugFunction("* Request: get data\n");

            /* Input validation logic */
            if (is_array($data)) { //input is array

                if ($outputFormat === NULL ) $outputFormat = 'array';
                if (
                    (isset($data[self::BEFORE]) && !$this->validate($data[self::BEFORE], self::TYPE_TIMESTAMP) && !$this->validate($data[self::BEFORE], self::TYPE_OID)) ||
                    (isset($data[self::AFTER]) && !$this->validate($data[self::BEFORE], self::TYPE_TIMESTAMP) && !$this->validate($data[self::BEFORE], self::TYPE_OID)) ||
                    (isset($data[self::AT]) && !$this->validate($data[self::AT], self::TYPE_TIMESTAMP_CSV)) ||
                    (isset($data[self::OF]) && !$this->validate($data[self::OF], self::TYPE_PLUG_ID_CSV)) ||
                    (isset($data[self::OFFSET]) && !$this->validate($data[self::OFFSET], self::TYPE_OFFSET)) ||
                    (isset($data[self::LIMIT]) && !$this->validate($data[self::LIMIT], self::TYPE_LIMIT))
                   ) { $this->error = self::ERR_INVALID_PARAMETER; $this->debugFunction("\t Invalid input\n"); return FALSE; }

            } else if (is_string($data)) { //input is a json encoded string
                $data = json_decode($data, true); //query string must be an array
                if($data === NULL) {
                    $this->debugFunction("\t Invalid input\n");
                    $this->error = self::ERR_INVALID_PARAMETER;
                    return FALSE;
                }

            } else {$this->error = self::ERR_INVALID_PARAMETER;
                    $this->debugFunction("\t Invalid input\n");
                    return FALSE;}

            if(!isset($data[self::CHMASK]) || !$this->validate($data[self::CHMASK], self::TYPE_CHMASK)) { $this->error = self::ERR_INVALID_PARAMETER; $this->debugFunction("\t Invalid input\n"); return FALSE; }
            $channelMask = $data[self::CHMASK];
            unset ($data[self::CHMASK]);

            /* Authentication logic */
            $authRequested = TRUE;

            /* HTTP logic */
            $headers = NULL;
            $query = $data;
            $body = NULL;
            $path = self::PATH_DATA . '/' . $this->encodeParam($channelMask);

            /* EXECUTE REQUEST */
            $result = $this->requestExec($authRequested, self::HTTP_GET, $path, $headers, $query, $body);

            //return
            return $this->retFunction($result, $outputFormat);
        }
        /**
        *  This function performs an HTTP request to the server to retrieve a list of channels refer already published data matching some criteria
        *  (explicit parameters version)
        *
        * @param    string $channelMask (see: @ref details_CHMASK)
        * @param    int|string $before (optional) @ref details_TIMESTAMP or @ref details_OBJECT_ID	(OID of published data)
        * @param    int|string $after (optional) @ref details_TIMESTAMP or @ref details_OBJECT_ID (OID of published data)
        * @param    int|string $at (optional) @ref details_TIMESTAMP_CSV
        * @param    string $of (optional) @ref details_PLUG_ID_CSV
        * @param    int $offset (optional) Number: positive integer (including 0)
        * @param    int $limit (optional) Number: positive integer (including 0)
        * @return   Formatted server output on success, false otherwise.
        */
        public function listChannelsEx ($channelMask, $before = NULL, $after = NULL, $at = NULL, $of = NULL, $offset = NULL, $limit = NULL) {
            //mandatory fields
            if(func_num_args() < 1 || !$channelMask) {
                $this->error = self::ERR_INVALID_PARAMETER;
                $this->debugFunction("\t Invalid input\n");
                return FALSE;
            }
            $data[self::CHMASK] = $channelMask;
            //optional fields

            if ($before !== NULL) $data[self::BEFORE] = $before;
            if ($after !== NULL) $data[self::AFTER] = $after;
            if ($at !== NULL) $data[self::AT] = $at;
            if ($of !== NULL) $data[self::OF] = $of;
            if ($offset !== NULL) $data[self::OFFSET] = $offset;
            if ($limit !== NULL) $data[self::LIMIT] = $limit;

            //invoke compact version
            return $this->listChannels ($data, 'JSON');
        }
        /**
        *  This function performs an HTTP request to the server to retrieve a list of channels refer already published data matching some criteria
        *  (compact parameters version)
        *
        * @param    array|string $data array or JSON string  containing the following parameters:\n
        *           \b "channel_mask" => @ref details_CHMASK \n
        *           \b "before" => (optional) @ref details_TIMESTAMP or @ref details_OBJECT_ID	(OID of published data) \n
        *           \b "after" => (optional) @ref details_TIMESTAMP or @ref details_OBJECT_ID	(OID of published data) \n
        *           \b "at" => (optional) @ref details_TIMESTAMP_CSV \n
        *           \b "of" => (optional) @ref details_PLUG_ID_CSV \n
        *           \b "offset" => (optional) Number: positive integer (including 0) \n
        *           \b "limit" => (optional) Number: positive integer (including 0) \n
        * @param    string $outputFormat  (optional) the expected output format (will be overwritten if default outputFormat value is set)
        * @return   Formatted server output on success, false otherwise.
        */
        public function listChannels ($data, $outputFormat = NULL) {
            $this->debugFunction("* Request: get channel\n");

            /* Input validation logic */
            if (is_array($data)) { //input is array

                if ($outputFormat === NULL ) $outputFormat = 'array';
                if (
                    (isset($data[self::BEFORE]) && !$this->validate($data[self::BEFORE], self::TYPE_TIMESTAMP) && !$this->validate($data[self::BEFORE], self::TYPE_OID)) ||
                    (isset($data[self::AFTER]) && !$this->validate($data[self::BEFORE], self::TYPE_TIMESTAMP) && !$this->validate($data[self::BEFORE], self::TYPE_OID)) ||
                    (isset($data[self::AT]) && !$this->validate($data[self::AT], self::TYPE_TIMESTAMP_CSV)) ||
                    (isset($data[self::OF]) && !$this->validate($data[self::OF], self::TYPE_PLUG_ID_CSV)) ||
                    (isset($data[self::OFFSET]) && !$this->validate($data[self::OFFSET], self::TYPE_OFFSET)) ||
                    (isset($data[self::LIMIT]) && !$this->validate($data[self::LIMIT], self::TYPE_LIMIT))
                   ){ $this->error = self::ERR_INVALID_PARAMETER; $this->debugFunction("\t Invalid input\n"); return FALSE; }

            } else if (is_string($data)) { //input is a json encoded string
                $data = json_decode($data, true); //query string must be an array
                if($data === NULL) {
                    $this->debugFunction("\t Invalid input\n");
                    $this->error = self::ERR_INVALID_PARAMETER;
                    return FALSE;
                }
            } else {
                $this->error = self::ERR_INVALID_PARAMETER;
                $this->debugFunction("\t Invalid input\n");
                return FALSE;
            }

            if(!isset($data[self::CHMASK]) || !$this->validate($data[self::CHMASK], self::TYPE_CHMASK)) {
                $this->error = self::ERR_INVALID_PARAMETER;
                $this->debugFunction("\t Invalid input\n");
                return FALSE;
            }

            $channelMask = $data[self::CHMASK];
            unset ($data[self::CHMASK]);

            /* Authentication logic */
            $authRequested = TRUE;

            /* HTTP logic */
            $headers = NULL;
            $query = $data;
            $body = NULL;
            $path = self::PATH_CHANNEL . '/' . $this->encodeParam($channelMask);

            /* EXECUTE REQUEST */
            $result = $this->requestExec($authRequested, self::HTTP_GET, $path, $headers, $query, $body);

            //return
            return $this->retFunction($result, $outputFormat);
        }
        /**
        *  This function performs an HTTP request to the server to remove a device (any: development, product or controller):
        *
        * @param    string|array $data @ref details_PLUG_ID or [@ref details_PLUG_ID,...] or @ref details_PLUG_ID_CSV : The ids of the devices to be removed
        * @param    string $outputFormat  (optional) the expected output format (will be overwritten if default outputFormat value is set)
        * @return   Formatted server output on success, false otherwise.
        */
        public function unenroll ($data = NULL, $outputFormat = NULL) {
            $this->debugFunction("* Request: Unenroll\n");

            /* Input validation logic */
            if (is_array($data)) { //input is array
                if (!$this->validate($data, self::TYPE_PLUG_ID_ARR) //if is an array in can only be a plug id array
                    ) { $this->error = self::ERR_INVALID_PARAMETER; $this->debugFunction("\t Invalid input\n");  return FALSE; }
                $data = json_encode($data);
            } else if (is_string($data)) {
                //input can consist of different data (plugin_id, plug_id_csv): do nothing
            } else {
                //no plug id specified. If auth id is a plugid then use it!
                if ($this->validate($this->id, self::TYPE_PLUG_ID))
                    $data = json_encode($this->id);
            }

            if ($data === NULL) {
                $this->error = self::ERR_INVALID_PARAMETER;
                $this->debugFunction("\t Invalid input\n");
                return FALSE;
            }

            /* Authentication logic */
            $authRequested = TRUE;

            /* HTTP logic */
            $headers = NULL;
            $query = NULL;
            $body = $data;

            $path = self::PATH_DEVICE;
            /* EXECUTE REQUEST */
            $result = $this->requestExec($authRequested, self::HTTP_DELETE, $path, $headers, $query, $body);

            //return
            return $this->retFunction($result, $outputFormat);
        }
        /**
        *  This function performs an HTTP request to the server to modify a device's information or properties
        *  (explicit parameters version)
        *
        * @param    string $id (see: @ref details_PLUG_ID)
        * @param    array $perm (optional), it contains just the sharing filters to modify (see: @ref details_PERM_FILTER)
        * @param    string $name (optional) the device name
        * @param    string $status (optional) (see: @ref details_STATUS)
        * @param    array $props (optional), it contains just the properties to modify
        * @param    array $propLinks (optional), it contains a link reference to the published data  (not supported yet, present only for future use. See: @ref details_PROP_LINKS)
        * @return   Formatted server output on success, false otherwise.
        */
        public function setDeviceEx ($id = NULL, $perm = NULL, $name = NULL, $status = NULL, $props = NULL, $propLinks = NULL) {
            //mandatory fields

            //optional fields
            $data = NULL;
            if ($id) $data[self::ID] = $id;
            if ($perm) $data[self::PERM] = $perm;
            if ($name) $data[self::NAME] = $name;
            if ($status) $data[self::STATUS] = $status;
            if ($props) $data[self::PROPS] = $props;
            if ($propLinks) $data[self::PROP_LINKS] = $propLinks;

            //invoke compact version
            return $this->setDevice ($data, 'JSON');
        }
        /**
        *  This function performs an HTTP request to the server to modify a device's information or properties
        *  (compact parameters version)
        *
        * @param    array|string $data array or JSON string containing the following parameters:\n
        *           \b "id" => @ref details_PLUG_ID (optional) \n
        *           \b "perm" => @ref details_PERM_FILTER (optional) it contains just the sharing filters to modify \n
        *           \b "name" => String (optional) the device name \n
        *           \b "status" => @ref details_STATUS (optional) \n
        *           \b "props" => array (optional) it contains just the properties to modify \n
        *           \b "prop_links" =>(optional), it contains a link reference to the published data  (not supported yet, present only for future use. See: @ref details_PROP_LINKS) \n
        *
        * @param    string $outputFormat  (optional) the expected output format (will be overwritten if default outputFormat value is set)
        * @return   Formatted server output on success, false otherwise.
        */
        public function setDevice ($data = NULL, $outputFormat = NULL) {
            $this->debugFunction("* Request: Set Device\n");


            /* Input validation logic */
            if (is_array($data)) { //input is array
                if ($outputFormat === NULL ) $outputFormat = 'array';
                $id = isset($data[self::ID]) ? $data[self::ID] : $this->id;
                unset($data[self::ID]);
                if (
                    (isset($data[self::PERM]) && !$this->validate($data[self::PERM], self::TYPE_PERM)) ||
                    (isset($data[self::NAME]) && !$this->validate($data[self::NAME], self::TYPE_NAME)) ||
                    (isset($data[self::STATUS]) && !$this->validate($data[self::STATUS], self::TYPE_STATUS)) ||
                    (isset($data[self::PROPS]) && !$this->validate($data[self::PROPS], self::TYPE_PROPS)) ||
                    (isset($data[self::PROP_LINKS]) && !$this->validate($data[self::PROP_LINKS], self::TYPE_PROP_LINKS))
                   ){ $this->error = self::ERR_INVALID_PARAMETER; return FALSE; }
                $data = json_encode($data);
            } else if (is_string($data)) { //input is a json encoded string
                $obj = json_decode($data);
                if($obj === NULL) {
                    $this->debugFunction("\t Invalid input\n");
                    $this->error = self::ERR_INVALID_PARAMETER;
                    return FALSE;
                }
                $id = isset($obj->{self::ID}) ? $obj->{self::ID} : NULL;
            }

            if (!isset($id)) $id = $this->id;
            if (!$this->validate($id, self::TYPE_PLUG_ID)) {
                    $this->error = self::ERR_INVALID_PARAMETER;
                    $this->debugFunction("\t Invalid id \n");
                    return FALSE;
            }

            /* Authentication logic */
            $authRequested = TRUE;

            /* HTTP logic */
            $headers = NULL;
            $query = NULL;
            $body = $data;
            $path = self::PATH_DEVICE . '/' . $id;

            /* EXECUTE REQUEST */
            $result = $this->requestExec($authRequested, self::HTTP_PATCH, $path, $headers, $query, $body);

            //return
            return $this->retFunction($result, $outputFormat);
        }

       /**
        *  This function performs an HTTP request to the server to enroll a controller device. This function can be invoked only if no ID set or if id is an Xplug email.
        *  After request, authentication credentials are automatically set to controller
        *  (explicit parameters version)
        *
        * @param    string $model  @ref details_PLUG_ID model id of the device to control
        * @param    string $ctrl @ref details_HWID serial number  of the device to control
        * @param    string $pass password value
        * @param    string $hwid @ref details_HWID unique string to identify this controller device
        * @param    string $name the name of this device
        * @return   Formatted server output on success, FALSE otherwise.
        */
        public function enrollControllerEx ($model, $ctrl, $pass, $hwid, $name) {
            //mandatory fields
            if(func_num_args() < 5 || !$model || !$ctrl  || !$pass || !$hwid || !$name) { $this->error = self::ERR_INVALID_PARAMETER; return FALSE; }
            $data = array(self::MODEL => $model,
                          self::CTRL => $ctrl,
                          self::PASS => $pass,
                          self::HWID => $hwid,
                          self::NAME => $name);

            //invoke compact version
            return $this->enrollController ($data, 'JSON');
        }
        /**
        *  This function performs an HTTP request to the server to enroll a controller device. This function can be invoked only if no ID set or if id is an Xplug email.
        *  After request, authentication credentials are automatically set to controller
        *  (compact parameters version)
        *
        * @param    array|string $data array or JSON string containing the following parameters:\n
        *           \b "model" =>  @ref details_PLUG_ID model id of the device to control\n
        *           \b "ctrl" =>  @ref details_HWID serial number  of the device to control \n
        *           \b "pass" =>  password value \n
        *           \b "hwid" => @ref details_HWID unique string to identify this controller device\n
        *           \b "name" => the name of this device \n
        * @param    string $outputFormat  (optional) the expected output format (will be overwritten if default outputFormat value is set)
        * @return   Formatted server output on success, FALSE otherwise.
        */
        public function enrollController ($data = NULL, $outputFormat = NULL) {
            $this->debugFunction("* Request: Enroll new production device\n");

            /* Input validation logic */

            //this function can be invoked only if no ID is set or if id is an Xplug email
            if ($this->id !== NULL &&  strpos($this->id,'@') === FALSE) {
                $this->error = self::ERR_INVALID_PARAMETER;
                $this->debugFunction("\t Invalid login: this function can be invoked only if no ath are set or if id is an Xplug email\n");
                return FALSE;
            }

            if (is_array($data)) { //input is array
                if ($outputFormat === NULL ) $outputFormat = 'array';
                if (!isset ($data[self::MODEL]) || !isset ($data[self::CTRL]) || !isset ($data[self::PASS]) || !isset ($data[self::HWID]) || !isset ($data[self::NAME]) ) { $this->error = self::ERR_INVALID_PARAMETER; return FALSE; }
                if (!$this->validate($data[self::MODEL], self::TYPE_PLUG_ID) ||
                    !$this->validate($data[self::CTRL], self::TYPE_CTRL) ||
                    !$this->validate($data[self::PASS], self::TYPE_PASS) ||
                    !$this->validate($data[self::HWID], self::TYPE_HWID) ||
                    !$this->validate($data[self::NAME], self::TYPE_NAME)
                    ) {
                        $this->error = self::ERR_INVALID_PARAMETER;
                        $this->debugFunction("\t Invalid input\n");
                        return FALSE;
                    }
                $data = json_encode($data);
            } else if (!is_string($data)) {
                $this->error = self::ERR_INVALID_PARAMETER;
                $this->debugFunction("\t Invalid input\n");
                return FALSE;
            }

            /* Authentication logic */
            $authRequested = FALSE;

            /* HTTP logic */
            $headers = NULL;
            $query = NULL;
            $body = $data;
            $path = self::PATH_DEVICE;
            /* EXECUTE REQUEST */
            $result = $this->requestExec($authRequested, self::HTTP_PUT, $path, $headers, $query, $body);


            //IF THE REQUEST WAS SUCCESFULL AND NO AUTH IS SET, SET THE AUTH INFO TO THE CONTROLLER
            if ($this->id === NULL && !!$result) {
                $arr = json_decode($this->response, true);
                $this->setAuth($arr[self::ID], $data[self::PASS], FALSE);
            }

            //return
            return $this->retFunction($result, $outputFormat);
        }
        /**
        *  This function performs an HTTP request to the server to unenroll a (controller) device
        *
        * @param    string $plugid (optional) @ref details_PLUG_ID the controller PLUG_ID
        * @param    string|array $plugidControlled  (optional) @ref details_PLUG_ID or [ @ref details_PLUG_ID, ... ] the device(s) to disassociate (default: all associated devices).
        * @param    string $outputFormat  (optional) the expected output format (will be overwritten if default outputFormat value is set)
        * @return   Formatted server output on success, FALSE otherwise.
        */
        public function uncontrolDevice ($plugid = NULL, $plugidControlled = NULL, $outputFormat = NULL){
            $this->debugFunction("* Request: uncontrol device\n");

            $plugid = isset($plugid) ? $plugid : $this->id;
            if (!$this->validate($plugid, self::TYPE_PLUG_ID)) { $this->error = self::ERR_INVALID_PARAMETER; return FALSE; }

            /* Input validation logic */
            if (is_array($plugidControlled)) { //input is array
                if (!$this->validate($plugidControlled, self::TYPE_PLUG_ID_ARR)
                    ) { $this->error = self::ERR_INVALID_PARAMETER; $this->debugFunction("\t Invalid input\n");  return FALSE; }
                $plugidControlled = json_encode($plugidControlled);
            }

            /* Authentication logic */
            $authRequested = TRUE;

            /* HTTP logic */
            $headers = NULL;
            $query = NULL;
            $body = $plugidControlled;
            $path = self::PATH_DEVICE . '/' . $plugid;

            /* EXECUTE REQUEST */
            $result = $this->requestExec($authRequested, self::HTTP_DELETE, $path, $headers, $query, $body);

            //return
            return $this->retFunction($result, $outputFormat);
        }

        /**
        *  This function performs an HTTP request to the server to obtain the privileges to control a device (read or publish data for that device)
        *  (explicit parameters version)
        *
        * @param    string $model  @ref details_PLUG_ID model id of the device to control
        * @param    string $ctrl @ref details_HWID serial number  of the device to control
        * @param    string $pass password value
        * @param    string $hwid (optional) @ref details_HWID unique string to identify this controller device
        * @param    string $name (optional) the name of this device
        * @return   Formatted server output on success, false otherwise.
        */
        public function controlDeviceEx ($model, $ctrl, $pass, $hwid = NULL, $name = NULL) {
            //mandatory fields
            if(func_num_args() < 3 || !$model || !$ctrl  || !$pass) { $this->error = self::ERR_INVALID_PARAMETER; return FALSE; }
            $data = array(self::MODEL => $model, self::CTRL => $ctrl, self::PASS => $pass);
            //optional fields
            if ($hwid) $data[self::HWID] = $hwid;
            if ($name) $data[self::NAME] = $name;
            //invoke compact version
            return $this->controlDevice ($data, 'JSON');
        }
        /**
        *  This function performs an HTTP request to the server to obtain the privileges to control a device (read or publish data for that device)
        *  (compact parameters version)
        *
        * @param    array|string $data array or JSON string containing the following parameters:\n
        *           \b "model" =>  @ref details_PLUG_ID model id of the device to control\n
        *           \b "ctrl" =>  @ref details_HWID serial number  of the device to control \n
        *           \b "pass" =>  password value \n
        *           \b "hwid" => (optional) @ref details_HWID unique string to identify this controller device\n
        *           \b "name" => (optional) the name of this device \n
        * @param    string $outputFormat  (optional) the expected output format (will be overwritten if default outputFormat value is set)
        * @return   Formatted server output on success, false otherwise.
        */
        public function controlDevice ($data, $outputFormat = NULL) {
            $this->debugFunction( "* Request: Enroll new production device\n");

            /* Input validation logic */
            if (is_array($data)) { //input is array
                if ($outputFormat === NULL ) $outputFormat = 'array';
                if (!isset ($data[self::MODEL]) || !isset ($data[self::CTRL]) || !isset ($data[self::PASS])) { $this->error = self::ERR_INVALID_PARAMETER; return FALSE; }
                if (!$this->validate($data[self::MODEL], self::TYPE_MODEL) || !$this->validate($data[self::CTRL], self::TYPE_CTRL) || !$this->validate($data[self::PASS], self::TYPE_PASS)) { $this->error = self::ERR_INVALID_PARAMETER; return FALSE; }
                if (    (isset($data[self::HWID]) && !$this->validate($data[self::HWID], self::TYPE_HWID)) ||
                        (isset($data[self::NAME]) && !$this->validate($data[self::NAME], self::TYPE_NAME))
                   ){ $this->error = self::ERR_INVALID_PARAMETER; return FALSE; }
                $data = json_encode($data);
            } else if (!is_string($data)) {
                $this->error = self::ERR_INVALID_PARAMETER;
                $this->debugFunction("\t Invalid input\n");
                return FALSE;
            }

            /* Authentication logic */
            $authRequested = TRUE;

            /* HTTP logic */
            $headers = NULL;
            $query = NULL;
            $body = $data;
            $path = self::PATH_DEVICE;
            /* EXECUTE REQUEST */
            $result = $this->requestExec($authRequested, self::HTTP_PUT, $path, $headers, $query, $body);

            //IF THE REQUEST WAS SUCCESFULL AND NO PLUGID IS SET, THEN SET THE NEW AUTH CREDENTIALS
            if(!!$result) {
                $arr = json_decode($this->response, true);
                $this->setAuth($arr[self::ID], $data[self::PASS], FALSE);
            }

            //return
            return $this->retFunction($result, $outputFormat);
        }

    }
