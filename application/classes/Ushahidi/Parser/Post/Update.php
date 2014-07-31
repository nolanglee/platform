<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Ushahidi Update Post Parser
 *
 * @author     Ushahidi Team <team@ushahidi.com>
 * @package    Ushahidi\Application
 * @copyright  2014 Ushahidi
 * @license    https://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License Version 3 (AGPL3)
 */

use Ushahidi\Tool\Parser;
use Ushahidi\Exception\ParserException;
use Ushahidi\Usecase\Post\PostData;

class Ushahidi_Parser_Post_Update implements Parser
{
	public function __invoke(Array $data)
	{
		if (empty($data['slug']) AND !empty($data['title']))
		{
			$data['slug'] = URL::title(trim($data['title']));
		}

		if (! empty($data['locale']))
		{
			$data['locale'] = UTF8::strtolower(trim($data['locale']));
		}

		// Unpack form to get form_id
		if (isset($data['form']))
		{
			if (is_array($data['form']) AND isset($data['form']['id']))
			{
				$data['form_id'] = $data['form']['id'];
			}
			elseif (! is_array($data['form']))
			{
				$data['form_id'] = $data['form'];
			}
		}
		// Make form_id a string, avoid triggering 'changed' value
		$data['form_id'] = isset($data['form_id']) ? (String) $data['form_id'] : NULL;

		$valid = Validation::factory($data)
			->rules('slug', array(
					array('not_empty'),
				))
			->rules('type', array(
					array('not_empty'),
				))
			->rules('locale', array(
					array('not_empty'),
				))
			->rules('form_id', array(
					array('not_empty'),
				));


		if (!$valid->check())
		{
			throw new ParserException("Failed to parse post create request", $valid->errors('tag'));
		}

		// Ensure that all properties of a Tag entity are defined by using Arr::extract
		return new PostData(Arr::extract($data, ['form_id', 'title', 'content', 'status', 'slug', 'locale']));
	}
}
