<?php


namespace App\Application\Entity;


use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="contact")
 */
class Contact
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
    private $name;
    /**
     * @ORM\Column(type="string")
     */
    private $vatId;
    /**
     * @ORM\Column(type="string")
     */
    private $accountNumber;
    /**
     * @ORM\OneToMany(targetEntity="Address", mappedBy="contact")
     */
    private $billingAddresses;
}