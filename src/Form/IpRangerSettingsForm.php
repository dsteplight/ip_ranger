<?php

namespace Drupal\ip_ranger\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\IpUtils;

/**
 * Form to hold Ip Ranger Settings.
 */
class IpRangerSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   *
   * @return array
   *   array contains config file name.
   */
  protected function getEditableConfigNames() {
    return [
      'ip_ranger.settings',
    ];
  }

  /**
   * {@inheritdoc}
   *
   * @return string
   *   string containing thee form ID.
   */
  public function getFormId() {
    return 'ip_ranger_form';
  }

  /**
   * {@inheritdoc}
   *
   * @param array $form
   *   Drupalform object.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Drupal form state.
   *
   * @return object
   *   Returns parent form object.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('ip_ranger.settings');

    $form['ip_ranger_ipv4_cidrs'] = [
      '#type' => 'textarea',
      '#title' => $this->t('IPv4 CIDRS'),
      '#description' => $this->t('Paste your valid IpV4 CIDRs for you network into this field.'),
      '#default_value' => $config->get('ip_ranger_ipv4_cidrs '),
    ];
    $form['ip_ranger_ipv6_cidrs'] = [
      '#type' => 'textarea',
      '#title' => $this->t('IPv6 CIDRS'),
      '#description' => $this->t('Paste your valid IpV6 CIDRs for you network into this field.'),
      '#default_value' => $config->get('ip_ranger_ipv6_cidrs '),
    ];
    $form['ip_ranger_ipv4'] = [
      '#type' => 'textarea',
      '#title' => $this->t('IPv4'),
      '#description' => $this->t('Paste your IPv4 addresses on a different line.'),
      '#default_value' => $config->get('ip_ranger_ipv4'),
    ];
    $form['ip_ranger_ipv6'] = [
      '#type' => 'textarea',
      '#title' => $this->t('IPv6'),
      '#description' => $this->t('Paste your valid IPv6 for you network into this field.'),
      '#default_value' => $config->get('ip_ranger_ipv6'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   *
   * @param array $form
   *   Drupal Formm Object.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Drupal Form State Object.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('arm_treasure.settings')
      ->set('ip_ranger_ipv4', $form_state->getValue('ip_ranger_ipv4'))
      ->save();
  }

  /**
   * Form validation for IP Ranger Configuration Settings.
   *
   * @param array $form
   *   Drupal $form object.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Druapl $form_state object.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $form_state->setErrorByName('ip_ranger_ipv4', $this->t('You have entered at least one invalid IpV4 address.'));

    //get clean array of IPv4 inputs
    $ipv4raw = $form_state->getValue('ip_ranger_ipv4');
    $ipv4s = $this->getArrayOfIps($ipv4raw);

    //get clean array of IPv6 inputs
    $ipv6raw = $form_state->getValue('ip_ranger_ipv6');
    $ipv6s = $this->getArrayOfIps($ipv6raw);

    //get clean array of IPv4 CIDRs
    $ipv4rawcidrs = $form_state->getValue('ip_ranger_ipv4_cidrs');
    $valid_ipv4_cidrs = $this->getArrayOfIps($ipv4rawcidrs);

    //get clean array of IPv6 CIDRs
    $ipv6rawcidrs = $form_state->getValue('ip_ranger_ipv6_cidrs');
    $valid_ipv6_cidrs = $this->getArrayOfIps($ipv6rawcidrs);

    //check if entries are valid IPs
    $valid_ip_v4s = $this->hasValidIps($ipv4s, "FILTER_FLAG_IPV4");
    $valid_ip_v6s = $this->hasValidIps($ipv6s, "FILTER_FLAG_IPV6");

    //check if entries are valid CIDRs
    $valid_ip_v4s_cidrs = $this->hasValidIpCidrs($valid_ipv4_cidrs);
    $valid_ip_v6s_cidrs = $this->hasValidIpCidrs($valid_ipv6_cidrs);

    ksm($valid_ip_v4s_cidrs);
    ksm($valid_ip_v6s_cidrs);

  }

  /**
   * Converts strings from Textarea input into an array of IPs for further validations
   * @param $ips
   * @return array|bool|false|string[]
   */
  private function getArrayOfIps($ips) {
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
  private function hasValidIps($ipvs, $flag) {

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
  private function hasValidIpCidrs($cidrs){

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
  public function validateCidr($cidr)
  {
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

}
