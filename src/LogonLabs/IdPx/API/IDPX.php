<?php
/**
 * Created by PhpStorm.
 * User: hlee
 * Date: 2019-02-25
 * Time: 2:17 PM
 */

namespace LogonLabs\IdPx\API;


class IDPX {

    private $connection;

    private $options;

    const ROUTE_VALIDATE = 'validate';
    const ROUTE_REDIRECT = 'redirect';
    const ROUTE_START = 'start';
    const ROUTE_PING = 'ping';
    const ROUTE_PROVIDERS = 'providers';
    const ROUTE_AUDIT = 'audit';
    const ROUTE_VALIDATE_LOCAL = 'validate_local';


    public function __construct($connection, $options = array()) {
        if (!$this->connection) {
            $this->connection = $connection;
        }
        $this->options = $options;
    }

    public function handleRedirect($response, $redirect = true) {
        if ($response['redirect'] && $redirect) {
            $this->connection->redirectUrl($response['redirect']);
        }
    }

    public function getProviders($data = array()) {
        $cmd = self::ROUTE_PROVIDERS;
        return $this->connection->get($cmd , $data);
    }


    public function auditLogon($data = array()) {
        $cmd = self::ROUTE_AUDIT;
        return $this->connection->post($cmd , $data);
    }

    public function ping($app_id) {;
        $cmd = self::ROUTE_PING;
        return $this->connection->get($cmd , array(
            'app_id' => $app_id
        ));
    }

    public function startLogin($data) {
        $cmd = self::ROUTE_START;
        return $this->connection->post($cmd , $data);
    }


    public function redirectLogin($token, $redirect = true) {
        $cmd = self::ROUTE_REDIRECT;
        $authorization =  $this->connection->get($cmd, array(
            'token' => $token
        ));

        $this->handleRedirect($authorization, $redirect);
        return $authorization;
    }

    public function validateLocalLogin($data) {
        $cmd = self::ROUTE_VALIDATE_LOCAL;
        return $this->connection->post($cmd, $data);;
    }

    public function validateLogin($data) {
        $cmd = self::ROUTE_VALIDATE;
        return $this->connection->post($cmd, $data);;
    }
}