<?php 
$sigbro_ardor_url = "https://random.api.nxter.org/tstardor";
$sigbro_signer_url = "https://sigbro-signer.api.nxter.org/api/v1/sign/";


// send transaction to the sign service
function sigbro_send_tx_to_sign($unsigned_tx, $token) {
    global $sigbro_signer_url;

    $params = array(
        'payload' => $unsigned_tx,
        'sigbro_token' => $token,
        'sigbro_network' => 'testnet',
    );
    $res = sigbro_send_post_json($sigbro_signer_url, $params, 5, $token);

    if ( isset($res["status"]) && $res["status"] == "ok" ) {
        return true;
    } 
    var_dump($res);

    return false;
}

// set up the property for the AccountRS
function sigbro_set_account_property($account, $property, $setter_publickey, $value, $token) {
    global $sigbro_ardor_url;
    $params = array(
        'requestType' => 'setAccountProperty',
        'chain' => 2,
        'recipient' => $account,
        'property' => $property, 
        'value' => $value,
        'publicKey' => $setter_publickey,
        'feeNQT' => -1,
        'deadline' => 15,
        'broadcast' => false,
    );

    $res = sigbro_send_post($sigbro_ardor_url, $params, 3);

    if ( isset($res["transactionJSON"]) ) {
        // we might try to setup property we need
        $sign_res = sigbro_send_tx_to_sign($res, $token);
        return $sign_res;
    }

    // error was happened
    return false;
}

// validate Account Property -> return True if valid
function sigbro_validate_account_property($account, $property, $setter, $value) {
    global $sigbro_ardor_url;

    $params = array(
        'requestType' => 'getAccountProperties',
        'recipient' => $account,
        'property' => $property, 
        'setter' => $setter,
    );

    $res = sigbro_send_post($sigbro_ardor_url, $params, 3);

    if ( $res["properties"] == $property && $res["value"] == $value ) {
        return true;
    }
    return false;
}

// send POST request
function sigbro_send_post($url, $params, $timeout = 3) {
    $res = @file_get_contents($url, false, stream_context_create(array(
        'http' => array(
            'method' => 'POST',
            'header' => 'Content-type: application/x-www-form-urlencoded',
            'content' => http_build_query($params),
            'timeout' => $timeout,
        ),
    )));
    return json_decode( $res, true );
}

// send POST JSON request 
function sigbro_send_post_json($url, $params, $timeout=3, $token='anonymous') {
	$options = array(
		'http' => array(
			'method'  => 'POST',
			'content' => json_encode( $params ),
			'header'=>  "Content-Type: application/json\r\n" .
									"Accept: application/json\r\n" .
									"X-Sigbro-Token: " . $token . "\r\n" .
									"User-Agent: sigbro-auth2\r\n",

			'timeout' => $timeout
			)
	);
	
	$context  = stream_context_create( $options );
	$result = file_get_contents( $url, false, $context );
	$response = json_decode( $result, true );

	return $response;
}

// generate unique UUID
function sigbro_generate_uuid()
{
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

?>