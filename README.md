# LogonLabs PHP
---
The official LogonLabs PHP library.
## Download
---
    composer require logonlabs/logonlabs-php
##Logon Labs API
---
For the full Developer Documentation please visit: https://logonlabs.com/docs/api
---
### Instantiating a new client
Create a new instance of `LogonClient`.  
Your `APP_ID` can be found in [App Settings](https://logonlabs.com/app/#/app-settings).
`APP_SECRETS` are configured [here](https://logonlabs.com/app/#/app-secrets).
The `LOGONLABS_API_ENDPOINT` should be set to `https://api.logonlabs.com`.
```php
<?php
use LogonLabs\IdPx\API\LogonClient as LogonClient;
$logonClient = new LogonClient(array(
    'app_id' => '{APP_ID}',
    'app_secret' => '{APP_SECRET}',
    'api_path' => '{LOGONLABS_API_ENDPOINT}',
));
```
---
### SSO Login QuickStart
The StartLogin function in the JS library begins the Logon Labs managed SSO process.  Configuration is required at https://logonlabs.com/app/#app-settings.  Once the `callback Url` has been configured for your application you can consume the payload sent to your page.
>Further documentation on starting the login process via our JavaScript client can be found at our GitHub page [here](https://github.com/logonlabs/logonlabs-js)
The following example demonstrates what to do once the `callback Url` has been used by our system to redirect the user back to your page:
```php
<?php
$token = $_REQUEST['token'];
$loginData = $logonClient->validateLogin($token);
if ($loginData['body']['event_success']) {
    //success!
}
```
---
###PHP Only Workflow
The following workflow is required if you're using a php to process all transaction requests.  If this does not apply to you please refer to the SSO Login QuickStart section.
#### Step 1 - StartLogin
This call begins the Logon Labs managed SSO process.  The `client_data` property is optional and is used to pass any data that is required after validating the request.  The `client_encryption_key` property is optionally passed if the application requires encrypting any data that is passed between the front and back ends of it's infrastructure. The `tags`property is an Array of type Tag which is a simple object representing a key/value pair.  The `redirect` is a key to allow auto-redirect or return a url for server redirection.
```php
<?php
use LogonLabs\IdentityProviders as IdentityProviders;
$client_data = array("client_data" => "value");
$client_encryption_key = "qbTRzCvUju";
$tags = array("example-key" => "example-value");
$redirect = false;
$redirect_uri = $logonClient->startLogin(IdentityProviders::GOOGLE, "emailAddress", $client_data, $client_encryption_key, $tags, $redirect);
```

The `redirect_uri` property returned should be redirected to by the application.  Upon the user completing entering their credentials they will be redirected to the `callback_url` set within the application settings at https://logonlabs.com/app/#/app-settings.
&nbsp;
#### Step 2 - ValidateLogin
This method is used to validate the results of the login attempt.  `query_token` corresponds to the query parameter with the name `token` appended to the callback url specified for your app.
The response contains all details of the login and the user has now completed the SSO workflow.  If there is any additional information to add UpdateEvent can be called on the `event_id` returned.
```php
<?php
use LogonLabs\EventValidationTypes as EventValidationTypes;

$token = $_REQUEST['token'];
$loginData = $logonClient->validateLogin($token);
if ($loginData['body']['event_success']) {
    //success!
} else {
    $validation_details = $loginData['body']['validation_details'];
    if(strcasecmp($validation_details['auth_validation'], EventValidationTypes::Fail) == 0)) {
        //authentication with identity provider failed
    }
    if(strcasecmp($validation_details['email_match_validation'], EventValidationTypes::Fail) == 0)) {
        //email didn't match the one provided to StartLogin
    }
    if(strcasecmp($validation_details['ip_validation'], EventValidationTypes::Fail) == 0) 
        || strcasecmp($validation_details['geo_validation'], EventValidationTypes::Fail) == 0)
        || strcasecmp($validation_details['time_validation'], EventValidationTypes::Fail) == 0)) {
        //validation failed via restriction settings for the app
    }
}
```
---
###Events
The CreateEvent method allows one to create events that are outside of our SSO workflows.  UpdateEvent can be used to update any events made either by CreateEvent or by our SSO login.
```php
<?php 
use LogonLabs\EventValidationTypes as EventValidationTypes;
$local_validation = EventValidationTypes::Pass;
$tags = array('example-key' => 'example-value');
$response = $logon->createEvent(LogonClient::LocalLogin, true,
        $local_validation, "email_address", "ip_address", "user_agent",
        "first_name", "last_name", $tags);

$event_id = $response['body']['event_id'];
$local_validation = EventValidationTypes::Fail;
$tags = array('failure-field' => 'detailed reason for failure');
$response = $logon->updateEvent($event_id, $local_validation, $tags);
```
---
### Helper Methods
#### GetProviders
This method is used to retrieve a list of all providers enabled for the application.
If an email address is passed to the method it will further filter any providers available/disabled for the domain of the address.
```php
<?php
$response = $logon->getProviders("emailAddress");
$result = $response['body'];
$identity_providers = $result['identity_providers'];
foreach ($identity_providers as $provider) {
    //each individual providers available for this email address
}
```
#### Encrypt
The PHP SDK has built in methods for encrypting strings using the AES encryption standard.  Use a value for your encryption key that only your client will know how to decrypt 
```php
<?php
use LogonLabs\IdPx\API\LogonClient as LogonClient;
$client_encryption_key = "qbTRzCvUju";
$value = "string to be encrypted";
$encrypted_string = LogonClient::encrypt($client_encryption_key, $value); 
```
#### ParseToken
This method parses out the value of the token query parameter returned with your callback url.
```php
<?php
use LogonLabs\IdPx\API\LogonClient as LogonClient;
$callback_url = "https://example.com?token=7dc6e5dc4f2641aab64a6fa1ed91a3b1";
$token = LogonClient::parseToken($callback_url);

var_dump($token);

//output
//string(32) "7dc6e5dc4f2641aab64a6fa1ed91a3b1"
```