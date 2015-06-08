<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Ushahidi Set Validator
 *
 * @author     Ushahidi Team <team@ushahidi.com>
 * @package    Ushahidi\Application
 * @copyright  2014 Ushahidi
 * @license    https://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License Version 3 (AGPL3)
 */

use Ushahidi\Core\Tool\Validator;
use Ushahidi\Core\Entity\UserRepository;
use Ushahidi\Core\Entity\RoleRepository;

class Ushahidi_Validator_Collection_Post_Create extends Validator
{
	protected $user_repo;
	protected $role_repo;
	protected $default_error_source = 'set';

	protected function getRules()
	{
		return [
			'id' => [
				['not_empty'],
				['digit'],
			],
		];
	}
}
