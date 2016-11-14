<?php

namespace Siebird\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use Siebird\OAuth2\Client\Provider\Exception\FormstackIdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class Formstack extends AbstractProvider
{
    use BearerAuthorizationTrait;

    /**
     * Domain
     *
     * @var string
     */
    public $domain = 'https://www.formstack.com';

    /**
     * Api domain
     *
     * @var string
     */
    public $apiDomain = 'https://www.formstack.com';

    /**
     * Get authorization url to begin OAuth flow
     *
     * @return string
     */
    public function getBaseAuthorizationUrl()
    {
        return $this->domain.'/api/v2/oauth2/authorize';
    }

    /**
     * Get access token url to retrieve token
     *
     * @param  array $params
     *
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return $this->domain.'/api/v2/oauth2/token';
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
        if ($this->domain === 'https://api.harvestapp.com') {
            return $this->apiDomain.'/account/who_am_i';
        }
        return $this->domain.'/account/who_am_i';
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
        return [];
    }

    /**
     * Check a provider response for errors.
     *
     * @throws FormstackIdentityProviderException
     * @param  ResponseInterface $response
     * @param  string $data Parsed response data
     * @return void
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if ($response->getStatusCode() >= 400) {
            throw FormstackIdentityProviderException::clientException($response, $data);
        } elseif (isset($data['error'])) {
            throw FormstackIdentityProviderException::oauthException($response, $data);
        }
    }

    /**
     * Generate a user object from a successful user details request.
     *
     * @param array $response
     * @param AccessToken $token
     * @return League\OAuth2\Client\Provider\ResourceOwnerInterface
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        $user = new FormstackResourceOwner($response);

        return $user->setDomain($this->domain);
    }

    /**
     * Returns an authenticated PSR-7 request instance.
     *
     * @param  string $method
     * @param  string $url
     * @param  AccessToken|string $token
     * @param  array $options Any of "headers", "body", and "protocolVersion".
     * @return RequestInterface
     */
    public function getAuthenticatedRequest($method, $url, $token, array $options = [])
    {
        $options['headers'] = array('Content-Type' => 'application/json','Accept' => 'application/json');
        return $this->createRequest($method, $url, $token, $options);
    }
}
