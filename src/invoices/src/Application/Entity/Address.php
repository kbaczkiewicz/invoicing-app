<?php


namespace App\Application\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="address")
 */
class Address
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="string")
     */
    private $id;
    /**
     * @ORM\Column(type="string")
     */
    private $city;
    /**
     * @ORM\Column(type="string")
     */
    private $postCode;
    /**
     * @ORM\Column(type="string")
     */
    private $streetName;
    /**
     * @ORM\Column(type="string")
     */
    private $buildingNumber;
    /**
     * @ORM\Column(type="string")
     */
    private $apartmentNumber;
    /**
     * @ORM\ManyToOne(targetEntity="Contact", inversedBy="BillingAddresses")
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="id")
     */
    private $contact;
    /**
     * @ORM\ManyToOne(targetEntity="Country")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id")
     */
    private $country;
}