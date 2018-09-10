<?php

namespace Drupal\update_runner\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Scheduled site update edit forms.
 *
 * @ingroup update_runner
 */
class ScheduledSiteUpdateForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\update_runner\Entity\ScheduledSiteUpdate */
    $form = parent::buildForm($form, $form_state);

    $entity = $this->entity;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Scheduled site update.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Scheduled site update.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.scheduled_site_update.canonical', ['scheduled_site_update' => $entity->id()]);
  }

}
