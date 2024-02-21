<?php
/*
 * (c) Copyright 1999-2010 PaperCut Software International Pty Ltd.
 *
 * A basic PHP example demonstrating how to script Application Server commands
 * using the XML-RPC Web services API.
 *
 * For a full list of available APIs and the argument types they expect please
 * see the JavaDoc documentation at server/examples/java/doc/index.html
 */


/*
 * If XML-RPC is compiled into PHP the following include line should be removed.
 * Otherwise, download the includable version from
 * http://phpxmlrpc.sourceforge.net/ and make sure the include line below points
 * to the location of xmlrpc.inc.
 */
include('xmlrpc.inc');

$server_name = 'localhost';
$port = 9191;
$auth_token = 'password';

$client = new xmlrpc_client('/rpc/api/xmlrpc', $server_name, $port);
$client->return_type = 'phpvals';

// EXAMPLE: printing out a user's balance
$user_name = 'testuser';
$balance = call_api('getUserProperty', array(
    new xmlrpcval($user_name, 'string'),
    new xmlrpcval('balance', 'string')
));
echo $balance;

/*
 * Helper function to wrap XML-RPC calls.
 * $data should be an array of the API's parameters (as _xmlrpcval_s).
 */
function call_api($name, $data) {
    global $auth_token;
    global $client;

    array_unshift($data, new xmlrpcval($auth_token, 'string'));
    $message = new xmlrpcmsg('api.'.$name, $data);
    $response = $client->send($message);

    if ($response->faultCode()) {
        return 'ERROR: '.$response->faultString();
    } else {
        return $response->value();
    }
}
?>
