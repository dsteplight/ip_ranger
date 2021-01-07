<?php

namespace Drupal\Tests\ip_ranger\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Test the module settings page
 *
 * @group ip_ranger
 */
class SettingsPageTest {

  public static $modules = [
    'user',
    'ip_ranger',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
  }

}
