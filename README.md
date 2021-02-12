# curlCaller


# settings

    CurlCaller::setSettings([
        'url' => 'url',
        // paramJson' => true,
        'responseJsonToArray' => true,
        'userAgent' => 'user-agent', 
        'basicAuth' => ['user' => 'user', 'password' => 'pass'],
        'headers' => [
            // 'customheader: info', 'Authorization: Bearer 12345678'
        ]
    ]);

# parms
    $response = CurlCaller::get('/endpoint', ['page' => 1]);

    $response = CurlCaller::post('/endpoint', ['customer' => 1234, 'sataus' => 'active']);

    $response = CurlCaller::patch('/endpoint/1234', ['sataus' => 'inactive']);

    $response = CurlCaller::put('/endpoint/1234', ['customer' => '1234', 'sataus' => 'active']);

    $response = CurlCaller::delete('/endpoint/1234', ['force' => 1]);

# body content
    $response = CurlCaller::post('url/endpoint', '{"customer" : 1234}');

# Raw calls
    $response = CurlCaller::call('/endpoint', 'POST', ['customer' => 1234, 'sataus' => 'active'])

    // var_export($response);
    // Array(
    //    'url' => 'url/endpoint',
    //    'httpCode' => 200,
    //    'responseType' => 'success',
    //    'response' => Array()
    // );

# curl results
    echo CurlCaller::getLastCurlError();

    echo CurlCaller::getLastCurlInfo();
