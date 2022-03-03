<?php

require_once 'nfbevents.civix.php';
// phpcs:disable
use CRM_Nfbevents_ExtensionUtil as E;
// phpcs:enable


/**
 * Implements hook_civicrm_postProcess().
 *
 * @param string $formName
 * @param CRM_Core_Form $form
 */
function nfbevents_civicrm_postProcess($formName, $form) {
  if ($formName === 'CRM_Event_Form_Registration_Confirm' && $form->_values['event']['id'] == 479) { //NFB 479, dev 7
    $participantID = $form->_values['participant']['id'];
    if ($form->_values['params'][$participantID]['custom_906'][1] == 1) { // NFB 906, dev 11
      nfbevents_send_upcoming_mail((int) $participantID, 124); //NOPBC 124, dev 69
    }
    if ($form->_values['params'][$participantID]['custom_907'][1] == 1) { //NFB 907, dev 12
      nfbevents_send_upcoming_mail((int) $participantID, 122); // Career Fair 122, dev 70
    }
    if ($form->_values['params'][$participantID]['custom_922'][1] == 1) { // NFB 922, dev 13
      nfbevents_send_upcoming_mail((int) $participantID, 123); //NFB Camp 123, dev 71
    }
  }
}

/**
 * Send the email.
 *
 * @param int $participantID The ID of the event participant.
 */
function nfbevents_send_upcoming_mail($participantID, $templateID) {
  $contact = nfbevents_get_contact_from_participant($participantID);

  $templateResponse = Civi\Api4\MessageTemplate::get()
    ->addWhere('id', '=', $templateID)
    ->execute();

  if (!count($templateResponse)) {
    return;
  }

  $template = $templateResponse[0];

  $knownSubjectTokens = CRM_Utils_Token::getTokens($template['msg_subject']);
  $processedSubject = CRM_Utils_Token::replaceContactTokens($template['msg_subject'], $contact, FALSE, $knownSubjectTokens);

  $knownHTMLTokens = CRM_Utils_Token::getTokens($template['msg_html']);
  $knownTextTokens = CRM_Utils_Token::getTokens($template['msg_text']);

  $processedHTML = CRM_Utils_Token::replaceContactTokens($template['msg_html'], $contact, TRUE, $knownHTMLTokens);
  $processedText = CRM_Utils_Token::replaceContactTokens($template['msg_text'], $contact, FALSE, $knownTextTokens);

  $params = [
    'from' => 'info@nfb.org',
    'toName' => $contact['display_name'],
    'toEmail' => $contact['withemail'][0]['email'],
    'subject' => $processedSubject,
    'text' => $processedText,
    'html' => $processedHTML,
  ];

  CRM_Utils_Mail::send($params);
}

/**
 * Given an event participant ID, return the contact with email.
 *
 * @param int $participantID
 *
 * @return array|FALSE Either the contact or FALSE if not found
 */
function nfbevents_get_contact_from_participant(int $participantID) {
  $participantResponse = Civi\Api4\Participant::get()
    ->addWhere('id', '=', $participantID)
    ->execute();

  if (count($participantResponse)) {
    $contactResponse = Civi\Api4\Contact::get()
    ->addWhere('id', '=', $participantResponse[0]['contact_id'])
    ->addChain('withemail', Civi\Api4\Email::get()
      ->addWhere('contact_id', '=', '$id')
      ->addWhere('is_primary', '=', TRUE))
    ->execute();

    if (count($contactResponse)) {
      return $contactResponse[0];
    }
  }

  return FALSE;
}

/**
 * Create the message template.
 * Called on install.
 *
 * Unused.
 *
 * @return int The template ID
 */
function nfbevents_create_message_template() : int {
  //TODO get their default subject, body, etc.
  $templateRequest = Civi\Api4\MessageTemplate::create()
    ->addValue('msg_title', 'Upcoming Events Notification Template')
    //->addValue('msg_subject', '')
    //->addValue('msg_html')
    //->addValue('msg_text')
    ->addValue('is_active', 1)
    ->execute();

  return $templateRequest[0]['id'];
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function nfbevents_civicrm_install() {
  _nfbevents_civix_civicrm_install();

  /*$templateSettingID = Civi::settings()->get('upcoming_event_message_ids');
  if ($templateSettingID === NULL) {
    //$templateID = nfbevents_create_message_template();
    //Civi::settings()->set('upcoming_event_message_ids', json_encode([]));
  }*/
}

#region Unchanged Template Hooks
/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function nfbevents_civicrm_config(&$config) {
  _nfbevents_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_xmlMenu
 */
function nfbevents_civicrm_xmlMenu(&$files) {
  _nfbevents_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function nfbevents_civicrm_postInstall() {
  _nfbevents_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function nfbevents_civicrm_uninstall() {
  _nfbevents_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function nfbevents_civicrm_enable() {
  _nfbevents_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function nfbevents_civicrm_disable() {
  _nfbevents_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function nfbevents_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _nfbevents_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
 */
function nfbevents_civicrm_managed(&$entities) {
  _nfbevents_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_caseTypes
 */
function nfbevents_civicrm_caseTypes(&$caseTypes) {
  _nfbevents_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_angularModules
 */
function nfbevents_civicrm_angularModules(&$angularModules) {
  _nfbevents_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_alterSettingsFolders
 */
function nfbevents_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _nfbevents_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function nfbevents_civicrm_entityTypes(&$entityTypes) {
  _nfbevents_civix_civicrm_entityTypes($entityTypes);
}

/**
 * Implements hook_civicrm_themes().
 */
function nfbevents_civicrm_themes(&$themes) {
  _nfbevents_civix_civicrm_themes($themes);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 */
//function nfbevents_civicrm_preProcess($formName, &$form) {
//
//}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
//function nfbevents_civicrm_navigationMenu(&$menu) {
//  _nfbevents_civix_insert_navigation_menu($menu, 'Mailings', array(
//    'label' => E::ts('New subliminal message'),
//    'name' => 'mailing_subliminal_message',
//    'url' => 'civicrm/mailing/subliminal',
//    'permission' => 'access CiviMail',
//    'operator' => 'OR',
//    'separator' => 0,
//  ));
//  _nfbevents_civix_navigationMenu($menu);
//}
#endregion Unchanged Template Hooks
