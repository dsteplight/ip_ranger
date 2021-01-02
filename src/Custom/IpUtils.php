<?php

namespace Drupal\ip_ranger\Custom;

/**
 * Class IpUtils
 * Custom Class to handle filters/validating strings/arrays of IP formats
 * @package Drupal\ip_ranger\Custom
 */
class IpUtils {

  //todo: pass in Drupal logger as an argument
  public function __construct()
  {
  }

  /**
   * Converts strings from Textarea input into an array of IPs for further validations
   * @param $ips
   * @return array|bool|false|string[]
   */
  public static function getArrayOfIps($ips) {
    if(!is_string($ips)){
      return false;
    }

    $ips_with_new_lines = str_replace(" ", "\n", $ips ) ;
    $ip_array = explode("\n", $ips_with_new_lines );
    $ip_array = array_map('trim', $ip_array);
    $ips = array_filter($ip_array);
    return $ips;
  }

  /**
   * Return true for good IPv4 addressses or list of bad ones for error messages.
   * @param $ipv4s
   * @return bool
   */
  public function hasValidIps($ipvs, $flag) {

    foreach ($ipvs as $ipvvalue) {

      //loop throgh IPs and collect the bad ones
      switch ($flag) {
        case  'FILTER_FLAG_IPV4':
          if( !filter_var($ipvvalue, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            //return these IP address for error message
            $bad_ip_vs[] = $ipvvalue;
          }
          break;
        case 'FILTER_FLAG_IPV6':
          if( !filter_var($ipvvalue, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            //return these IP address for error message
            $bad_ip_vs[] = $ipvvalue;
          }
          break;
        default:
          break;
      }

    }

    //return true if all IPs are good or an array of bad IP entries
    if(empty($bad_ip_vs)) {
      return true;
    }
    else{
      return $bad_ip_vs;
    }

  }

  /**
   * Return true if incoming cidrs are all good or returns an array of bad entries.
   * @param $cidrs
   * @return bool
   */
  public function hasValidIpCidrs($cidrs){

    foreach ($cidrs as $ipvvalue) {

      $validResult =   $this->validateCidr($ipvvalue);
      if($validResult === false){
        $bad_ip_vs[] = $ipvvalue;
      }
    }

    if(empty($bad_ip_vs)) {
      return true;
    }
    else{
      return $bad_ip_vs;
    }
  }

  /**
   * Validates if a proper CIDR notation is being uses.
   * https://gist.github.com/mdjekic/ac1f264e37bddfc63be8a042ced52e64
   * @param $cidr
   * @return bool
   */
  public function validateCidr($cidr){
    $parts = explode('/', $cidr);
    if(count($parts) != 2) {
      return false;
    }

    $ip = $parts[0];
    $netmask = intval($parts[1]);

    if($netmask < 0) {
      return false;
    }

    if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
      return $netmask <= 32;
    }

    if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
      return $netmask <= 128;
    }

    return false;
  }

  public function getIpAddress() {
    //todo: insert logic to get IP address
  }

  /**
   * Check to see if an IP address is a match for an array of CIDRs
   * @param $ip
   * @param $cidr_ranges
   * @return array
   */
  public static function isIpInCidrRange($ip, $cidr_ranges)
  {

    //catch any bad IP ranges for reporting purposes
    $bad_range_matches = [];

    foreach ($cidr_ranges as $ip_range) {
      $result = \Dxw\CIDR\IP::contains($ip_range, $ip);
      if ($result->isErr()) {
        // handle the error
        //TODO: log error to the DB
      }

      $match = $result->unwrap();

      //only catch bad matches
      if (!$match) {
        $bad_range_matches[] = $ip_range;
      }
    }

    return $bad_range_matches;
  }
}
