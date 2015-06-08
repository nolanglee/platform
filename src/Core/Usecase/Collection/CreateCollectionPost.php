<?php

/**
 * Add post to Collection Usecase
 *
 * @author     Ushahidi Team <team@ushahidi.com>
 * @package    Ushahidi\Platform
 * @copyright  2014 Ushahidi
 * @license    https://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License Version 3 (AGPL3)
 */

namespace Ushahidi\Core\Usecase\Collection;

use Ushahidi\Core\Entity;
use Ushahidi\Core\Entity\SetRepository;
use Ushahidi\Core\Traits\IdentifyRecords;
use Ushahidi\Core\Traits\VerifyEntityLoaded;
use Ushahidi\Core\Usecase\CreateUsecase;

class CreateCollectionPost extends CreateUsecase
{
	use IdentifyRecords,
		VerifyEntityLoaded,
		SetRepositoryTrait,
		GetCollection,
		AuthorizeCollection;

	// Usecase
	public function interact()
	{
		// First fetch the collection entity
		$collection = $this->getCollectionEntity();

		// ... and verify the collection can be edited by the current user
		$this->verifyCollectionUpdateAuth($collection);

		// ... then verify we have a valid payload
		// @todo this is a bit of a hack to check we have an 'id' in the payload
		$this->verifyValidPayload($this->payload);

		// .. and fetchthe post...
		$post = $this->getEntity();

		// ... verify that the post is visible to the current user
		$this->verifyReadAuth($post);

		// .. add the post to the collection
		$id = $this->setRepo->addPostToSet($collection->id, $post->id);

		// ... and return the formatted post
		return $this->formatter->__invoke($post);
	}

	/**
	 * Find entity based on identifying parameters.
	 *
	 * @return Entity
	 */
	protected function getEntity()
	{
		// Entity will be loaded using the provided id
		$id = $this->payload['id'];

		// ... attempt to load the entity
		$entity = $this->repo->get($id);

		// ... and verify that the entity was actually loaded
		$this->verifyEntityLoaded($entity, compact('id'));

		// ... then return it
		return $entity;
	}

	// @todo original verifyValid method only takes an Entity so renamed
	protected function verifyValidPayload($payload)
	{
		if (!$this->validator->check($payload)) {
			$this->validatorError($this->repo->getEntity());
		}
	}

}
