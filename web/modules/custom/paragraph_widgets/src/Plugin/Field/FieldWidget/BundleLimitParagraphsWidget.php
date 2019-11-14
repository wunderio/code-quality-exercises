<?php

namespace Drupal\paragraph_widgets\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\Plugin\Field\FieldWidget\InlineParagraphsWidget;

/**
 * Defines the 'bundle_limit_paragraphs_widget' field widget.
 *
 * @FieldWidget(
 *   id = "bundle_limit_paragraphs_widget",
 *   label = @Translation("Bundle limit paragraphs widget"),
 *   field_types = {
 *     "entity_reference_revisions"
 *   }
 * )
 */


class BundleLimitParagraphsWidget extends InlineParagraphsWidget
{

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array('bundle_limits' => array(), 'bundle_limit_size' => 0) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);
    $settings = $this->getSettings();

    $elements['bundle_limits'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Bundle limits'),
      '#description' => $this->t('Limit how many entities per bundle can be added to field. Type 0 for no limit.'),
    );
    foreach ($this->getAllowedTypes() as $type_name => $type) {
      $bundle_limit = $settings['bundle_limit_size'];
      if (isset($this->getSetting('bundle_limits')[$type_name])) {
        $bundle_limit = $this->getSetting('bundle_limits')[$type_name];
      }
      $elements['bundle_limits'][$type_name] = [
        '#type' => 'number',
        '#title' => $this->t('Item limit for %type', ['%type' => $type['label']]),
        '#default_value' => $bundle_limit,
        '#required' => TRUE,
        '#min' => 0,
      ];
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function formMultipleElements(FieldItemListInterface $items, array &$form, FormStateInterface $form_state) {
    $elements = parent::formMultipleElements($items, $form, $form_state);
    $field_name = $this->fieldDefinition->getName();
    $this->fieldParents = $form['#parents'];
    $field_state = static::getWidgetState($this->fieldParents, $field_name, $form_state);
    // Get all used paragraphs and group them by entity bundle type to determine if limit is reached.
    $types_used = array();
    if (!isset($field_state['paragraphs']))) {
      return $elements;
    }
    foreach ($field_state['paragraphs'] as $paragraph_data) {
      if (!isset($paragraph_data['entity'])) {
        continue
      }
      if (!isset($paragraph_data['mode'])) {
        continue
      }
      if ($paragraph_data['mode'] === 'removed') {
        continue
      }

      if ($paragraph_data['entity'] instanceof Paragraph) {
        $types_used[$paragraph_data['entity']->bundle()][] = $paragraph_data['entity'];
      }
    }
    $settings = $this->getSetting('bundle_limits');
    foreach ($settings as $type_name => $bundle_limit) {
      if ($bundle_limit == 0) {
        continue;
      }
      if (!isset($types_used[$type_name])) {
        continue;
      }
      if (count($types_used[$type_name]) < $bundle_limit) {
        continue;
      }
      if (!isset($elements['add_more'])) {
        continue;
      }
      if (!isset($elements['add_more']['add_more_button_' . $type_name])) {
        continue;
      }
      unset($elements['add_more']['add_more_button_' . $type_name]);
    }

    return $elements;
  }

}
