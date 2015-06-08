<?php

/**
 * Ushahidi Platform Verify Collection Exists for Usecase
 *
 * @author     Ushahidi Team <team@ushahidi.com>
 * @package    Ushahidi\Platform
 * @copyright  2014 Ushahidi
 * @license    https://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License Version 3 (AGPL3)
 */

namespace Ushahidi\Core\Usecase\Collection;

use Ushahidi\Core\Entity;

trait verifyCollectionExists
{

	/**
	 * Checks that the collection exists.
	 * @param  Data $input
	 * @return void
	 */
	protected function verifyCollectionExists()
	{
		// Ensure that the collection exists.
		$collection = $this->getSetRepository()->get($this->getRequiredIdentifier('set_id'));
		$this->verifyEntityLoaded($collection, $this->identifiers);
	}

	// Usecase
	public function interact()
	{
		$this->verifyCollectionExists();
		return parent::interact();
	}

	// IdentifyRecords
	abstract protected function getRequiredIdentifier($name);

	// VerifyEntityLoaded
	abstract protected function verifyEntityLoaded(Entity $entity, $lookup);

	// SetRepositoryTrait
	abstract public function getSetRepository();
}
