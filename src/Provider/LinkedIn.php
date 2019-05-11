<?php

namespace League\OAuth2\Client\Provider;

use Exception;
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
    public $defaultScopes = ['r_liteprofile', 'r_emailaddress'];

    /**
     * Requested fields in scope, seeded with default values
     *
     * @var array
     * @see https://developer.linkedin.com/docs/fields/basic-profile
     */
    protected $fields = [
        'id', 'firstName', 'lastName', 'localizedFirstName', 'localizedLastName',
        'profilePicture(displayImage~:playableStreams)',
    ];

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
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        $query = http_build_query([
            'projection' => '(' . implode(',', $this->fields) . ')'
        ]);

        return 'https://api.linkedin.com/v2/me?' . urldecode($query);
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
        $query = http_build_query([
            'q' => 'members',
            'projection' => '(elements*(state,primary,type,handle~))'
        ]);

        return 'https://api.linkedin.com/v2/clientAwareMemberHandles?' . urldecode($query);
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
     * Returns the requested fields in scope.
     *
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Attempts to fetch resource owner's email address via separate API request.
     *
     * @param  AccessToken $token [description]
     * @return string|null
     * @throws IdentityProviderException
     */
    public function getResourceOwnerEmail(AccessToken $token)
    {
        $emailUrl = $this->getResourceOwnerEmailUrl($token);
        $emailRequest = $this->getAuthenticatedRequest(self::METHOD_GET, $emailUrl, $token);
        $emailResponse = $this->getParsedResponse($emailRequest);

        return $this->extractEmailFromResponse($emailResponse);
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

    /**
     * Attempts to extract the email address from a valid email api response.
     *
     * @param  array  $response
     * @return string|null
     */
    protected function extractEmailFromResponse($response = [])
    {
        try {
            $confirmedEmails = array_filter($response['elements'], function ($element) {
                return
                    strtoupper($element['type']) === 'EMAIL'
                    && strtoupper($element['state']) === 'CONFIRMED'
                    && $element['primary'] === true
                    && isset($element['handle~']['emailAddress'])
                ;
            });

            return $confirmedEmails[0]['handle~']['emailAddress'];
        } catch (Exception $e) {
            return null;
        }
    }
}
