<?php

/**
 * Remove post from collection Use Case
 *
 * @author     Ushahidi Team <team@ushahidi.com>
 * @package    Ushahidi\Platform
 * @copyright  2014 Ushahidi
 * @license    https://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License Version 3 (AGPL3)
 */

namespace Ushahidi\Core\Usecase\Collection;

use Ushahidi\Core\Usecase\DeleteUsecase;
use Ushahidi\Core\Data;
use Ushahidi\Core\Tool\ValidatorTrait;
use Ushahidi_Repository;
use Ushahidi\Core\Entity;
use Ushahidi\Core\Entity\SetRepository;

class DeleteCollectionPost extends DeleteUsecase
{
	protected $set_repo;

	public function setSetRepository(SetRepository $set_repo)
	{
		$this->set_repo = $set_repo;
		return $this;
	}

	// Usecase
	public function interact()
	{
		// Fetch the entity, using provided identifiers...
		$entity = $this->getEntity();

		// ... verify that the entity can be deleted by the current user
		$this->verifyDeleteAuth($entity);

		$post_id = $this->getIdentifier('id');
		$set_id  = $this->getIdentifier('set_id');

		// ... persist the delete
		$this->set_repo->deleteSetPost($set_id, $post_id);

		// ... and return the formatted entity
		return $this->formatter->__invoke($entity);
	}

	/**
	 * Find entity based on identifying parameters.
	 *
	 * @return Entity
	 */
	protected function getEntity()
	{
		// Entity will be loaded using the provided id
		$id = $this->getRequiredIdentifier('id');

		// ... attempt to load the entity
		$entity = $this->repo->get($id);

		// ... and verify that the entity was actually loaded
		$this->verifyEntityLoaded($entity, compact('id'));

		// ... then return it
		return $entity;
	}

}
