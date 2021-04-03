<?php

namespace Drupal\Tests\user\Functional;

use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests whether proper language is stored for new users and access to language
 * selector.
 *
 * @group user
 */
class UserLanguageCreationTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['user', 'language'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Functional test for language handling during user creation.
   */
  public function testLocalUserCreation() {
    // User to add and remove language and create new users.
    $admin_user = $this->drupalCreateUser([
      'administer languages',
      'access administration pages',
      'administer users',
    ]);
    $this->drupalLogin($admin_user);

    // Add predefined language.
    $langcode = 'fr';
    ConfigurableLanguage::createFromLangcode($langcode)->save();

    // Set language negotiation.
    $edit = [
      'language_interface[enabled][language-url]' => TRUE,
    ];
    $this->drupalPostForm('admin/config/regional/language/detection', $edit, 'Save settings');
    $this->assertText('Language detection configuration saved.');

    // Check if the language selector is available on admin/people/create and
    // set to the currently active language.
    $this->drupalGet($langcode . '/admin/people/create');
    $this->assertTrue($this->assertSession()->optionExists("edit-preferred-langcode", $langcode)->isSelected());

    // Create a user with the admin/people/create form and check if the correct
    // language is set.
    $username = $this->randomMachineName(10);
    $edit = [
      'name' => $username,
      'mail' => $this->randomMachineName(4) . '@example.com',
      'pass[pass1]' => $username,
      'pass[pass2]' => $username,
    ];

    $this->drupalPostForm($langcode . '/admin/people/create', $edit, 'Create new account');

    $user = user_load_by_name($username);
    $this->assertEqual($langcode, $user->getPreferredLangcode(), 'New user has correct preferred language set.');
    $this->assertEqual($langcode, $user->language()->getId(), 'New user has correct profile language set.');

    // Register a new user and check if the language selector is hidden.
    $this->drupalLogout();

    $this->drupalGet($langcode . '/user/register');
    $this->assertSession()->fieldNotExists('language[fr]');

    $username = $this->randomMachineName(10);
    $edit = [
      'name' => $username,
      'mail' => $this->randomMachineName(4) . '@example.com',
    ];

    $this->drupalPostForm($langcode . '/user/register', $edit, 'Create new account');

    $user = user_load_by_name($username);
    $this->assertEqual($langcode, $user->getPreferredLangcode(), 'New user has correct preferred language set.');
    $this->assertEqual($langcode, $user->language()->getId(), 'New user has correct profile language set.');

    // Test that the admin can use the language selector and if the correct
    // language is saved.
    $user_edit = $langcode . '/user/' . $user->id() . '/edit';

    $this->drupalLogin($admin_user);
    $this->drupalGet($user_edit);
    $this->assertTrue($this->assertSession()->optionExists("edit-preferred-langcode", $langcode)->isSelected());

    // Set passRaw so we can log in the new user.
    $user->passRaw = $this->randomMachineName(10);
    $edit = [
      'pass[pass1]' => $user->passRaw,
      'pass[pass2]' => $user->passRaw,
    ];

    $this->drupalPostForm($user_edit, $edit, 'Save');

    $this->drupalLogin($user);
    $this->drupalGet($user_edit);
    $this->assertTrue($this->assertSession()->optionExists("edit-preferred-langcode", $langcode)->isSelected());
  }

}
