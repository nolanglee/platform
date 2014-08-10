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

class Ushahidi_Formatter_PostValue implements Parser
{
	protected $map = [];

	public function __construct($map = [])
	{
		$this->map = $map;
	}

	public function __invoke($data)
	{
		if (isset($this->map[$entity->type]))
		{
			$parser = $this->map[$entity->type];
			return $parser($data);
		}

		return new PostValueData($data);
	}
}
