<?php

namespace Drupal\opay\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure opay settings for this site.
 */
class OPaySettingsForm extends ConfigFormBase {

  /**
   * Configure opay settings form id.
   *
   * @var string
   */
  const FORM_ID = 'opay_settings_form';

  /**
   * Configure opay settings file.
   *
   * @var string
   */
  const CONFIG_NAME = 'opay.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return self::FORM_ID;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [self::CONFIG_NAME];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(self::CONFIG_NAME);
    $form['token'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Token signature'),
      '#default_value' => $config->get('token'),
    ];
    $form['merchant_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Merchant id'),
      '#default_value' => $config->get('merchant_id'),
    ];
    $form['api_uri'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Base URI'),
      '#default_value' => $config->get('api_uri'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (empty($form_state->getValue('token'))) {
      $form_state->setErrorByName('token', $this->t('The token value is not correct.'));
    }
    if (empty($form_state->getValue('merchant_id'))) {
      $form_state->setErrorByName('merchant_id', $this->t('The Merchant ID value is not correct.'));
    }
    if (empty($form_state->getValue('api_uri'))) {
      $form_state->setErrorByName('api_uri', $this->t('The API Uri value is not correct.'));
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config(self::CONFIG_NAME)
      ->set('token', $form_state->getValue('token'))
      ->set('merchant_id', $form_state->getValue('merchant_id'))
      ->set('api_uri', $form_state->getValue('api_uri'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
