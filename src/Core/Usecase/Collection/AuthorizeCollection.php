<?php

/**
 * Ushahidi Platform Get Collection for Collection/Post Usecase
 *
 * @author     Ushahidi Team <team@ushahidi.com>
 * @package    Ushahidi\Platform
 * @copyright  2014 Ushahidi
 * @license    https://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License Version 3 (AGPL3)
 */

namespace Ushahidi\Core\Usecase\Collection;

use Ushahidi\Core\Data;
use Ushahidi\Core\Entity;
use Ushahidi\Core\Tool\Authorizer;

use Ushahidi\Core\Exception\AuthorizerException;

trait AuthorizeCollection
{
	/**
	 * @var Authorizer
	 */
	protected $setAuth;

	/**
	 * @param  Authorizer $auth
	 * @return void
	 */
	public function setSetAuthorizer(Authorizer $auth)
	{
		$this->setAuth = $auth;
		return $this;
	}

	/**
	 * Verifies the current user is allowed $privilege on $entity
	 *
	 * @param  Entity  $entity
	 * @param  String  $privilege
	 * @return void
	 * @throws AuthorizerException
	 */
	protected function verifyCollectionAuth(Entity $entity, $privilege)
	{
		if (!$this->setAuth->isAllowed($entity, $privilege)) {
			throw new AuthorizerException(sprintf(
				'User %d is not allowed to %s resource %s #%d',
				$this->auth->getUserId(),
				$privilege,
				$entity->getResource(),
				$entity->getId()
			));
		}
	}

	/**
	 * Verifies the current user is allowed update access on $entity
	 *
	 * @param  Entity  $entity
	 * @param  Data    $input
	 * @return void
	 * @throws AuthorizerException
	 */
	protected function verifyCollectionUpdateAuth(Entity $entity)
	{
		$this->verifyCollectionAuth($entity, 'update');
	}

}
