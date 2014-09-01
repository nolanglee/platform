<?php

/**
 * Ushahidi Platform Post Data
 *
 * @author     Ushahidi Team <team@ushahidi.com>
 * @package    Ushahidi\Platform
 * @copyright  2014 Ushahidi
 * @license    https://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License Version 3 (AGPL3)
 */

namespace Ushahidi\Usecase\Post;

use Ushahidi\Data;

class PostData extends Data
{
	public $form_id;
	public $user_id;
	//public $user_email;
	//public $user_realname;
	public $title;
	public $slug;
	public $content;
	public $status;
	public $locale;

	// @todo figure out if these should live elsewhere
	public $type;
	public $parent_id;

	public $values = [];
	public $tags = [];


	/**
	 * Compare with some existing data and get the delta between the two.
	 * Only values that were present in the input data will be returned!
	 * @param  Array  $compare  existing data
	 * @return Data
	 */
	public function getDifferent(Array $compare)
	{
		// Get the difference of current data and comparison. If not all properties
		// were defined in input, this will contain false positive (empty) values.
		$base = $this->asArray();
		// Exclude values and tags, since array_diff_assoc can't cope with arrays.
		unset($base['values'], $base['tags'], $compare['values'], $compare['tags']);

		$delta = array_diff_assoc($base, $compare);

		// @todo recursive diff on values and tags
		// For now, just assume they're always updated
		$delta['values'] = $this->values;
		$delta['tags']	 = $this->tags;

		return new static($delta);
	}
}
