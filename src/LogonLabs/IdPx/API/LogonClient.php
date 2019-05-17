<?php

namespace LogonLabs\IdPx\API;

use \Exception as Exception;
use LogonLabs\EventValidationTypes as EventValidationTypes;
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

    const LocalLogin = 'LocalLogin';
    const LocalLogout = 'LocalLogout';
    public static $eventType = array(self::LocalLogin, self::LocalLogout);

    const token = 'token';


    public function __construct($settings) {
        if (!isset($settings['app_id'])) {
            throw new Exception("'app_id' must be provided");
        }
        $this->app_id = $settings['app_id'];

        if (isset($settings['api_path'])) {
            $this->api_path = $settings['api_path'];
        }

        if (isset($settings['app_secret'])) {
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

    public function startLogin($identity_provider, $email_address = false, $client_data = false, $client_encryption_key = false, $tags = false, $redirect = true) {
        $data = array(
            'app_id' => $this->app_id,
            'identity_provider' => $identity_provider
        );

        if (!empty($email_address)) {
            $data['email_address'] = $email_address;
        }

        if (!empty($client_data)) {
            if (is_object($client_data)) {
                $client_data = json_encode($client_data);
            }
            $data['client_data'] = $client_data;
        }

        if (!empty($client_encryption_key)) {
            $data['client_encryption_key'] = $client_encryption_key;
        }

        if (!empty($tags)) {
            $data['tags'] = $tags;
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

    public function createEvent($type, $validate, $local_validation, $email_address, $ip_address, $user_agent, $first_name, $last_name, $tags = false) {
        if (empty($type)) {
            throw new Exception("'type' must be provided");
        }
        if (!in_array($type, self::$eventType)) {
            throw new Exception("'type' must be either LocalLogin or LocalLogout");
        }

        $data = array(
            'app_id' => $this->app_id,
            'type' => $type
        );

        if (!empty($local_validation)) {
            if (!in_array($local_validation, EventValidationTypes::$eventValidationTypes)) {
                throw new Exception("'local_validation' must be either Pass, Fail, or NotApplicable");
            }
            $data['local_validation'] = $local_validation;
        }

        if (!empty($validate)) {
            $data['validate'] = $validate;
        }
        if (!empty($email_address)) {
            $data['email_address'] = $email_address;
        }
        if (!empty($ip_address)) {
            $data['ip_address'] = $ip_address;
        }
        if (!empty($user_agent)) {
            $data['user_agent'] = $user_agent;
        }
        if (!empty($first_name)) {
            $data['first_name'] = $first_name;
        }
        if (!empty($last_name)) {
            $data['last_name'] = $last_name;
        }
        if (!empty($tags)) {
            $data['tags'] = $tags;
        }

        return $this->idpx()->createEvent($data);
    }

    public function updateEvent($event_id, $local_success, $tags) {
        if (empty($event_id)) {
            throw new Exception("'event_id' must be provided");
        }

        $data = array(
            'app_id' => $this->app_id
        );

        if (!empty($local_success)) {
            if (!in_array($local_success, EventValidationTypes::$eventValidationTypes)) {
                throw new Exception("'local_success' must be either Pass, Fail, or NotApplicable");
            }
            $data['local_success'] = $local_success;
        }

        if (!empty($tags)) {
            $data['tags'] = $tags;
        }

        return $this->idpx()->updateEvent($event_id, $data);
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

    public static function parseToken($url) {
        $out_url = parse_url($url);
        $query = $out_url['query'];
        parse_str($query, $output);
        return $output[LogonClient::token];
    }
}