<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

class LinkedIn extends AbstractProvider
{
    /**
     * Default scopes
     *
     * @var array
     */
    public $defaultScopes = [];

    /**
     * Requested fields in scope
     *
     * @var array
     */
    public $fields = [
        'id', 'email-address', 'first-name', 'last-name', 'headline',
        'location', 'industry', 'picture-url', 'public-profile-url',
    ];

    /**
     * Get the string used to separate scopes.
     *
     * @return string
     */
    protected function getScopeSeparator()
    {
        return ' ';
    }

    /**
     * Get authorization headers used by this provider.
     *
     * Typically this is "Bearer" or "MAC". For more information see:
     * http://tools.ietf.org/html/rfc6749#section-7.1
     *
     * No default is provided, providers must overload this method to activate
     * authorization headers.
     *
     * @return array
     */
    protected function getAuthorizationHeaders($token = null)
    {
        return ['Authorization' => 'Bearer ' . $token];
    }

    /**
     * Get authorization url to begin OAuth flow
     *
     * @return string
     */
    public function getBaseAuthorizationUrl()
    {
        return 'https://www.linkedin.com/uas/oauth2/authorization';
    }

    /**
     * Get access token url to retrieve token
     *
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return 'https://www.linkedin.com/uas/oauth2/accessToken';
    }

    /**
     * Get provider url to fetch user details
     *
     * @param  AccessToken $token
     *
     * @return string
     */
    public function getUserDetailsUrl(AccessToken $token)
    {
        $fields = implode(',', $this->fields);

        return 'https://api.linkedin.com/v1/people/~:(' . $fields . ')?format=json';
    }

    /**
     * Get the default scopes used by this provider.
     *
     * This should not be a complete list of all scopes, but the minimum
     * required for the provider user interface!
     *
     * @return array
     */
    protected function getDefaultScopes()
    {
        return $this->defaultScopes;
    }

    /**
     * Check a provider response for errors.
     *
     * @throws IdentityProviderException
     * @param  ResponseInterface $response
     * @param  string $data Parsed response data
     * @return void
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {

    }

    /**
     * Generate a user object from a successful user details request.
     *
     * @param array $response
     * @param AccessToken $token
     * @return League\OAuth2\Client\Provider\UserInterface
     */
    protected function createUser(array $response, AccessToken $token)
    {
        $responseValues = $this->getUserAttributesFromResponse($response);

        $attributes = [
            'userId' => $responseValues['id'],
            'firstname' => $responseValues['firstName'],
            'lastname' => $responseValues['lastName'],
            'email' => $responseValues['email'],
            'location' => $responseValues['location'],
            'description' => $responseValues['description'],
            'imageurl' => $responseValues['pictureUrl'],
            'url' => $responseValues['publicProfileUrl'],
        ];

        return new User($attributes);
    }

    /**
     * Attempt to get attributes from response
     *
     * @param  array $response
     *
     * @return array
     */
    private function getUserAttributesFromResponse($response)
    {
        $attributes = [];
        $attributes['id'] = $this->issetAndGetValue($response['id']);
        $attributes['firstName'] = $this->issetAndGetValue($response['firstName']);
        $attributes['lastName'] = $this->issetAndGetValue($response['lastName']);
        $attributes['email'] = $this->issetAndGetValue($response['emailAddress']);
        $attributes['location'] = $this->issetAndGetValue($response['location']['name']);
        $attributes['description'] = $this->issetAndGetValue($response['headline']);
        $attributes['pictureUrl'] = $this->issetAndGetValue($response['pictureUrl']);
        $attributes['publicProfileUrl'] = $this->issetAndGetValue($response['publicProfileUrl']);

        return $attributes;
    }

    /**
     * Checks if value is set, returns if set
     *
     * @param  mixed
     *
     * @return mixed
     */
    private function issetAndGetValue($item)
    {
        return isset($item) ? $item : null;
    }
}
