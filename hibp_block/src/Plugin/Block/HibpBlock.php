<?php

namespace Drupal\hibp_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
/**
 * Provides a 'HibpBlock' block plugin.
 *
 * @Block(
 *   id = "my_block",
 *   admin_label = @Translation("My Custom Block"),
 * )
 */

class HibpBlock extends BlockBase{

  public function blockForm($form, FormStateInterface $form_state)
  {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();
    $form['Body'] = [
      '#type' => 'textarea',
      '#placeholder' => $this->t('Body (HTML code is allowed)'),
      '#title' => $this->t('Body'),
      '#required' => TRUE,
      '#default_value' => array_key_exists('Body', $config) ? $config['Body'] : '',
    ];
    $form['Filter_Name'] = [
      '#type' => 'textfield',
      '#placeholder' => $this->t('Filter Name'),
      '#title' => $this->t('Filter Name'),
      '#required' => TRUE,
      '#default_value' => array_key_exists('Filter_Name', $config) ? $config['Filter_Name'] : '',
    ];

    $form['result_200'] = [
      '#type' => 'textarea',
      '#placeholder' => $this->t('Please enter response if your pwned'),
      '#title' => $this->t('200 Responce'),
      '#required' => TRUE,
      '#default_value' => array_key_exists('result_200', $config) ? $config['result_200'] : '', 
    ];

    $form['result_404'] = [
      '#type' => 'textarea',
      '#placeholder' => $this->t('Please enter response if your not pwned'),
      '#title' => $this->t('404 Responce'),
      '#required' => TRUE,
      '#default_value' => array_key_exists('result_404', $config) ? $config['result_404'] : '',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    $block_id = strval($config['block_id']);
    $simpleform = \Drupal::formBuilder()->getForm('Drupal\hibp_block\Form\hibp_form',$block_id);
    $build = [
      '#theme' => 'hibp_block',
      '#key1' => $config['Body'],
      '#key2' => $simpleform,
    ];
    return $build;
  }
  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state)
  {
    $this->setConfigurationValue('Body', $form_state->getValue('Body'));
    $this->setConfigurationValue('Filter_Name', $form_state->getValue('Filter_Name'));
    $this->setConfigurationValue('result_200', $form_state->getValue('result_200'));
    $this->setConfigurationValue('result_404', $form_state->getValue('result_404'));
    $this->setConfigurationValue('block_id', $form_state->getBuildInfo()['callback_object']->getEntity()->id());
  }
}