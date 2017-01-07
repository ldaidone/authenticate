<?php


/**
 * JWTAuth
 *
 * @category    Erdiko
 * @package     Authenticate
 * @copyright   Copyright (c) 2016, Arroyo Labs, http://www.arroyolabs.com
 * @author      Andy Armstrong, andy@arroyolabs.com
 */


namespace erdiko\authenticate;

use erdiko\authenticate\traits\ConfigLoader;
use erdiko\authenticate\traits\Builder;

class JWTAuthenticator implements BaseAuthenticator
{

	use ConfigLoader;
	use Builder;

	private $config;
	private $container;
	private $selectedStorage;

	protected $erdikoUser;


	public function __construct(iErdikoUser $user)
	{
		$this->erdikoUser = $user;
		$this->container = new \Pimple\Container();
		$this->config = $this->loadFromJson();
		// Storage
		$this->selectedStorage = $this->config["storage"]["selected"];
		$storage = $this->config["storage"]["storage_types"];
		$this->buildStorages($storage);
		// Authentications
		$authentication = $this->config["authentication"]["available_types"];
		$this->buildAuthenticator($authentication);
	}

	public function persistUser(iErdikoUser $user) { }

	public function current_user()
	{
		try {
			$store = $this->container["STORAGES"][$this->selectedStorage];
			$user  = $store->attemptLoad($this->erdikoUser);
			if(empty($user)) $user = $this->erdikoUser->getAnonymous();

		} catch (\Exception $e) {
			$user = $this->erdikoUser->getAnonymous();
		}
		return $user;
	}

	public function logout()
	{
		try{
			$store = $this->container["STORAGES"][$this->selectedStorage];
			$store->destroy();
		} catch (\Exception $e) {
			throw new \Exception($e->getMessage());
		}
	}

	public function persistUserAndToken($userData)
    {
		try {
			$store = $this->container["STORAGES"][$this->selectedStorage];
			$store->persist($userData);
		} catch (\Exception $e) {
			throw new \Exception($e->getMessage());
		}
    }

	public function login($credentials = array(), $type = 'mock')
    {
        $storage = $this->container["STORAGES"][$this->selectedStorage];

		// checks if it's already logged in
		$user = $storage->attemptLoad($this->erdikoUser);
		if($user instanceof iErdikoUser){
			$this->logout();
        }

		$result = false;
		try {
			$auth = $this->container["AUTHENTICATIONS"][$type];
            $result = $auth->login($credentials);
            $user = $result->user;

			if(!empty($user) && (false !== $user)) {
				$this->persistUser( $user );
				$response = true;
			}
		} catch (\Exception $e) {
			\error_log($e->getMessage());
		}
		return $result;
	}

}
