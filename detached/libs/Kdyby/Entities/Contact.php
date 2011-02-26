<?php

namespace Kdyby\Data;

use Doctrine\Common\Collections\ArrayCollection;
use Nette;
use Kdyby;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 * @Entity @Table(name="contacts")
 */
class Contact extends Kdyby\Doctrine\IdentifiedEntity
{

	/** @Column(type="array") */
	private $mobiles = array();

	/** @Column(type="array") */
	private $telephones = array();

	/** @Column(type="array") */
	private $faxes = array();

	/** @Column(type="array") */
	private $email = array();

	/**
     * @ManyToMany(targetEntity="Kdyby\Location\Address")
     * @JoinTable(name="contacts_addresses",
     *      joinColumns={@JoinColumn(name="contact_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="address_id", referencedColumnName="id", unique=true)}
     *      )
     */
    private $addresses;

}