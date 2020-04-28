<?php


namespace App\Application\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="event_store")
 */
class Event
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
    private $aggregateId;
    /**
     * @ORM\Column(type="string")
     */
    private $aggregateClass;
    /**
     * @ORM\Column(type="json")
     */
    private $event;
    /**
     * @ORM\Column(type="string")
     */
    private $eventType;
    /**
     * @ORM\Column(type="datetime")
     */
    private $dateCreated;
    /**
     * @ORM\Column(type="integer")
     */
    private $version;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id): void
    {
        $this->id = $id;
    }

    public function getAggregateId()
    {
        return $this->aggregateId;
    }

    public function setAggregateId($aggregateId): void
    {
        $this->aggregateId = $aggregateId;
    }

    public function getAggregateClass()
    {
        return $this->aggregateClass;
    }

    public function setAggregateClass($aggregateClass): void
    {
        $this->aggregateClass = $aggregateClass;
    }

    public function getEvent()
    {
        return $this->event;
    }

    public function setEvent($event): void
    {
        $this->event = $event;
    }

    public function getEventType()
    {
        return $this->eventType;
    }

    public function setEventType($eventType): void
    {
        $this->eventType = $eventType;
    }

    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    public function setDateCreated($dateCreated): void
    {
        $this->dateCreated = $dateCreated;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function setVersion($version): void
    {
        $this->version = $version;
    }

}