<?php

/**
 * Create Set Usecase
 *
 * @author     Ushahidi Team <team@ushahidi.com>
 * @package    Ushahidi\Platform
 * @copyright  2014 Ushahidi
 * @license    https://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License Version 3 (AGPL3)
 */

namespace Ushahidi\Core\Usecase\Set;

use Ushahidi\Core\Entity;
use Ushahidi\Core\Usecase\CreateUsecase;

class CreateSet extends CreateUsecase
{
	protected function getEntity()
	{
		// Start with the payload input
		$payload = $this->payload;

		// If no user information is provided, default to the current session user.
		if (
			empty($payload['user']) &&
			empty($payload['user_id']) &&
			$this->auth->getUserId()
		) {
			$payload['user_id'] = $this->auth->getUserId();
		}

		// Force search to be false, and filter to be empty
		// Sets and Saved Searches share the Set entity
		// and this is what separates them
		// @todo split Entity classes too
		$payload['search'] = 0;
		$payload['filter'] = null;

		// Finally grab a new entity and push the payload into its state
		return $this->repo->getEntity()->setState($payload);
	}
}
