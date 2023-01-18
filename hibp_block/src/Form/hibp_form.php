<?php

namespace Drupal\hibp_block\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;

class hibp_form extends FormBase
{

  public function getFormId()
  {
    return 'hibp_form';
  }
  public function buildForm(array $form, FormStateInterface $form_state, $block_id= NULL)
  {
    $form['Email'] = [
      '#type' => 'textfield',
      '#placeholder' => $this->t('Please enter your email address'),
      '#label' => $this->t('Email Address'),
      '#required' => TRUE,
    ];
    /*Hidden Field*/
    $form['Block_id'] = [
      '#title' => 'Block_Id',
      '#type' => 'hidden',
      '#default_value'=> $block_id,
    ];
    $form['actions'] = [
      '#type' => 'button',
      '#value' => $this->t('Betroffenheit prüfen'),
      '#prefix' => '<div class="cr_form-block cr_button">',
      '#suffix' => '</div>',
      '#ajax' => [
        'callback' => '::setMessage',
      ],
    ];
    $form['message'] = [
      '#type' => 'markup',
      '#markup' => '<div class="result_message"></div>'
    ];

    return $form;
  }
  public function setMessage(array $form, FormStateInterface $form_state)
  {
    $block_id = $form_state->getValue('Block_id');
    $block = \Drupal\block\Entity\Block::load($block_id);
    if ($block) {
      $settings = $block->get('settings');
      $Filter_Name = $settings['Filter_Name'];
      $resp_404 = $settings['result_404'];
      $resp_200 = $settings['result_200'];
    }
    $response = new AjaxResponse();
    $name = $form_state->getValue('Email');
    $regrex = '~(?:\+?49|0)(?:\s*\d{3}){2}\s*\d{4,10}~'; /*German Number format*/

    $regrex_gen = '~(?:0)(?:\s*\d{3}){2}\s*\d{4,10}~'; /*German Number starts with 0 format*/
    /*Phone number and email validation*/
    if (preg_match_all($regrex_gen, $name, $matches)) {
      $name = preg_replace('/0/', '+49', $name, 1);
    }
    /*checking Email Address validation and Phone number validation*/
    if (filter_var($name, FILTER_VALIDATE_EMAIL) || preg_match_all($regrex, $name, $matches)) {
      $service_url = 'https://haveibeenpwned.com/api/v3/breachedaccount/' . $name . '?domain=' . $Filter_Name;
      $curl = curl_init($service_url);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

      $ch = curl_init($service_url);
      curl_setopt($ch, CURLOPT_HTTPGET, true);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'api-key: <api-key>',
        'user-agent:  <Your Site Name>'
      ));
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      $returndata = curl_exec($ch);
      $status_code = @curl_getinfo($ch, CURLINFO_HTTP_CODE);

      $curl_response = curl_exec($curl);
      if ($curl_response === false) {
        $info = curl_getinfo($curl);
        $json = curl_close($curl);
        die('error occured during curl exec. Additioanl info: ' . var_export($info));
      }
      curl_close($curl);
      $decoded1 = json_decode($curl_response, true);

      if (isset($decoded1->response->status) && $decoded1->response->status == 'ERROR') {
        echo curl_getinfo($ch);
        die('error occured: ' . $decoded1->response->errormessage);
      }
      var_export($decoded1->response, true);
      if ($status_code == '200') {

        $response->addCommand(
          new HtmlCommand(
            '.result_message',
            '<p>' . $resp_200 . '</p>'
          ),
        );
      } else if ($status_code == '404') {
        $response->addCommand(
          new HtmlCommand(
            '.result_message',
            '<p>' . $resp_404 . '</p>'
          ),
        );
      } else if ($status_code == '429') {
        $response->addCommand(
          new HtmlCommand(
            '.result_message',
            '<p> Bitte versuchen Sie es nach einiger Zeit. </p>'
          ),
        );
      }
    } else {
      $response->addCommand(
        new HtmlCommand(
          '.result_message',
          '<div class="my_top_message">' .
            'Bitte geben Sie eine gültige E-Mail Adresse oder Telefonnummer ein.' .
            '</div>'
        ),
      );
    }
    updateDatabase($status_code, $Filter_Name);
    return $response;
  }
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    /*NULL*/
  }
}

function updateDatabase($status_code, $Filter_Name)
{
  if ($status_code == "200") {
    $status_value = 'Pwned';
  } else if ($status_code == "404") {
    $status_value = 'Not Pwned';
  } else if ($status_code == "429") {
    $status_value = 'Request Limit Exceeded';
  }
  $query = \Drupal::database()->insert('hibp_table');
  $query->fields([
    'IP_Address' => \Drupal::request()->getClientIp(),
    'Filter_Name' => ucfirst(strval(preg_replace('/.com/', ' ', $Filter_Name, 1))),
    'Response' => strval($status_value),
    'Date' => date("Y-m-d H:i:s")
  ]);
  $query->execute();
  return true;
}
