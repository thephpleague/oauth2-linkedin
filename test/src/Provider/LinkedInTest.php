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
        $email = uniqid();
        $userId = rand(1000,9999);
        $firstName = uniqid();
        $lastName = uniqid();
        $picture = uniqid();
        $location = uniqid();
        $url = uniqid();
        $description = uniqid();
        $summary = uniqid();
        $somethingExtra = ['more' => uniqid()];

        $postResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $postResponse->shouldReceive('getBody')->andReturn('{"access_token": "mock_access_token", "expires_in": 3600}');
        $postResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);

        $userResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $userResponse->shouldReceive('getBody')->andReturn('{"id": '.$userId.', "firstName": "'.$firstName.'", "lastName": "'.$lastName.'", "emailAddress": "'.$email.'", "location": { "name": "'.$location.'" }, "headline": "'.$description.'", "summary": "'.$summary.'", "pictureUrl": "'.$picture.'", "publicProfileUrl": "'.$url.'", "somethingExtra": '.json_encode($somethingExtra).'}');
        $userResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')
            ->times(2)
            ->andReturn($postResponse, $userResponse);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        $user = $this->provider->getResourceOwner($token);

        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals($email, $user->toArray()['emailAddress']);
        $this->assertEquals($userId, $user->getId());
        $this->assertEquals($userId, $user->toArray()['id']);
        $this->assertEquals($firstName, $user->getFirstName());
        $this->assertEquals($firstName, $user->toArray()['firstName']);
        $this->assertEquals($lastName, $user->GeTlAsTnAmE()); // https://github.com/thephpleague/oauth2-linkedin/issues/4
        $this->assertEquals($lastName, $user->toArray()['lastName']);
        $this->assertEquals($picture, $user->getImageurl());
        $this->assertEquals($picture, $user->toArray()['pictureUrl']);
        $this->assertEquals($location, $user->getLocation());
        $this->assertEquals($location, $user->toArray()['location']['name']);
        $this->assertEquals($url, $user->getUrl());
        $this->assertEquals($url, $user->toArray()['publicProfileUrl']);
        $this->assertEquals($description, $user->getDescription());
        $this->assertEquals($description, $user->toArray()['headline']);
        $this->assertEquals($summary, $user->getSummary());
        $this->assertEquals($summary, $user->toArray()['summary']);
        $this->assertEquals($somethingExtra, $user->getAttribute('somethingExtra'));
        $this->assertEquals($somethingExtra, $user->toArray()['somethingExtra']);
        $this->assertEquals($somethingExtra['more'], $user->getAttribute('somethingExtra.more'));
    }

    public function testMissingUserData()
    {
        $email = uniqid();
        $userId = rand(1000,9999);
        $firstName = uniqid();
        $lastName = uniqid();
        $location = uniqid();
        $url = uniqid();
        $description = uniqid();
        $summary = uniqid();

        $postResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $postResponse->shouldReceive('getBody')->andReturn('{"access_token": "mock_access_token", "expires_in": 3600}');
        $postResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);

        $userResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $userResponse->shouldReceive('getBody')->andReturn('{"id": '.$userId.', "firstName": "'.$firstName.'", "lastName": "'.$lastName.'", "emailAddress": "'.$email.'", "location": { "name": "'.$location.'" }, "headline": "'.$description.'", "summary": "'.$summary.'", "publicProfileUrl": "'.$url.'"}');
        $userResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')
            ->times(2)
            ->andReturn($postResponse, $userResponse);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        $user = $this->provider->getResourceOwner($token);

        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals($email, $user->toArray()['emailAddress']);
        $this->assertEquals($userId, $user->getId());
        $this->assertEquals($userId, $user->toArray()['id']);
        $this->assertEquals($firstName, $user->getFirstName());
        $this->assertEquals($firstName, $user->toArray()['firstName']);
        $this->assertEquals($lastName, $user->GeTlAsTnAmE()); // https://github.com/thephpleague/oauth2-linkedin/issues/4
        $this->assertEquals($lastName, $user->toArray()['lastName']);
        $this->assertEquals(null, $user->getImageurl());
        $this->assertEquals($location, $user->getLocation());
        $this->assertEquals($location, $user->toArray()['location']['name']);
        $this->assertEquals($url, $user->getUrl());
        $this->assertEquals($url, $user->toArray()['publicProfileUrl']);
        $this->assertEquals($description, $user->getDescription());
        $this->assertEquals($description, $user->toArray()['headline']);
        $this->assertEquals($summary, $user->getSummary());
        $this->assertEquals($summary, $user->toArray()['summary']);
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
