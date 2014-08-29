<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Ushahidi Post Geometry Repository
 *
 * @author     Ushahidi Team <team@ushahidi.com>
 * @package    Ushahidi\Application
 * @copyright  2014 Ushahidi
 * @license    https://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License Version 3 (AGPL3)
 */

use Ushahidi\Entity\PostValue;
use Ushahidi\Entity\PostValueRepository;

class Ushahidi_Repository_PostGeometry extends Ushahidi_Repository_PostValue
{
	// Ushahidi_Repository
	protected function getTable()
	{
		return 'post_geometry';
	}

	// Override selectQuery to fetch 'value' from db as text
	protected function selectQuery(Array $where = [])
	{
		$query = parent::selectQuery($where);

		// Get geometry value as text
		$query->select(
				$this->getTable().'.*',
				// Fetch AsText(value) aliased to value
				[DB::expr('AsText(value)'), 'value']
			);

		return $query;
	}

	// Override createValue to save 'value' using GeomFromText
	public function createValue($value, $form_attribute_id, $post_id)
	{
		$input = compact('form_attribute_id', 'post_id');
		$input['value'] = DB::expr('GeomFromText(:text)')->param(':text', $value);
		$input['created'] = time();

		return $this->insert($input);
	}

	// Override updateValue to save 'value' using GeomFromText
	public function updateValue($id, $value, $form_attribute_id, $post_id)
	{
		$update = [
			'value' => DB::expr('GeomFromText(:text)')->param(':text', $value)
		];
		if ($id && $update)
		{
			$this->update(compact('id', 'post_id', 'form_attribute_id'), $update);
		}
		return $this->get($id);
	}

}
