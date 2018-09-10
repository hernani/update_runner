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
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\update_runner\Entity\ScheduledSiteUpdate */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.update_runner_job.edit_form',
      ['update_runner_job' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
