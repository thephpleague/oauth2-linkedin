<?php

namespace League\OAuth2\Client\Provider;

use InvalidArgumentException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class LinkedIn extends AbstractProvider
{
    use BearerAuthorizationTrait;

    /**
     * Default scopes
     *
     * @var array
     */
    public $defaultScopes = ['r_liteprofile', 'r_emailAddress'];

    /**
     * Requested fields in scope, seeded with default values
     *
     * @var array
     * @see https://docs.microsoft.com/en-us/linkedin/consumer/integrations/self-serve/sign-in-with-linkedin?context=linkedin/consumer/context#response-body-schema
     */
    protected $fields = [
        'id',
        'firstName',
        'lastName',
        'localizedFirstName',
        'localizedLastName',
        'profilePicture(displayImage~:playableStreams)',
    ];

    /**
     * Try getting ResourceOwner email in a separate request.
     *
     * @var bool
     */
    protected $getEmail = true;

    /**
     * Constructs an OAuth 2.0 service provider.
     *
     * @param array $options An array of options to set on this provider.
     *     Options include `clientId`, `clientSecret`, `redirectUri`, and `state`.
     *     Individual providers may introduce more options, as needed.
     * @param array $collaborators An array of collaborators that may be used to
     *     override this provider's default behavior. Collaborators include
     *     `grantFactory`, `requestFactory`, and `httpClient`.
     *     Individual providers may introduce more collaborators, as needed.
     */
    public function __construct(array $options = [], array $collaborators = [])
    {
        if (isset($options['fields']) && !is_array($options['fields'])) {
            throw new InvalidArgumentException('The fields option must be an array');
        }

        parent::__construct($options, $collaborators);
    }

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
     * Get authorization url to begin OAuth flow
     *
     * @return string
     */
    public function getBaseAuthorizationUrl()
    {
        return 'https://www.linkedin.com/oauth/v2/authorization';
    }

    /**
     * Get access token url to retrieve token
     *
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return 'https://www.linkedin.com/oauth/v2/accessToken';
    }

    /**
     * Get provider url to fetch user details
     *
     * @param  AccessToken $token
     *
     * @return string
     */
    public function getResourceOwnerEmailUrl(AccessToken $token)
    {
        $projection = '(elements*(state,primary,type,handle~))';

        return 'https://api.linkedin.com/v2/clientAwareMemberHandles?q=members&projection='.$projection;
    }

    /**
     * Get provider url to fetch user details
     *
     * @param  AccessToken $token
     *
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return 'https://api.linkedin.com/v2/me?projection=(' . implode(',', $this->fields).')';
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
        if (isset($data['error'])) {
            throw new IdentityProviderException(
                $data['error_description'] ?: $response->getReasonPhrase(),
                $response->getStatusCode(),
                $response
            );
        }
    }

    /**
     * Generate a user object from a successful user details request.
     *
     * @param array $response
     * @param AccessToken $token
     * @return LinkedInResourceOwner
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new LinkedInResourceOwner($response);
    }

    /**
     * @param AccessToken $token
     *
     * @return array|mixed
     * @throws IdentityProviderException
     */
    protected function fetchResourceOwnerDetails(AccessToken $token)
    {
        $profile = parent::fetchResourceOwnerDetails($token);
        $emailResponse = null;

        if ($this->getEmail) {
            $emailUrl = $this->getResourceOwnerEmailUrl($token);
            $emailRequest = $this->getAuthenticatedRequest(self::METHOD_GET, $emailUrl, $token);
            $emailResponse = $this->getParsedResponse($emailRequest);
        }

        return [
            'profile' => $profile,
            'email' => $emailResponse,
        ];
    }

    /**
     * Returns the requested fields in scope.
     *
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Updates the requested fields in scope.
     *
     * @param  array   $fields
     *
     * @return LinkedIn
     */
    public function withFields(array $fields)
    {
        $this->fields = $fields;

        return $this;
    }
}
