<?php

/**
 * Create Set Usecase
 *
 * @author     Ushahidi Team <team@ushahidi.com>
 * @package    Ushahidi\Platform
 * @copyright  2014 Ushahidi
 * @license    https://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License Version 3 (AGPL3)
 */

namespace Ushahidi\Core\Usecase\Collection;

use Ushahidi\Core\Entity;
use Ushahidi\Core\Usecase\CreateUsecase;

class CreateCollection extends CreateUsecase
{
	protected function getEntity()
	{
		$payload = $this->payload;

		// If no user information is provided, default to the current session user.
		if (
			empty($payload['user']) &&
			empty($payload['user_id']) &&
			$this->auth->getUserId()
		) {
			$payload['user_id'] = $this->auth->getUserId();
		}
		
		$payload['search'] = 0;
		$payload['filter'] = null;

		return $this->repo->getEntity()->setState($payload);
	}
}
