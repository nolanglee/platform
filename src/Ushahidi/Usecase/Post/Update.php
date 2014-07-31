<?php

/**
 * Ushahidi Platform Admin Post Update Use Case
 *
 * @author     Ushahidi Team <team@ushahidi.com>
 * @package    Ushahidi\Platform
 * @copyright  2014 Ushahidi
 * @license    https://www.gnu.org/licenses/agpl-3.0.html GNU Affero General Public License Version 3 (AGPL3)
 */

namespace Ushahidi\Usecase\Post;

use Ushahidi\Entity\Post;
use Ushahidi\Tool\Validator;
use Ushahidi\Exception\ValidatorException;

class Update
{
	private $repo;
	private $valid;

	private $updated = [];

	public function __construct(UpdatePostRepository $repo, Validator $valid)
	{
		$this->repo  = $repo;
		$this->valid = $valid;
	}

	public function interact(Post $post, PostData $input)
	{
		// We only want to work with values that have been changed
		$update = $input->getDifferent($post->asArray());

		if (!$this->valid->check($update))
			throw new ValidatorException("Failed to validate post", $this->valid->errors());

		// Determine what changes to make in the post
		$this->updated = $update->asArray();

		$this->repo->updatePost($post->id, $this->updated);

		// Reflect the changes in the post
		$post->setData($this->updated);

		return $post;
	}

	public function getUpdated()
	{
		return $this->updated;
	}
}
