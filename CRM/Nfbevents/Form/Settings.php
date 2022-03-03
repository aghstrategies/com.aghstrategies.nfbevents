<?php

use CRM_Nfbevents_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_Nfbevents_Form_Settings extends CRM_Core_Form {
  public function buildQuickForm() {
    // add form elements
    $this->add(
      'select', // field type
      'template_id', // field name
      'Template', // field label
      $this->getTemplates(), // list of options
      TRUE // is required
    )->setSelected(Civi::settings()->get('upcoming_event_message_template_id'));

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => E::ts('Submit'),
        'isDefault' => TRUE,
      ),
    ));

    $this->assign('value', Civi::settings()->get('upcoming_event_message_template_id'));

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  #TODO Why does this not show the correct value after the first submit?
  public function postProcess() {
    \Civi::settings()->set('upcoming_event_message_template_id', $this->getSubmittedValue('template_id'));
    parent::postProcess();
  }

  public function getTemplates() {
    $options = [];
    $templatesResult = \Civi\Api4\MessageTemplate::get()
    ->execute();

    foreach ($templatesResult as $template) {
      $options[$template['id']] = $template['msg_title'];
    }

    return $options;
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  public function getRenderableElementNames() {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons".  These
    // items don't have labels.  We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = array();
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

}
