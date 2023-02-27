<?php
class My_Plugin {
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

    return $data;
  }
}
