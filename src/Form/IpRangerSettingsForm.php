<?php

namespace Drupal\ip_ranger\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

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


    $form['ip_ranger_ipv4'] = [
      '#type' => 'textarea',
      '#title' => $this->t('IPv4'),
      '#description' => $this->t('Paste your valid IPv4 for you network into this field.'),
      '#default_value' => $config->get('ip_ranger_ipv4'),
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

    if ($form_state->isValueEmpty('ip_ranger_ipv4')) {
      $form_state->setErrorByName('ip_ranger_ipv4', $this->t('Please enter a valid ipv4 address.'));
    }
  }

}
