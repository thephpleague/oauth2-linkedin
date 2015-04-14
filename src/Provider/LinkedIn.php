<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Entity\User;
use League\OAuth2\Client\Token\AccessToken;

class LinkedIn extends AbstractProvider
{
    public $scopes = ['r_basicprofile r_emailaddress r_contactinfo'];
    public $responseType = 'json';
    public $authorizationHeader = 'Bearer';
    public $fields = [
        'id', 'email-address', 'first-name', 'last-name', 'headline',
        'location', 'industry', 'picture-url', 'public-profile-url',
    ];

    public function urlAuthorize()
    {
        return 'https://www.linkedin.com/uas/oauth2/authorization';
    }

    public function urlAccessToken()
    {
        return 'https://www.linkedin.com/uas/oauth2/accessToken';
    }

    public function urlUserDetails(AccessToken $token)
    {
        $fields = implode(',', $this->fields);
        return 'https://api.linkedin.com/v1/people/~:(' . $fields . ')?format=json';
    }

    public function userDetails($response, AccessToken $token)
    {
        $user = new User();

        $id = $this->issetAndGetValue($response->id);
        $firstName = $this->issetAndGetValue($response->firstName);
        $lastName = $this->issetAndGetValue($response->lastName);
        $email = $this->issetAndGetValue($response->emailAddress);
        $location = $this->issetAndGetValue($response->location->name);
        $description = $this->issetAndGetValue($response->headline);
        $pictureUrl = $this->issetAndGetValue($response->pictureUrl);
        $publicProfileUrl = $this->issetAndGetValue($response->publicProfileUrl);

        $user->exchangeArray([
            'uid' => $id,
            'name' => $firstName.' '.$lastName,
            'firstname' => $firstName,
            'lastname' => $lastName,
            'email' => $email,
            'location' => $location,
            'description' => $description,
            'imageurl' => $pictureUrl,
            'urls' => $publicProfileUrl,
        ]);

        return $user;
    }

    public function userUid($response, AccessToken $token)
    {
        return $response->id;
    }

    public function userEmail($response, AccessToken $token)
    {
        return $this->issetAndGetValue($response->emailAddress);
    }

    public function userScreenName($response, AccessToken $token)
    {
        return [$response->firstName, $response->lastName];
    }

    private function issetAndGetValue($item)
    {
        return isset($item) ? $item : null;
    }
}
