<?php

namespace Depotwarehouse\OAuth2\Client\Provider;

use Depotwarehouse\OAuth2\Client\Entity\WowUser;
use League\OAuth2\Client\Token\AccessToken;

class WowProvider extends BattleNet
{
    protected $game = "wow";
}
