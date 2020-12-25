<?php

namespace Drupal\ip_ranger\EventSubscriber;

// This is the interface we are going to implement.
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;

// This class contains the event we want to subscribe to.
use Symfony\Component\HttpKernel\KernelEvents;
// Our event listener method will receive one of these.
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;

// use Drupal\Core\Messenger\MessengerInterface;

/**
 * Class IpRangerSubscriber.
 */
class IpRangerSubscriber implements EventSubscriberInterface {

  /**
   * Constructs a new IpRangerSubscriber object.
   */
  public function __construct() {

  }

  public function checkUserIp(GetResponseEvent $event) {
   // \Drupal::logger('ip_ranger')->log("Debug Test Log In Subscriber");
     $ip = $event->getRequest()->getClientIp();
    \Drupal::messenger()->addMessage($ip);


    /*
    if ($event->getRequest()->query->get('redirect-me')) {
      $event->setResponse(new RedirectResponse('http://example.com/'));
    }
    */
  }


  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = array('checkUserIp');
    return $events;
  }

}
