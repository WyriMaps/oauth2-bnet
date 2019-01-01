<?php

namespace WyriMaps\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;
use WyriMaps\OAuth2\Client\Entity\User;

abstract class BattleNet extends AbstractProvider
{

    /**
     * The game we wish to query. Defaults to SC2. Available options are:
     *  * sc2
     *  * wow
     * @var string
     */
    protected $game;

    /**
     * The Battle.net region we wish to query on. Available options are:
     *  * us
     *  * eu
     *  * kr
     *  * tw
     *  * cn
     *  * sea (sc2-only!)
     *
     * @var string
     */
    protected $region = "us";

    const ACCESS_TOKEN_RESOURCE_OWNER_ID = 'accountId';

    public function __construct(array $options = [ ], array $collaborators = [ ])
    {
        parent::__construct($options, $collaborators);

        // We need to validate some data to make sure we haven't constructed in an illegal state.
        if (!in_array($this->game, [ "sc2", "wow"])) {
            throw new \InvalidArgumentException("Game must be either sc2 or wow, given: {$this->game}");
        }

        $availableRegions = [ "us", "eu", "kr", "tw", "cn", "sea" ];
        if (!in_array($this->region, $availableRegions)) {
            $regionList = implode(", ", $availableRegions);
            throw new \InvalidArgumentException("Region must be one of: {$regionList}, given: {$this->region}");
        }

        if ($this->region == "sea" && $this->game != "sc2") {
            throw new \InvalidArgumentException("sea region is only available for sc2");
        }
    }


    protected function getScopeSeparator()
    {
        return " ";
    }

    public function getBaseAuthorizationUrl()
    {
        return "https://{$this->region}.battle.net/oauth/authorize";
    }

    public function getBaseAccessTokenUrl(array $params)
    {
        return "https://{$this->region}.battle.net/oauth/token";
    }

    protected function getDefaultScopes()
    {
        return [
            "{$this->game}.profile"
        ];
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
        if ($response->getStatusCode() != 200) {
            $data = (is_array($data)) ? $data : json_decode($data, true);
            throw new IdentityProviderException($data['error_description'], $response->getStatusCode(), $data);
        }
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return "https://{$this->region}.battle.net/oauth/userinfo?access_token={$token}";
    }

    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new User((array)($response), $this->region);
    }
}
