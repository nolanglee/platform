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

use League\OAuth2\Server\Entity\AbstractTokenEntity;
use League\OAuth2\Server\Entity\AccessTokenEntity;
use League\OAuth2\Server\Entity\ScopeEntity;
use League\OAuth2\Server\Storage\AccessTokenInterface;
use League\OAuth2\Server\Storage\Adapter;

class OAuth2_Storage_AccessToken extends OAuth2_Storage implements AccessTokenInterface
{
    /**
     * {@inheritdoc}
     */
    public function get($token)
    {
		$query = $this->select('oauth_access_tokens', ['access_token' => $token]);
		$result = $this->select_one_result($query);

        if ($result) {
            $token = (new AccessTokenEntity($this->server))
                        ->setId($result['access_token'])
                        ->setExpireTime($result['expire_time']);

            return $token;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getScopes(AbstractTokenEntity $token)
    {
		$query = DB::query(Database::SELECT, '
		SELECT oauth_scopes.id, oauth_scopes.description
		  FROM oauth_access_token_scopes, oauth_scopes
		 WHERE oauth_access_token_scopes.scope = oauth_scopes.id
		   AND access_token = :accessToken');

		$query->param(':accessToken', $token->getId());

 		$result = $this->select_results($query);

        $response = [];

        if ($result and sizeof($result) > 0) {
            foreach ($result as $row) {
                $scope = (new ScopeEntity($this->server))->hydrate([
                    'id'            =>  $row['id'],
                    'description'   =>  $row['description'],
                ]);
                $response[] = $scope;
            }
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function create($token, $expireTime, $sessionId)
    {
		return $this->insert('oauth_access_tokens', [
                        'access_token'  =>  $token,
                        'session_id'    =>  $sessionId,
                        'expire_time'   =>  $expireTime,
                    ]);
    }

    /**
     * {@inheritdoc}
     */
    public function associateScope(AbstractTokenEntity $token, ScopeEntity $scope)
    {
		return $this->insert('oauth_access_token_scopes', [
                        'access_token'  =>  $token->getId(),
                        'scope' 		=>  $scope->getId(),
                    ]);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(AbstractTokenEntity $token)
    {		 
    	$this->_delete('oauth_access_token_scopes', ['access_token' => $token->getId()]);
    }
}
