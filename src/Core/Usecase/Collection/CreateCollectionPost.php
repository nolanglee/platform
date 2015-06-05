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
use Ushahidi\Core\Usecase\CreateUsecase;

class CreateCollectionPost extends CreateUsecase
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
		// Get payload input
		$payload = $this->payload;

		// Fetch and hydrate the entity...
		$entity = $this->getEntity();

		// ... verify that the entity can be created by the current user
		$this->verifyCreateAuth($entity);

		// ... verify that the entity is in a valid state
		$this->verifyValidPayload($payload);

		// ... add post to set
		$id = $this->set_repo->addPostToSet($payload['set_id'], $payload['post_id']);

		// ... get the newly created entity
		$entity = $this->getCreatedEntity($payload['post_id']);

		// ... verify that the entity can be read by the current user
		$this->verifyReadAuth($entity);

		// ... and return the formatted entity
		return $this->formatter->__invoke($entity);
	}

	protected function verifyValidPayload(array $payload)
	{
		if (!$this->validator->check($payload)) {
			$this->validatorError($this->getEntity());
		}
	}

}
