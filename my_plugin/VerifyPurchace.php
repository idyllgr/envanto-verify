class VerifyPurchace {
  private $api_endpoint = 'https://example.com/verify-purchase.php';

  public function verify_purchase($purchase_code) {
    $response = wp_remote_get($this->api_endpoint . '?purchase_code=' . urlencode($purchase_code));
    if (is_wp_error($response)) {
      return new WP_Error('http_error', 'HTTP error: ' . $response->get_error_message());
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (isset($data['error'])) {
      return new WP_Error('verification_error', $data['error']);
    }

    $license_types = array(
      'regular' => 'Regular License',
      'extended' => 'Extended License',
      'elements' => 'Envato Elements',
    );
    $license_type = isset($license_types[$data['license_type']]) ? $license_types[$data['license_type']] : $data['license_type'];

    $supported_until = $data['supported_until'] !== 'Not supported' ? date('F j, Y', strtotime($data['supported_until'])) : 'Not supported';

    $output = array(
      'item_name' => $data['item_name'],
      'item_url' => $data['item_url'],
      'purchase_date' => $data['purchase_date'],
      'license_type' => $license_type,
      'supported_until' => $supported_until,
      'buyer_username' => $data['buyer_username'],
      'buyer_email' => $data['buyer_email'],
      'buyer_country' => $data['buyer_country'],
      'buyer_city' => $data['buyer_city'],
      'buyer_state' => $data['buyer_state'],
    );

    return $output;
  }
}
