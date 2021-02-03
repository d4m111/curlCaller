# curlCaller

# settings
CurlCaller::$settings['queryTimeout'] = 20;

CurlCaller::$settings['basicAuth'] = ['user','pass'];

# parms
$response = CurlCaller::get('url.com/api',["page" => 1]);

$response = CurlCaller::post('url.com/api',["customer" => 1234, "sataus" => 'active']);

$response = CurlCaller::patch('url.com/api/1234',["sataus" => 'inactive']);

$response = CurlCaller::put('url.com/api/1234',["customer" => '1234', "sataus" => 'active']);

$response = CurlCaller::delete('url.com/api/1234',["force" => 1]);

# body content
$response = CurlCaller::post('url.com/api','{"customer":1234}');

# curl results
echo CurlCaller::$curlError;

echo CurlCaller::$curlInfo['http_code'];
