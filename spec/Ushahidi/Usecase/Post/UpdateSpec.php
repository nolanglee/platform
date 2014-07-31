<?php

namespace spec\Ushahidi\Usecase\Post;

use Ushahidi\Tool\Validator;
use Ushahidi\Entity\Post;
use Ushahidi\Usecase\Post\UpdatePostRepository;
use Ushahidi\Usecase\Post\PostData;

use PhpSpec\ObjectBehavior;

class UpdateSpec extends ObjectBehavior
{
	function let(UpdatePostRepository $repo, Validator $valid)
	{
		$this->beConstructedWith($repo, $valid);
	}

	function it_is_initializable()
	{
		$this->shouldHaveType('Ushahidi\Usecase\Post\Update');
	}

	function it_can_update_a_post_with_valid_input($valid, $repo, Post $post, PostData $input, PostData $update)
	{
		$raw_post   = ['title' => 'Before Update', 'content' => 'Some content'];
		$raw_input  = ['title' => 'After Update', 'content' => 'Some content'];
		$raw_update = ['title' => 'After Update'];

		$post->asArray()->willReturn($raw_post);
		$input->asArray()->willReturn($raw_input);
		$update->asArray()->willReturn($raw_update);

		// the update will be what is different in the input, as compared to the tag
		$input->getDifferent($raw_post)->willReturn($update);

		// only the changed values will be validated
		$valid->check($update)->willReturn(true);

		// the repo will only receive changed values
		$repo->updatePost($post->id, $raw_update)->shouldBeCalled();

		// the persisted changes will be applied to the tag
		$post->setData($raw_update)->shouldBeCalled();

		// after being updated, the same tag will be returned
		$this->interact($post, $input)->shouldReturn($post);
	}
}
