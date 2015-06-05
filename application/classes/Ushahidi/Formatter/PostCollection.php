<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Ushahidi API Formatter for Collections (aka Sets)
 *
 * Not to be confused with CollectionFormatter
 * (collections of resources)
 *
 * @author     Ushahidi Team <team@ushahidi.com>
 * @package    Ushahidi\Application
 * @copyright  2014 Ushahidi
 * @license    https://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License Version 3 (AGPL3)
 */

use Ushahidi\Core\Traits\FormatterAuthorizerMetadata;
use Ushahidi\Core\SearchData;

class Ushahidi_Formatter_PostCollection extends Ushahidi_Formatter_API
{
	use FormatterAuthorizerMetadata;
}
