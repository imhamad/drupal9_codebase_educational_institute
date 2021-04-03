<?php

namespace Drupal\fivestar\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Render\Element;
use Drupal\fivestar\WidgetManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for Fivestar field formatters.
 */
abstract class FivestarFormatterBase extends FormatterBase {

  /**
   * The widget manager.
   *
   * @var \Drupal\fivestar\WidgetManager
   */
  protected $widgetManager;

  /**
   * Constructs a FivestarFormatterBase object.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\fivestar\WidgetManager $widget_manager
   *   The widget manager.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, WidgetManager $widget_manager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->widgetManager = $widget_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('fivestar.widget_manager')
    );
  }

  /**
   * Prepares the widget's render element for rendering.
   *
   * @param array $element
   *   The element to transform.
   *
   * @return array
   *   The transformed element.
   *
   * @see ::formElement()
   */
  public function previewsExpand(array $element) {
    foreach (Element::children($element) as $css) {
      $vars = [
        '#theme' => 'fivestar_preview_widget',
        '#css' => $css,
        '#name' => mb_strtolower($element[$css]['#title']),
      ];
      $element[$css]['#description'] = \Drupal::service('renderer')->render($vars);
    }

    return $element;
  }

}
