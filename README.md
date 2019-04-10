The official LogonLabs PHP API library.

## Documentation

For the full Developer Documentation please visit: https://logonlabs.com/docs/api/

### Install LogonLabs

Via GitHub Download:

1. Git clone the whole project to your library folder.
2. Load the files with:
    
    
    require(PATH_TO_SDK . 'vendor/autoload.php');

Via composer: (future)

	composer require logonlabs/logonlabs-php

## Usage

### Client Side Workflow

Use the snippet below to initiate the SSO login process for you user. This will start the Login process by starting a redirect session and redirect to the LogonLabs to broker the SSO request with the desired identity provider.

```php
<?php

use LogonLabs\IdPx\API\LogonClient as LogonClient;
use LogonLabs\IdentityProviders as IdentityProviders;

$logonClient = new LogonClient(array(
    'app_id' => 'YOUR_APP_ID',
));
$logonClient->startLogin(IdentityProviders::GOOGLE);
```

### Server Side Workflows

Use the snippet below to validate the login data from LogonLabs and continue your app's authentication workflows (ie, create a user session token).

```php
<?php

use LogonLabs\IdPx\API\LogonClient as LogonClient;
$logonClient = new LogonClient(array(
    'app_id' => 'YOUR_APP_ID',
    'app_secret' => 'YOUR_APP_SECRET'
));

$token = $_REQUEST['token'];
$loginData = $logonClient->validateLogin($token);

if ($loginData['body']['validation_success']) {
    //success!
}
//Success! Continue your app's login workflow and create a user session, etc!
```