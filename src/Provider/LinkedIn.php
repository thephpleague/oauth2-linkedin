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

        $attributes = $this->getUserAttributesFromResponse($response);

        $user->exchangeArray([
            'uid' => $attributes['id'],
            'name' => $attributes['firstName'].' '.$attributes['lastName'],
            'firstname' => $attributes['firstName'],
            'lastname' => $attributes['lastName'],
            'email' => $attributes['email'],
            'location' => $attributes['location'],
            'description' => $attributes['description'],
            'imageurl' => $attributes['pictureUrl'],
            'urls' => $attributes['publicProfileUrl'],
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

    private function getUserAttributesFromResponse($response)
    {
        $attributes = [];
        $attributes['id'] = $this->issetAndGetValue($response->id);
        $attributes['firstName'] = $this->issetAndGetValue($response->firstName);
        $attributes['lastName'] = $this->issetAndGetValue($response->lastName);
        $attributes['email'] = $this->issetAndGetValue($response->emailAddress);
        $attributes['location'] = $this->issetAndGetValue($response->location->name);
        $attributes['description'] = $this->issetAndGetValue($response->headline);
        $attributes['pictureUrl'] = $this->issetAndGetValue($response->pictureUrl);
        $attributes['publicProfileUrl'] = $this->issetAndGetValue($response->publicProfileUrl);

        return $attributes;
    }

    private function issetAndGetValue($item)
    {
        return isset($item) ? $item : null;
    }
}
