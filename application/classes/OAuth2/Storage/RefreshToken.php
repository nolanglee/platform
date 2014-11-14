<?php defined('SYSPATH') or die('No direct script access');
/**
 * OAuth2 Storage for Sessions
 *
 * License is MIT, to be more compatible with PHP League.
 *
 * @author     Ushahidi Team <team@ushahidi.com>
 * @package    Ushahidi\OAuth2
 * @copyright  2014 Ushahidi
 * @license    http://mit-license.org/
 * @link       http://github.com/php-loep/oauth2-server
 */

use League\OAuth2\Server\Storage\ClientInterface;
use League\OAuth2\Server\Entity\SessionEntity;
use League\OAuth2\Server\Entity\ClientEntity;

class OAuth2_Storage_RefreshToken extends OAuth2_Storage implements ClientInterface
{
    /**
     * {@inheritdoc}
     */
    public function get($token)
    {
        $query = $this->select('oauth_refresh_tokens', ['refresh_token'=>$token]);
        $result = $this->select_one_result($query);

        if ($result) {
            $token = (new RefreshTokenEntity($this->server))
                        ->setId($result['refresh_token'])
                        ->setExpireTime($result['expire_time'])
                        ->setAccessTokenId($result['access_token']);

            return $token;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function create($token, $expireTime, $accessToken)
    {
        return $this->insert('oauth_refresh_tokens', [
                        'refresh_token' =>  $token,
                        'access_token'  =>  $accessToken,
                        'expire_time'   =>  $expireTime,
                    ]);

    }

    /**
     * {@inheritdoc}
     */
    public function delete(RefreshTokenEntity $token)
    {
        $this->delete('oauth_session_refresh_tokens', ['refresh_token' => $token->getId()]);
    }
}
