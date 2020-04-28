<?php


namespace App\Application\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="position")
 */
class Position
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
     * @ORM\Column(type="float")
     */
    private $quantity;
    /**
     * @ORM\Column(type="float")
     */
    private $priceNett;
    /**
     * @ORM\Column(type="float")
     */
    private $vatRate;
}