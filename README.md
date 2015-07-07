# LinkedIn Provider for OAuth 2.0 Client
[![Latest Version](https://img.shields.io/github/release/thephpleague/oauth2-linkedin.svg?style=flat-square)](https://github.com/thephpleague/oauth2-linkedin/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/thephpleague/oauth2-linkedin/master.svg?style=flat-square)](https://travis-ci.org/thephpleague/oauth2-linkedin)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/thephpleague/oauth2-linkedin.svg?style=flat-square)](https://scrutinizer-ci.com/g/thephpleague/oauth2-linkedin/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/thephpleague/oauth2-linkedin.svg?style=flat-square)](https://scrutinizer-ci.com/g/thephpleague/oauth2-linkedin)
[![Total Downloads](https://img.shields.io/packagist/dt/league/oauth2-linkedin.svg?style=flat-square)](https://packagist.org/packages/league/oauth2-linkedin)

This package provides LinkedIn OAuth 2.0 support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client).

## Installation

To install, use composer:

```
composer require league/oauth2-linkedin
```

## Usage

Usage is the same as The League's OAuth client, using `\League\OAuth2\Client\Provider\LinkedIn` as the provider.

### Authorization Code Flow

```php
$provider = new League\OAuth2\Client\Provider\LinkedIn([
    'clientId'          => '{linkedin-client-id}',
    'clientSecret'      => '{linkedin-client-secret}',
    'redirectUri'       => 'https://example.com/callback-url',
]);

if (!isset($_GET['code'])) {

    // If we don't have an authorization code then get one
    $authUrl = $provider->getAuthorizationUrl();
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: '.$authUrl);
    exit;

// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

    unset($_SESSION['oauth2state']);
    exit('Invalid state');

} else {

    // Try to get an access token (using the authorization code grant)
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);

    // Optional: Now you have a token you can look up a users profile data
    try {

        // We got an access token, let's now get the user's details
        $user = $provider->getUser($token);

        // Use these details to create a new profile
        printf('Hello %s!', $user->getFirstname());

    } catch (Exception $e) {

        // Failed to get user details
        exit('Oh dear...');
    }

    // Use this to interact with an API on the users behalf
    echo $token;
}
```

### Managing Scopes

When creating your LinkedIn authorization URL, you can specify the scopes your application may authorize.

```php
$options = ['scopes' => ['r_basicprofile','r_emailaddress']];

$url = $provider->getAuthorizationUrl($options);
```

At the time of authoring this documentation, the following scopes are available.

- r_basicprofile
- r_emailaddress
- rw_company_admin
- w_share

## Testing

``` bash
$ ./vendor/bin/phpunit
```

## Contributing

Please see [CONTRIBUTING](https://github.com/thephpleague/oauth2-linkedin/blob/master/CONTRIBUTING.md) for details.


## Credits

- [Steven Maguire](https://github.com/stevenmaguire)
- [All Contributors](https://github.com/thephpleague/oauth2-linkedin/contributors)


## License

The MIT License (MIT). Please see [License File](https://github.com/thephpleague/oauth2-linkedin/blob/master/LICENSE) for more information.
