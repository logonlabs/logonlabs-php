<?php

namespace LogonLabs\IdPx\API;
use \Exception as Exception;
/*
 *  Logon Labs API Client
 */

class LogonClient {

    private $app_id;

    private $request;

    private $idpx_request;

    private $api_path = 'https://api.logonlabs.com/';
    private $app_secret;

    /*
     *  Configure API client with required settings
     *  $settins array will required the following keys
     *
     *  - api_key
     */

    public function __construct($settings) {
        if (!isset($settings['app_id'])) {
            throw new Exception("'app_id' must be provided");
        }
        $this->app_id = $settings['app_id'];

        if ($settings['api_path']) {
            $this->api_path = $settings['api_path'];
        }

        if ($settings['app_secret']) {
            $this->app_secret = $settings['app_secret'];
        }
    }

    private function connection() {
        if (!$this->request) {
            $this->request = new Connection($this->api_path, $this->app_secret);
        }
        return $this->request;
    }

    private function idpx() {
        if (!$this->idpx_request) {
            $connection = $this->connection();
            $this->idpx_request = new IDPX($connection);
        }
        return $this->idpx_request;
    }

    public function startLogin($identity_provider, $email_address = false, $client_data = false, $redirect = true) {
        $data = array(
            'app_id' => $this->app_id,
            'identity_provider' => $identity_provider
        );

        if (!empty($email_address)) {
            $data['email_address'] = $email_address;
        }

        if (!empty($client_data)) {
            $client_data = json_encode($client_data);
            $data['client_data'] = $client_data;
        }

        $response = $this->idpx()->startLogin($data);


        if (!isset($response['body']) || !isset($response['body']['token'])) {
            return $response;
        }

        return $this->idpx()->redirectLogin($response['body']['token'], $redirect);
    }

    public function validateLogin($token) {
        $data = array(
            'app_id' => $this->app_id,
            'token' => $token
        );
        return $this->idpx()->validateLogin($data);
    }

    public function getProviders($email_address = false) {
        $data = array(
            'app_id' => $this->app_id
        );
        if (!empty($email_address)) {
            $data['email_address'] = $email_address;
        }

        return $this->idpx()->getProviders($data);
    }

    public function ping() {
        return $this->idpx()->ping($this->app_id);
    }

    public static function encrypt($client_encryption_key, $value) {
        return Util::encrypt($client_encryption_key, $value);
    }
}