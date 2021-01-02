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

/**
 * Class IpRangerSubscriber.
 */
class IpRangerSubscriber implements EventSubscriberInterface {

  /**
   * Constructs a new IpRangerSubscriber object.
   */
  public function __construct() {

  }

  /**
   * Gets the User's IP Address
   * @param GetResponseEvent $event
   */
  public function checkUserIp(GetResponseEvent $event) {
    //The method to determine the user's IP may different from across environments and if VPN/CDN is used.
    //use a switch statement that defaults to getClientIp() method
    $ip = $event->getRequest()->getClientIp();

    //TODO: Logic to get User ID may need to be it's own Service
    $current_user = \Drupal::currentUser();
    $user = \Drupal\user\Entity\User::load($current_user->id());
    $uid = $user->id();

    $known_ipv4 = \Drupal::config('ip_ranger.settings')->get('ip_ranger_ipv4');
    $known_ipv6 = \Drupal::config('ip_ranger.settings')->get('ip_ranger_ipv6');
    $known_ipv4_cidrs = \Drupal::config('ip_ranger.settings')->get('ip_ranger_ipv4_cidrs');
    $known_ipv6_cidrs = \Drupal::config('ip_ranger.settings')->get('ip_ranger_ipv6_cidrs');

    $ip_service = \Drupal::service('ip_ranger.iputils');
    $ip4_array = $ip_service::getArrayOfIps($known_ipv4);
    $ip6_array = $ip_service::getArrayOfIps($known_ipv6);
    $ip4_cidrs_array = $ip_service::getArrayOfIps($known_ipv4_cidrs);
    $ip6_cidrs_array = $ip_service::getArrayOfIps($known_ipv6_cidrs);

    //check if ip is in array
    $network_status_array = [];
    $network_status_array['ipv4'] = (in_array($ip, $ip4_array))?1:0;
    $network_status_array['ipv6'] = (in_array($ip, $ip6_array))?1:0;

    //check to see if IP address are in the given CIDR range
    $ip_v4_cidr_matches = $ip_service::isIpInCidrRange($ip, $ip4_cidrs_array);
    $ip_v6_cidr_matches = $ip_service::isIpInCidrRange($ip, $ip6_cidrs_array);

    //count($array) should return empty when there is a match or populate array with matched ranges
    $network_status_array['ipv4cidr'] = (!count($ip_v4_cidr_matches))?1:0;
    $network_status_array['ipv6cidr'] = (!count($ip_v6_cidr_matches))?1:0;

    $in_network_status = "FALSE";
    if( in_array(1, $network_status_array)) {
      $in_network_status = "TRUE";
    }

    $request = \Drupal::request();
    $session = $request->getSession();
    $session->set('ip_ranger_is_in_network', $in_network_status);

    //current network status
    $ip_network_status = $session->get('ip_ranger_is_in_network');
    //todo: refactor IpRangerSettingsForms.php validation helper functions to utilize Custom/IpUtils helper functions

  }


  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = array('checkUserIp');
    return $events;
  }

}
