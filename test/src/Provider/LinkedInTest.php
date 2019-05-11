<?php namespace League\OAuth2\Client\Test\Provider;

use InvalidArgumentException;
use League\OAuth2\Client\Tool\QueryBuilderTrait;
use Mockery as m;

class LinkedinTest extends \PHPUnit_Framework_TestCase
{
    use QueryBuilderTrait;

    protected $provider;

    protected function setUp()
    {
        $this->provider = new \League\OAuth2\Client\Provider\LinkedIn([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
        ]);
    }

    public function tearDown()
    {
        m::close();
        parent::tearDown();
    }

    public function testAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertArrayHasKey('scope', $query);
        $this->assertArrayHasKey('response_type', $query);
        $this->assertArrayHasKey('approval_prompt', $query);
        $this->assertNotNull($this->provider->getState());
    }

    public function testResourceOwnerDetailsUrl()
    {
        $accessToken = m::mock('League\OAuth2\Client\Token\AccessToken');
        $expectedFields = $this->provider->getFields();
        $url = $this->provider->getResourceOwnerDetailsUrl($accessToken);
        $uri = parse_url($url);
        $path = $uri['path'];
        $query = explode('=', $uri['query']);
        $fields = $query[1];
        $actualFields = explode(',', preg_replace('/^\((.*)\)$/', '\1', $fields));
        $this->assertEquals('/v2/me', $path);
        $this->assertEquals('projection', $query[0]);
        $this->assertEquals($expectedFields, $actualFields);
    }

    public function testResourceOwnerEmailUrl()
    {
        $accessToken = m::mock('League\OAuth2\Client\Token\AccessToken');
        $expectedFields = $this->provider->getFields();
        $url = $this->provider->getResourceOwnerEmailUrl($accessToken);
        $uri = parse_url($url);
        parse_str($uri['query'], $query);
        $this->assertEquals('/v2/clientAwareMemberHandles', $uri['path']);
        $this->assertEquals('(elements*(state,primary,type,handle~))', $query['projection']);
    }

    public function testScopes()
    {
        $scopeSeparator = ' ';
        $options = ['scope' => [uniqid(), uniqid()]];
        $query = ['scope' => implode($scopeSeparator, $options['scope'])];
        $url = $this->provider->getAuthorizationUrl($options);
        $encodedScope = $this->buildQueryString($query);
        $this->assertContains($encodedScope, $url);
    }

    public function testFields()
    {
        $provider = new \League\OAuth2\Client\Provider\LinkedIn([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none'
        ]);

        $currentFields = $provider->getFields();
        $customFields = [uniqid(), uniqid()];

        $this->assertTrue(is_array($currentFields));
        $provider->withFields($customFields);
        $this->assertEquals($customFields, $provider->getFields());
    }

    public function testNonArrayFieldsDuringInstantiationThrowsException()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $provider = new \League\OAuth2\Client\Provider\LinkedIn([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none',
            'fields' => 'foo'
        ]);
    }

    public function testGetAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);

        $this->assertEquals('/oauth/v2/authorization', $uri['path']);
    }

    public function testGetBaseAccessTokenUrl()
    {
        $params = [];

        $url = $this->provider->getBaseAccessTokenUrl($params);
        $uri = parse_url($url);

        $this->assertEquals('/oauth/v2/accessToken', $uri['path']);
    }

    public function testGetAccessToken()
    {
        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->andReturn('{"access_token": "mock_access_token", "expires_in": 3600}');
        $response->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->times(1)->andReturn($response);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

        $this->assertEquals('mock_access_token', $token->getToken());
        $this->assertLessThanOrEqual(time() + 3600, $token->getExpires());
        $this->assertGreaterThanOrEqual(time(), $token->getExpires());
        $this->assertNull($token->getRefreshToken());
        $this->assertNull($token->getResourceOwnerId());
    }

    public function testUserData()
    {
        $apiProfileResponse = json_decode(file_get_contents(__DIR__.'/../../api_responses/me.json'), true);
        $somethingExtra = ['more' => uniqid()];
        $apiProfileResponse['somethingExtra'] = $somethingExtra;

        $postResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $postResponse->shouldReceive('getBody')->andReturn('{"access_token": "mock_access_token", "expires_in": 3600}');
        $postResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);

        $userResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $userResponse->shouldReceive('getBody')->andReturn(json_encode($apiProfileResponse));
        $userResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')
            ->times(2)
            ->andReturn($postResponse, $userResponse);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        $user = $this->provider->getResourceOwner($token);

        $this->assertEquals('abcdef1234', $user->getId());
        $this->assertEquals('abcdef1234', $user->toArray()['id']);
        $this->assertEquals('John', $user->getFirstName());
        $this->assertEquals('John', $user->toArray()['localizedFirstName']);
        $this->assertEquals('Doe', $user->getLastName());
        $this->assertEquals('Doe', $user->toArray()['localizedLastName']);
        $this->assertEquals('http://example.com/avatar_800_800.jpeg', $user->getImageUrl());
        $this->assertEquals('https://www.linkedin.com/in/john-doe', $user->getUrl());
        $this->assertEquals($somethingExtra, $user->getAttribute('somethingExtra'));
        $this->assertEquals($somethingExtra, $user->toArray()['somethingExtra']);
        $this->assertEquals($somethingExtra['more'], $user->getAttribute('somethingExtra.more'));
        $this->assertEquals([100, 200, 400, 800], $user->getImageSizes());
        $this->assertTrue(is_array($user->getImageBySize(100)));
        $this->assertNull($user->getImageBySize(300));
    }

    public function testMissingUserData()
    {
        $userId = rand(1000,9999);
        $firstName = uniqid();
        $lastName = uniqid();
        $apiProfileResponse = json_decode(file_get_contents(__DIR__.'/../../api_responses/me.json'), true);
        $apiProfileResponse['id'] = $userId;
        $apiProfileResponse['localizedFirstName'] = $firstName;
        $apiProfileResponse['localizedLastName'] = $lastName;
        unset($apiProfileResponse['profilePicture']);
        unset($apiProfileResponse['vanityName']);

        $postResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $postResponse->shouldReceive('getBody')->andReturn('{"access_token": "mock_access_token", "expires_in": 3600}');
        $postResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);

        $userResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $userResponse->shouldReceive('getBody')->andReturn(json_encode($apiProfileResponse));
        $userResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')
            ->times(2)
            ->andReturn($postResponse, $userResponse);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        $user = $this->provider->getResourceOwner($token);

        $this->assertEquals($userId, $user->getId());
        $this->assertEquals($userId, $user->toArray()['id']);
        $this->assertEquals($firstName, $user->getFirstName());
        $this->assertEquals($firstName, $user->toArray()['localizedFirstName']);
        $this->assertEquals($lastName, $user->GeTlAsTnAmE()); // https://github.com/thephpleague/oauth2-linkedin/issues/4
        $this->assertEquals($lastName, $user->toArray()['localizedLastName']);
        $this->assertEquals(null, $user->getImageurl());
        $this->assertEquals(null, $user->getUrl());
    }

    public function testUserEmail()
    {
        $apiEmailResponse = json_decode(file_get_contents(__DIR__.'/../../api_responses/email.json'), true);

        $postResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $postResponse->shouldReceive('getBody')->andReturn('{"access_token": "mock_access_token", "expires_in": 3600}');
        $postResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);

        $userResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $userResponse->shouldReceive('getBody')->andReturn(json_encode($apiEmailResponse));
        $userResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')
            ->times(2)
            ->andReturn($postResponse, $userResponse);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        $email = $this->provider->getResourceOwnerEmail($token);

        $this->assertEquals('resource-owner@example.com', $email);
    }

    public function testUserEmailNullIfApiResponseInvalid()
    {
        foreach ([null, []] as $apiEmailResponse) {
            $postResponse = m::mock('Psr\Http\Message\ResponseInterface');
            $postResponse->shouldReceive('getBody')->andReturn('{"access_token": "mock_access_token", "expires_in": 3600}');
            $postResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);

            $userResponse = m::mock('Psr\Http\Message\ResponseInterface');
            $userResponse->shouldReceive('getBody')->andReturn(json_encode($apiEmailResponse));
            $userResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);

            $client = m::mock('GuzzleHttp\ClientInterface');
            $client->shouldReceive('send')
                ->times(2)
                ->andReturn($postResponse, $userResponse);
            $this->provider->setHttpClient($client);

            $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
            $email = $this->provider->getResourceOwnerEmail($token);

            $this->assertNull($email);
        }
    }

    /**
     * @expectedException League\OAuth2\Client\Provider\Exception\IdentityProviderException
     **/
    public function testExceptionThrownWhenErrorObjectReceived()
    {
        $message = uniqid();
        $status = rand(400,600);
        $postResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $postResponse->shouldReceive('getBody')->andReturn('{"error_description": "'.$message.'","error": "invalid_request"}');
        $postResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $postResponse->shouldReceive('getStatusCode')->andReturn($status);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')
            ->times(1)
            ->andReturn($postResponse);
        $this->provider->setHttpClient($client);
        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
    }
}
