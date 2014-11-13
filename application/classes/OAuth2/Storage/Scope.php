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

use League\OAuth2\Server\Storage\ScopeInterface;

class OAuth2_Storage_Scope extends OAuth2_Storage implements ScopeInterface
{
	/**
	 * Return information about a scope
	 *
	 * Example SQL query:
	 *
	 * <code>
	 * SELECT * FROM oauth_scopes WHERE scope = :scope
	 * </code>
	 *
	 * Response:
	 *
	 * <code>
	 * Array
	 * (
	 *     [id] => (int) The scope's ID
	 *     [scope] => (string) The scope itself
	 *     [name] => (string) The scope's name
	 *     [description] => (string) The scope's description
	 * )
	 * </code>
	 *
	 * @param  string     $scope     The scope
	 * @param  string     $grantType The grant type used in the request (default = "null")
	 * @param  string     $clientId  The client ID (default = "null")
	 * @return \League\OAuth2\Server\Entity\ScopeEntity
	 */
	public function get($scope, $grantType = null, $clientId = null)
	{
		// NOTE: this implementation does not implement any grant type checks!

		$where = array(
			'scope' => $scope,
			);

		$query = $this->select('oauth_scopes', $where);
		$result = $this->select_one_result($query);

		if (!$result) {
            return null;
        }

        return (new ScopeEntity($this->server))->hydrate([
            'id'            =>  $result['id'],
            'description'   =>  $result['name'],
        ]);
	}
}
