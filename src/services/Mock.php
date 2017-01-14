<?php
/**
 * Mock
 *
 * @package     erdiko/authenticate/services
 * @copyright   Copyright (c) 2017, Arroyo Labs, http://www.arroyolabs.com
 * @author      Leo Daidone, leo@arroyolabs.com
 */

namespace erdiko\authenticate\services;

class Mock implements \erdiko\authenticate\AuthenticationInterface
{
	public function login($credentials)
	{
		/**
		 * this is a mock login, I will fake users for testing purpose
		 */
		$user = new MyErdikoUser();
		switch ($credentials['username']) {
			case "foo@mail.com":
				$user->setUsername('foo');
				$user->setDisplayName('Foo');
				$user->setUserId(1);
				$user->setRoles(array("client"));
				break;
			case "bar@mail.com":
				$user->setUsername('bar');
				$user->setDisplayName('Bar');
				$user->setUserId(2);
				$user->setRoles(array("admin"));
				break;
		}
		return $user;
	}
}

