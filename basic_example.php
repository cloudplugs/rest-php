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
</license> */

$AUTH_PLUGID =  "dev-xxxxxxxxxxxxxxxxxxx"; /**< The device plug ID or your CloudPlugs account id if AUTH_MASTER is TRUE */
$AUTH_PASS = "your-password"; /**< The device connection password or your CloudPlugs account password if AUTH_MASTER is TRUE */
$AUTH_MASTER = TRUE;

include_once 'RestClient.class.php';

use cloudplugs\RestClient;

$client = new RestClient();
$client->setAuth ($AUTH_PLUGID, $AUTH_PASS, $AUTH_MASTER);

$channel = "temperature";
$value = rand ( 0 , 100 );

$data = array("data" => $value);
$res =  $client->publishData($data, $channel);

// show server output
echo "Http Response: " . $client->getLastHttpResultCode() . ", Published OID:"  . var_export($res, true) . "\n";
