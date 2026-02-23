<?php
namespace lib\events;

interface EventInterface {}

interface EventSubscriberInterface {
    public static function getSubscribedEvents(): array; // ['event.name' => 'methodName']
}

class Event {
    protected bool $propagationStopped = false;

    public function stopPropagation(): void {
        $this->propagationStopped = true;
    }

    public function isPropagationStopped(): bool {
        return $this->propagationStopped;
    }
}

class EventDispatcher {
    protected array $listeners = [];
    protected array $onceListeners = [];

    public function listen(string $event, callable $callback, int $priority = 0): void {
        $this->listeners[$event][] = ['callback' => $callback, 'priority' => $priority];
        usort($this->listeners[$event], fn($a, $b) => $b['priority'] <=> $a['priority']);
    }

    public function once(string $event, callable $callback, int $priority = 0): void {
        $this->onceListeners[$event][] = ['callback' => $callback, 'priority' => $priority];
        usort($this->onceListeners[$event], fn($a, $b) => $b['priority'] <=> $a['priority']);
    }

    public function addSubscriber(EventSubscriberInterface $subscriber): void {
        foreach ($subscriber::getSubscribedEvents() as $event => $method) {
            $this->listen($event, [$subscriber, $method]);
        }
    }

    public function dispatch(EventInterface|string $event, mixed $payload = null): void {
        $eventName = is_string($event) ? $event : get_class($event);
        $eventObject = is_string($event) ? $payload : $event;

        $listeners = array_merge(
            $this->listeners[$eventName] ?? [],
            $this->onceListeners[$eventName] ?? []
        );

        usort($listeners, fn($a, $b) => $b['priority'] <=> $a['priority']);

        foreach ($listeners as $listener) {
            ($listener['callback'])($eventObject);
            if (
                is_object($eventObject) &&
                method_exists($eventObject, 'isPropagationStopped') &&
                $eventObject->isPropagationStopped()
            ) {
                break;
            }
        }

        // Clear one-time listeners
        unset($this->onceListeners[$eventName]);
    }

    /**
     * Optional: Wildcard support (e.g., "user.*")
     */
    public function dispatchWithWildcard(EventInterface|string $event, mixed $payload = null): void {
        $eventName = is_string($event) ? $event : get_class($event);
        $eventObject = is_string($event) ? $payload : $event;

        $allListeners = array_merge($this->listeners, $this->onceListeners);
        $matched = [];

        foreach ($allListeners as $key => $callbacks) {
            if ($key === $eventName || fnmatch($key, $eventName)) {
                $matched = array_merge($matched, $callbacks);
            }
        }

        usort($matched, fn($a, $b) => $b['priority'] <=> $a['priority']);

        foreach ($matched as $listener) {
            ($listener['callback'])($eventObject);
            if (
                is_object($eventObject) &&
                method_exists($eventObject, 'isPropagationStopped') &&
                $eventObject->isPropagationStopped()
            ) {
                break;
            }
        }

        // Clear one-time wildcard listeners (optional)
        foreach (array_keys($this->onceListeners) as $key) {
            if (fnmatch($key, $eventName)) {
                unset($this->onceListeners[$key]);
            }
        }
    }
}
?>
