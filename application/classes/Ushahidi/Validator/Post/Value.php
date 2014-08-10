<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Ushahidi Post Value Parser
 *
 *
 * @author     Ushahidi Team <team@ushahidi.com>
 * @package    Ushahidi\Application
 * @copyright  2014 Ushahidi
 * @license    https://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License Version 3 (AGPL3)
 */

class Ushahidi_Validator_Post_Value implements Validator
{
	protected $map = [];

	public function __construct($map = [])
	{
		$this->map = $map;
	}

	public function check(Data $input)
	{
		if (isset($this->map[$entity->type]))
		{
			$validator = $this->map[$entity->type];
			return $validator->check($data);
		}

		return TRUE;
	}
}
