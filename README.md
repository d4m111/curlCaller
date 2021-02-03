# curlCaller

# settings
CurlCaller::$settings['queryTimeout'] = 20;

CurlCaller::$settings['basicAuth'] = ['user','pass'];

# parms
$response = CurlCaller::get('url/endpoint',["page" => 1]);

$response = CurlCaller::post('url/endpoint',["customer" => 1234, "sataus" => 'active']);

$response = CurlCaller::patch('url/endpoint/1234',["sataus" => 'inactive']);

$response = CurlCaller::put('url/endpoint/1234',["customer" => '1234', "sataus" => 'active']);

$response = CurlCaller::delete('url/endpoint/1234',["force" => 1]);

# body content
$response = CurlCaller::post('url/endpoint','{"customer":1234}');

# curl results
echo CurlCaller::$curlError;

echo CurlCaller::$curlInfo['http_code'];
