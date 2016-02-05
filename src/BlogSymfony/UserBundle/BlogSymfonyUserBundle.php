<?php

namespace BlogSymfony\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class BlogSymfonyUserBundle extends Bundle
{
	public function getParent(){
		return 'FOSUserBundle';
	}
}
