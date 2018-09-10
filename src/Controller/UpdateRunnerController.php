<?php

namespace Drupal\update_runner\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class UpdateRunnerController.
 */
class UpdateRunnerController extends ControllerBase {

  /**
   * Generate.
   *
   * @return string
   *   Return Hello string.
   */
  public function generate() {

    /*
    $values = array(
      'hash' => md5('asdfd'),
      'status' => 1,
      'created' => time(),
      'processor' => 'github',
    );

    $entity = \Drupal::entityTypeManager()->getStorage('scheduled_site_update')->create($values);
    $entity->save();
    */

    $plugin_manager = \Drupal::service('plugin.manager.update_runner_processor_plugin');
    $plugin_manager->createInstance('bitbucket_update_runner_processor_plugin')->build();


    return [
      '#type' => 'markup',
      '#markup' => $this->t('Implement method: generate')
    ];
  }

}
