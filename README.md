# LogonLabs PHP

The official LogonLabs PHP library.
## Download

    composer require logonlabs/logonlabs-php
## LogonLabs API

- Prior to coding, some configuration is required at https://app.logonlabs.com/app/#app-settings

- For the full Developer Documentation please visit: https://app.logonlabs.com/api/

---
### Instantiating a new client

- Your `APP_ID` can be found in [App Settings](https://app.logonlabs.com/app/#/app-settings).
- `APP_SECRETS` are configured [here](https://app.logonlabs.com/app/#/app-secrets).
- The `LOGONLABS_API_ENDPOINT` should be set to `https://api.logonlabs.com`.

Create a new instance of `LogonClient`.  
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
The StartLogin function in the JS library begins the LogonLabs managed SSO process.
>Further documentation on starting the login process via our JavaScript client can be found at our GitHub page [here](https://github.com/logonlabs/logonlabs-js). 

The following example demonstrates what to do once the `callback Url` has been used by our system to redirect the user back to your page:
```php
<?php
$token = $_REQUEST['token'];
$loginData = $logonClient->validateLogin($token);
if ($loginData['body']['event_success']) {
     //authentication and validation succeeded. proceed with post-auth workflows (ie, create a user session token for your system).
}
```
---
### PHP Only Workflow
The following workflow is required if you're using php to process all transaction requests.  If this does not apply to you, please refer to the SSO Login QuickStart section.
#### Step 1 - StartLogin
This call begins the LogonLabs managed SSO process.  The `client_data` property is optional and is used to pass any data that is required after validating the request.  The `tags` property is an Array of type Tag which is a simple object representing a key/value pair.  The `redirect` is a key to allow auto-redirect or return a url for server redirection.
```php
<?php
use LogonLabs\IdentityProviders as IdentityProviders;
$client_data = array("client_data" => "value");
$tags = array("example-key" => "example-value");
$redirect = false;
$redirect_uri = $logonClient->startLogin(IdentityProviders::GOOGLE, "example@emailaddress.com", $client_data, $tags, $redirect);
```

The `redirect_uri` property returned should be redirected to by the application.  Upon submitting their credentials, users will be redirected to the `callback_url` set within the application settings at https://logonlabs.com/app/#/app-settings.

#### Step 2 - ValidateLogin
This method is used to validate the results of the login attempt.  `query_token` corresponds to the query parameter with the name `token` appended to the callback url specified for your app.
The response contains all details of the login and the user has now completed the SSO workflow.  If there is any additional information to add, UpdateEvent can be called on the `event_id` returned.
```php
<?php
use LogonLabs\EventValidationTypes as EventValidationTypes;

$token = $_REQUEST['token'];
$loginData = $logonClient->validateLogin($token);
if ($loginData['body']['event_success']) {
    //success!
} else {
    $validation_details = $loginData['body']['validation_details'];

    if(strcasecmp($validation_details['domain_validation'], EventValidationTypes::Fail) == 0)) {
        //provider used was not enabled for the domain of the user that was authenticated
    }
    if(strcasecmp($validation_details['ip_validation'], EventValidationTypes::Fail) == 0) 
        || strcasecmp($validation_details['geo_validation'], EventValidationTypes::Fail) == 0)
        || strcasecmp($validation_details['time_validation'], EventValidationTypes::Fail) == 0)) {
        //validation failed via restriction settings for the app
    }
}
```
---
### Events
The CreateEvent method can be used to create events that are outside of our SSO workflows.  UpdateEvent can be used to update any events made either by CreateEvent or by our SSO login.
```php
<?php 
use LogonLabs\EventValidationTypes as EventValidationTypes;
$local_validation = EventValidationTypes::Pass;
$tag = array(
    'key' => 'example-key',
    'value' => 'example-value'
);
$tags = array($tag);
$response = $logonClient->createEvent(LogonClient::LocalLogin, true,
        $local_validation, "{EMAIL_ADDRESS}", "{IP_ADDRESS}", "{USER_AGENT}",
        "{FIRST_NAME}", "{LAST_NAME}", $tags);

```
---
### Helper Methods
#### GetProviders
This method is used to retrieve a list of all providers enabled for the application.
If an email address is passed it will further filter any providers available/disabled for the domain of the address.  
If any Enterprise Identity Providers have been configured a separate set of matching providers will also be returned in enterprise_identity_providers.
```php
<?php
$response = $logonClient->getProviders("example@emailaddress.com");
$result = $response['body'];
$suggestedProvider = $result[suggested_identity_provider]; //use suggested provider
$social_providers = $result['social_identity_providers'];
foreach ($social_providers as $provider) {
    //each individual provider available for this app / email address
}
$enterprise_providers = $result['enterprise_identity_providers'];
foreach ($enterprise_providers as $provider) {
    //each enterprise provider available for this app / email address
}
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