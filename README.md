# LinkedIn Provider for OAuth 2.0 Client
[![Latest Version](https://img.shields.io/github/release/thephpleague/oauth2-linkedin.svg?style=flat-square)](https://github.com/thephpleague/oauth2-linkedin/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/thephpleague/oauth2-linkedin/master.svg?style=flat-square)](https://travis-ci.org/thephpleague/oauth2-linkedin)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/thephpleague/oauth2-linkedin.svg?style=flat-square)](https://scrutinizer-ci.com/g/thephpleague/oauth2-linkedin/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/thephpleague/oauth2-linkedin.svg?style=flat-square)](https://scrutinizer-ci.com/g/thephpleague/oauth2-linkedin)
[![Total Downloads](https://img.shields.io/packagist/dt/league/oauth2-linkedin.svg?style=flat-square)](https://packagist.org/packages/league/oauth2-linkedin)

This package provides LinkedIn OAuth 2.0 support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client).

## Before You Begin

> The LinkedIn API has been largely closed off and is only available to approved LinkedIn developers. You can request authorization here - [https://business.linkedin.com/marketing-solutions/marketing-partners/become-a-partner/marketing-developer-program](https://business.linkedin.com/marketing-solutions/marketing-partners/become-a-partner/marketing-developer-program)

You may be able to successfully obtain Access Tokens using this package and still not be authorized to access some resources available in the API.

If you encounter the following, or something similar, this policy is being enforced.

```
{
    "serviceErrorCode": 100,
    "message": "Not enough permissions to access: GET /me",
    "status": 403
}
```

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
        $user = $provider->getResourceOwner($token);

        // Use these details to create a new profile
        printf('Hello %s!', $user->getFirstName());

    } catch (Exception $e) {

        // Failed to get user details
        exit('Oh dear...');
    }

    // Use this to interact with an API on the users behalf
    echo $token->getToken();
}
```

### Managing Scopes

When creating your LinkedIn authorization URL, you can specify the state and scopes your application may authorize.

```php
$options = [
    'state' => 'OPTIONAL_CUSTOM_CONFIGURED_STATE',
    'scope' => ['r_liteprofile','r_emailaddress'] // array or string
];

$authorizationUrl = $provider->getAuthorizationUrl($options);
```
If neither are defined, the provider will utilize internal defaults.

At the time of authoring this documentation, the following scopes are available.

- r_liteprofile (requested by default)
- r_emailaddress (requested by default)
- r_fullprofile
- w_member_social
- rw_company_admin

### Retrieving LinkedIn member information

When fetching resource owner details, the provider allows for an explicit list of fields to be returned, so long as they are allowed by the scopes used to retrieve the access token.

A default set of fields is provided. Overriding these defaults and defining a new set of fields is easy using the `withFields` method, which is a fluent method that returns the updated provider.

You can find a complete list of fields on LinkedIn's Developer Documentation:
 - [For r_liteprofile](https://docs.microsoft.com/en-us/linkedin/shared/references/v2/profile/basic-profile).
 - [For r_fullprofile](https://docs.microsoft.com/en-us/linkedin/shared/references/v2/profile/full-profile).

```php
$fields = [
    'id', 'firstName', 'lastName', 'maidenName',
    'headline', 'vanityName', 'birthDate', 'educations'
];

$provider = $provider->withFields($fields);
$member = $provider->getResourceOwner($token);

// or in one line...

$member = $provider->withFields($fields)->getResourceOwner($token);
```

The `getResourceOwner` will return an instance of `League\OAuth2\Client\Provider\LinkedInResourceOwner` which has some helpful getter methods to access basic member details.

For more customization and control, the `LinkedInResourceOwner` object also offers a `getAttribute` method which accepts a string to access specific attributes that may not have a getter method explicitly defined.

```php
$firstName = $member->getFirstName();
$birthDate = $member->getAttribute('birthDate');
```

#### A note about obtaining the resource owner's email address

> The email has to be fetched by the provider in a separate request, it is not one of the profile fields.

When getting the resource owner a second request to fetch the email address will always be attempted. This request will fail silently (and `getEmail()` will return `null`) if the access token provided was not issued with the `r_emailaddress` scope.

```php
$member = $provider->getResourceOwner($token);
$email = $member->getEmail();
```

You can also attempt to fetch the email in a separate request. This request will fail and throw an exception if the access token provided was not issued with the `r_emailaddress` scope.

```php
$emailAddress = $provider->getResourceOwnerEmail($token);

```



### Refresh Tokens

> LinkedIn has introduced Refresh Tokens with OAuth 2.0. This feature is currently available for a limited set of partners. It will be made GA in the near future. [Source](https://developer.linkedin.com/docs/Refresh-Tokens-with-OAuth-2)

If your LinkedIn Client ID is associated with a partner that supports refresh tokens, this package will help you access and work with Refresh Tokens.

```
$refreshToken = $token->getRefreshToken();
$refreshTokenExpiration = $token->getRefreshTokenExpires();
```

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
