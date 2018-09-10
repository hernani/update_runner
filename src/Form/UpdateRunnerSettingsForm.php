<?php

namespace Drupal\update_runner\Form;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Egulias\EmailValidator\EmailValidator;

/**
 * Configure update runner settings for this site.
 *
 * @internal
 */
class UpdateRunnerSettingsForm extends ConfigFormBase implements ContainerInjectionInterface {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'update_runner_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['update_runner.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    

    parent::submitForm($form, $form_state);
  }

}
