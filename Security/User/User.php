<?php

namespace Kdyby;

use Doctrine\Common\Collections\ArrayCollection;
use Kdyby;
use Kdyby\Security\Acl\Role;
use Nette;
use Nette\Security\IIdentity;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 * @Entity @Table(name="users")
 *
 * @property-read int $id
 * @property string $username
 */
class Identity extends Kdyby\Person implements IIdentity
{

    /** @Column(type="string", length=50) */
    private $username;

	/** @Column(type="string", length=32) */
	private $passwordHash;

	/** @Column(type="datetime") */
	private $registeredAt;

	/** @Column(type="array") @var Doctrine\Common\Collections\ArrayCollection */
	private $roles;



	public function __construct($username, $password)
	{
		parent::__construct();

		$this->roles = ArrayCollection();
		$this->username = $username;
		$this->passwordHash = $this->cryptPassword($password);
	}

	public function getUsername() { return $this->username; }
	public function setUsername($username) { $this->username = $username; }

	public function &getRegisteredAt() { return $this->registeredAt; }
	public function setRegisteredAt(\DateTime $date) { $this->registeredAt = $date; }

	public function getRoles() { return $this->roles; }
	public function addRole(Role $role) { $this->roles->add($role); }
	public function removeRole(Role $role) { $this->roles->removeElement($role); }


	public function isValidPassword($password)
	{
		return $this->passwordHash === $this->cryptPassword($password);
	}


	public function cryptPassword($password)
	{
		return sha1($password);
	}

}