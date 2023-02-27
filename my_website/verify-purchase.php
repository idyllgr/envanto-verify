<?php
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$access_token = getenv('BZN_FILEPOND_API_ACCESS_TOKEN');
$purchase_code = $_GET['purchase_code'] ?? '';
if (empty($purchase_code)) {
  http_response_code(400);
  echo json_encode(array('error' => 'Purchase code is missing'));
  exit;
}

if (empty($access_token)) {
  http_response_code(500);
  echo json_encode(array('error' => 'Access token is missing'));
  exit;
}

$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => "https://api.envato.com/v3/market/author/sale?code=" . urlencode($purchase_code),
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_HTTPHEADER => array(
    "Authorization: Bearer $access_token",
    "User-Agent: bzn-filepond-api/1.0",
  ),
));
$response = curl_exec($curl);
$http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

if ($http_status !== 200) {
  http_response_code($http_status);
  echo $response;
  exit;
}

$data = json_decode($response, true);
if (isset($data['error'])) {
  http_response_code(500);
  echo json_encode(array('error' => $data['error']));
  exit;
}

$buyer = $data['buyer'];
$item = $data['item'];
$sale_date = $data['sold_at'];
$license = $data['license'];
$support = $data['supported_until'] ?? '';

$output = array(
  'item_name' => $item['name'],
  'purchase_date' => date('F j, Y', strtotime($sale_date)),
  'license_type' => ucfirst($license),
  'supported_until' => $support ? date('F j, Y', strtotime($support)) : 'Not supported',
  'buyer_username' => $buyer['username'],
  'buyer_email' => $buyer['email'],
  'buyer_country' => $buyer['country'],
  'buyer_city' => $buyer['city'],
  'buyer_state' => $buyer['state'],
);

echo json_encode($output);
