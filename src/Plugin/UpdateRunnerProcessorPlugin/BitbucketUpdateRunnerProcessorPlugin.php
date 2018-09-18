<?php

namespace Drupal\update_runner\Plugin\UpdateRunnerProcessorPlugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use GuzzleHttp\Exception\ClientException;


/**
 * @UpdateRunnerProcessorPlugin(
 *  id = "bitbucket_update_runner_processor_plugin",
 *  label = @Translation("Bitbucket Processor"),
 * )
 */
class BitbucketUpdateRunnerProcessorPlugin extends UpdateRunnerProcessorPlugin implements ContainerFactoryPluginInterface, PluginInspectionInterface {

  /**
   * {@inheritdoc}
   */
  public function run($job) {

    $access_token = $this->getAccessToken();
    if (!$access_token) {
      return UPDATE_RUNNER_JOB_FAILED;
    }

    $auth = 'Bearer ' . $access_token;

    // check previous sha1
    try {
      $query = $this->http_client->get($this->configuration['api_endpoint'] . '/repositories/' . $this->configuration['api_repository'] . '/refs/branches/' . $this->configuration['api_branch'], [
        'headers' => [
          'Authorization' => $auth,
        ]
      ]);

      $contents = json_decode($query->getBody()->getContents());
    } catch (ClientException $e) {
      // might be first commit
    }

    $object = [
      'author' => $this->configuration['api_commiter_info'],
      'branch' => $this->configuration['api_branch'],
      'update_runner.json' => json_encode(unserialize($job->data->value)),
      'message' => 'Update Runner Commit - ' . date('Y-m-d H:i:s'),
    ];

    // make sure previous commit is parent
    if (!empty($contents)) {
      $object['parents'] = $contents->target->hash;
    }

    // does the push
    try {
      $query = $this->http_client->post(trim($this->configuration['api_endpoint']) . '/repositories/' . $this->configuration['api_repository'] . '/src', [
        'form_params' => (array) ($object),
        'headers' => [
          'Authorization' => $auth,
          'Content-Type' => 'application/x-www-form-urlencoded'
        ]
      ]);

    } catch (RequestException $e) {
      $this->logger->error("Update runner process for bitbucket plugin failed: %msg", ['%msg' => $e->getMessage()]);
      return UPDATE_RUNNER_JOB_FAILED;
    }

    return parent::run($job);
  }

  private function getAccessToken() {
    $auth = 'Basic ' . base64_encode(trim($this->configuration['api_key']) . ':' . trim($this->configuration['api_secret']));

    try {
      $query = $this->http_client->post('https://bitbucket.org/site/oauth2/access_token', [
        'form_params' => ['grant_type' => 'client_credentials'],
        'headers' => [
          'Authorization' => $auth,
        ]
      ]);

      $contents = json_decode($query->getBody()->getContents());
      return $contents->access_token;
    } catch (ClientException $e) {
      $this->logger->error("Update runner process for bitbucket plugin failed: %msg", ['%msg' => $e->getMessage()]);
      return UPDATE_RUNNER_JOB_FAILED;;
    }
  }

  public function optionsKeys() {
    return array_merge(parent::optionsKeys(), [
      'api_endpoint',
      'api_repository',
      'api_key',
      'api_secret',
      'api_branch',
      'api_commiter_info'
    ]);
  }

  /**
   * @param \Drupal\update_runner\Plugin\UpdateRunnerProcessorPlugin\EntityInterface|NULL $entity
   * @return array
   */
  public function formOptions(EntityInterface $entity = NULL) {

    $formOptions = parent::formOptions($entity);

    $formOptions['bitbucket'] = [
      '#type' => 'fieldset',
      '#title' => t('Bitbucket configuration')
    ];

    $formOptions['bitbucket']['api_endpoint'] = [
      '#type' => 'textfield',
      '#title' => t('API Endpoint'),
      '#description' => t('In case of bitbucket.com, should be https://api.bitbucket.org/2.0 . Do not include trailing slash.'),
      '#default_value' => !empty($this->defaultValues['api_endpoint']) ? $this->defaultValues['api_endpoint'] : 'https://api.bitbucket.org/2.0',
      '#required' => TRUE,
    ];

    $formOptions['bitbucket']['api_repository'] = [
      '#type' => 'textfield',
      '#title' => t('Repository'),
      '#description' => t('Repository to use'),
      '#required' => TRUE,
      '#default_value' => !empty($this->defaultValues['api_repository']) ? $this->defaultValues['api_repository'] : ''
    ];

    $formOptions['bitbucket']['api_key'] = [
      '#type' => 'textfield',
      '#title' => t('Key'),
      '#description' => t('Key to use'),
      '#required' => TRUE,
      '#default_value' => !empty($this->defaultValues['api_key']) ? $this->defaultValues['api_key'] : ''
    ];

    $formOptions['bitbucket']['api_secret'] = [
      '#type' => 'textfield',
      '#title' => t('Secret'),
      '#description' => t('Secret to use'),
      '#required' => TRUE,
      '#default_value' => !empty($this->defaultValues['api_secret']) ? $this->defaultValues['api_secret'] : ''
    ];

    $formOptions['bitbucket']['api_branch'] = [
      '#type' => 'textfield',
      '#title' => t('Branch'),
      '#required' => TRUE,
      '#description' => t('The branch to use'),
      '#default_value' => !empty($this->defaultValues['api_branch']) ? $this->defaultValues['api_branch'] : ''
    ];

    $formOptions['api_commiter'] = [
      '#type' => 'fieldset',
      '#title' => t('Committer information'),
    ];

    $formOptions['api_commiter']['api_commiter_info'] = [
      '#type' => 'textfield',
      '#title' => t('Committer info'),
      '#required' => TRUE,
      '#description' => Html::escape(t("Name <email>")),
      '#default_value' => !empty($this->defaultValues['api_commiter_info']) ? $this->defaultValues['api_commiter_info'] : ''
    ];

    return $formOptions;
  }

}
