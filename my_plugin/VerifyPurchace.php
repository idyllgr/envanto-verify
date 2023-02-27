class VerifyPurchace {
  private $purchase_api_url = 'https://example.com/verify-purchase.php';

  public function verify_purchase($purchase_code) {
    $response = wp_remote_get($this->purchase_api_url . '?purchase_code=' . $purchase_code);
    if (is_wp_error($response)) {
      return array('error' => $response->get_error_message());
    }

    $data = json_decode($response['body'], true);
    if (isset($data['error'])) {
      return array('error' => $data['error']);
    }

    return $data;
  }

  private function get_access_token() {
    return getenv('BZN_FILEPOND_API_ACCESS_TOKEN');
  }

  public function is_valid_purchase($purchase_code) {
    $access_token = $this->get_access_token();
    if (empty($access_token)) {
      return false;
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

    return $http_status === 200;
  }
}
