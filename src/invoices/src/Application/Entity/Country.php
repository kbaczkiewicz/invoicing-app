<?php


namespace App\Application\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="country")
 */
class Country
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
    private $isoCode;
    /**
     * @ORM\Column(type="string", length=3)
     */
    private $currency;
    /**
     * @ORM\ManyToOne(targetEntity="AppUser", inversedBy="countries")
     * @ORM\JoinColumn(nullable=false)
     */
    private $owner;

    public static function createFromModel(\App\Domain\Country\Model\Country $country): self
    {
        $self = new self();
        $self->setFieldsByModel($country, $self);

        return $self;
    }

    public function editByModel(\App\Domain\Country\Model\Country $country)
    {
        return $this->setFieldsByModel($country, $this);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getIsoCode()
    {
        return $this->isoCode;
    }

    private function setFieldsByModel(\App\Domain\Country\Model\Country $country, self $self)
    {
        $self->id = $country->getId();
        $self->name = $country->getName();
        $self->currency = $country->getCurrency();
        $self->isoCode = $country->getIsoCode() ? $country->getIsoCode()->get() : null;

        return $self;
    }

    public function getOwner(): ?AppUser
    {
        return $this->owner;
    }

    public function setOwner(?AppUser $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }
}