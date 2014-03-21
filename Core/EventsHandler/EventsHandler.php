<?php
/**
 * This file is part of the RedKiteCmsBunde Application and it is distributed
 * under the MIT License. To use this application you must leave
 * intact this copyright notice.
 *
 * Copyright (c) RedKite Labs <webmaster@redkite-labs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * For extra documentation and help please visit http://www.redkite-labs.com
 *
 * @license    MIT License
 *
 */

namespace RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\EventsHandler;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Exception\General\InvalidArgumentException;
use RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Exception\General\RuntimeException;
use RedKiteLabs\RedKiteCms\RedKiteCmsBundle\Core\Exception\Content\General\InvalidArgumentTypeException;
use Symfony\Component\EventDispatcher\Event;

/**
 * A base EventsHandler object to create the events and dispatch them when required
 *
 * When an event already exists, it is recreated
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 *
 * @api
 */
abstract class EventsHandler implements EventsHandlerInterface
{
    private $events = array();
    private $eventDispatcher;
    private $methods;

    /**
     * Configures the methods that will be evaluated and valorized when a new
     * event is created
     *
     * @api
     */
    abstract protected function configureMethods();

    /**
     * Constructor
     *
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     *
     * @api
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->methods = $this->configureMethods();
    }

    /**
     * {@inheritdoc}
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * Returns the handled events
     *
     * @return array
     *
     * @api
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * {@inheritdoc}
     */
    public function getEvent($eventName)
    {
        return $this->fetchEvent($eventName);
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException
     * @throws InvalidArgumentTypeException
     */
    public function createEvent($eventName, $class, array $args)
    {
        if ( ! is_string($eventName)) {
            $exception = array(
                'message' => '"exception_invalid_argument_provided_for_event_name',
                'parameters' => array(
                    '%className%' => get_class($this),
                ),
            );
            throw new InvalidArgumentException(json_encode($exception));
        }

        if ( ! class_exists($class)) {
            $exception = array(
                'message' => 'exception_invalid_class_name_for_createEvent',
                'parameters' => array(
                    '%argumentClass%' => get_class($this),
                    '%className%' => get_class($this),
                ),
            );
            throw new InvalidArgumentException(json_encode($exception));
        }

        // When the event already exists, it is recreated
        $event = $this->fetchEvent($eventName);
        if (null !== $event) {unset($this->events[$eventName]);}

        $event = new $class();
        if (! $event instanceof Event) {
            $exception = array(
                'message' => 'exception_invalid_class_instance_for_createEvent',
                'parameters' => array(
                    '%argumentClass%' => get_class($this),
                    '%className%' => get_class($this),
                ),
            );
            throw new InvalidArgumentTypeException(json_encode($exception));
        }

        $this->events[$eventName] = $event;

        $numberOfArgs = count($args);
        if ($numberOfArgs == 0) return $this;

        $methods = array_slice($this->methods, 0, $numberOfArgs);
        $callables = array_combine($methods, $args);

        // Valorizes the event's methods
        foreach ($callables as $method => $arg) {
            call_user_func(array($event, $method), $arg);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    public function dispatch($eventName = null)
    {
        // Tries to fetch the event by the given eventname
        $event = null;
        if (null !== $eventName) {
            $event = $this->fetchEvent($eventName);
        }

        // Fetches the last saved event
        if (null === $event && !empty($this->events)) {
            $eventNames = array_keys($this->events);
            $events = array_values($this->events);
            $elemens = count($events) - 1;
            $event = $events[$elemens];
            $eventName = $eventNames[$elemens];
        }

        if (null === $event) {
            throw new RuntimeException('exception_no_events');
        }

        $this->eventDispatcher->dispatch($eventName, $event);

        return $this;
    }

    /**
     * Returns the requested event if exists
     *
     * @param  string                                   $eventName
     * @return \Symfony\Component\EventDispatcher\Event
     */
    protected function fetchEvent($eventName)
    {
        return (array_key_exists($eventName, $this->events)) ? $this->events[$eventName] : null;
    }
}
