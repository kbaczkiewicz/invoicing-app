<?php


namespace App\Domain;


use App\Domain\Event;
use App\Domain\Validation\Validatable;

abstract class AggregateRoot
{
    /**
     * @var Event[]
     */
    protected $events;
    protected $id;

    protected function __construct() {}

    public function getId()
    {
        return $this->id;
    }

    public static function replay(array $events): AggregateRoot
    {
        $className = get_called_class();
        if (self::class === $className) {
            throw new \LogicException('Cannot instantiate abstract Aggregate Root');
        }

        $obj = new $className();
        foreach ($events as $key => $event) {
            $obj->apply($event, false);
        }

        return $obj;
    }

    public function getRecordedEvents(): array
    {
        return $this->events;
    }

    protected function apply($event)
    {
        $fqn = explode('\\', get_class($event));
        $methodName = 'apply'.end($fqn);
        if (method_exists($this, $methodName)) {
            $this->$methodName($event);

            return;
        }

        throw new \LogicException('Cannot apply event ' . end($fqn));
    }

    protected function record($event)
    {
        $this->events[] = $event;
    }

    abstract public function getAggregateName(): string;
}
