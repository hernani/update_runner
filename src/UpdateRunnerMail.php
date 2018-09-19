<?php

namespace Drupal\update_runner;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UpdateRunnerMail implements ContainerInjectionInterface {


  public function __construct(ConfigFactory $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory')
    );
  }

  function mailDefinition($key, &$message, $params) {
    $options = array(
      'langcode' => $message['langcode'],
    );

    switch ($key) {
      case 'job_created':
        $message['from'] = $this->configFactory->get('system.site')->get('mail');
        $message['subject'] = t('Update runner job created for @processorId', array('@processorId' => $params['processor_id']), $options);
        $message['body'][] = t('The following data will be used: ' . $params['job_data']);
        break;
      case 'job_completed':
        $message['from'] = $this->configFactory->get('system.site')->get('mail');
        $message['subject'] = t('Update runner job executed for @processorId', array('@processorId' => $params['processor_id']), $options);
        $message['body'][] = t('The following data got used: ' . $params['job_data']);
        break;
    }

  }
}