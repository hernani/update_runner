<?php

namespace Drupal\update_runner;

use Drupal\update_runner\Plugin\UpdateRunnerProcessorPluginManager;
use Drupal\Core\Entity\EntityManager;
use Drupal\Core\Entity\EntityStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactory;

class UpdateRunnerManager {

  protected $efq;

  /**
   * When the service is created, set a value for the example variable.
   */
  public function __construct(ConfigFactory $config_factory, EntityManager $entityManager, UpdateRunnerProcessorPluginManager $pluginManager) {
    $this->entityStorage = $entityManager->getStorage('update_runner_job');
    $this->pluginManager = $pluginManager;
    $this->configFactory = $config_factory;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity.manager'),
      $container->get('plugin.manager.update_runner_processor_plugin')
    );
  }

  public function fetch() {

  }

  public function process() {

    // Load all available update runner jobs
    $ids = $this->entityStorage->getQuery()
      ->condition('status', UPDATE_RUNNER_JOB_NOT_PROCESSED)
      ->execute();

    $availableUpdates = $this->entityStorage->loadMultiple($ids);

    // Executes them
    foreach ($availableUpdates as $update) {

      $processorConfig = $this->configFactory->get('update_runner.update_runner_processor.' . $update->processor->value);
      $pluginType = $processorConfig->get('plugin');

      $status = $this->pluginManager->createInstance($pluginType, unserialize($processorConfig->get('data')))->run($update->data);
      $update->status = $status;
      $update->save();
    }
  }

}