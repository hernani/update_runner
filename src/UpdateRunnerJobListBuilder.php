<?php

namespace Drupal\update_runner;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Scheduled site update entities.
 *
 * @ingroup update_runner
 */
class UpdateRunnerJobListBuilder extends EntityListBuilder {


  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Job ID');
    $header['created'] = $this->t('Created');
    $header['status'] = $this->t('Status');
    $header['processor'] = $this->t('Processor');
    $header['changed'] = $this->t('Processed');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\update_runner\Entity\UpdateRunnerJob */
    $row['id'] = $entity->id();
    $row['created'] = date('Y-m-d H:i:s', $entity->get('created')->value);
    $row['status'] = $entity->get('status')->value;
    $row['processor'] = $entity->get('processor')->value;
    $row['changed'] = $entity->get('status')->value ? date('Y-m-d H:i:s', $entity->get('changed')->value) : '';

    return $row + parent::buildRow($entity);
  }

}
