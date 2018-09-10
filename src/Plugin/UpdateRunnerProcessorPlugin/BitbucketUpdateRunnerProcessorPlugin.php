<?php

namespace Drupal\update_runner\Plugin\UpdateRunnerProcessorPlugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * @UpdateRunnerProcessorPlugin(
 *  id = "bitbucket_update_runner_processor_plugin",
 *  label = @Translation("Bitbucket Processor"),
 * )
 */
class BitbucketUpdateRunnerProcessorPlugin extends PluginBase implements ContainerFactoryPluginInterface, PluginInspectionInterface {

  /**
   * Constructs the object.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param \GuzzleHttp\Client $http_client
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Client $http_client) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->http_client = $http_client;
    $this->configuration = $configuration;

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('http_client')
    );
  }

  private function getAccessToken() {
//    $auth = 'Basic ' . base64_encode('Pr4s3uM2BnHr2vqFht' . ':' . 'SAL2jPvpabdXykkd9VQkm5GfezRQ33PR');
    $auth = 'Basic ' . base64_encode($this->configuration['api_key'] . ':' . $this->configuration['api_secret']);

    try {
//    $query = $this->http_client->post('https://bitbucket.org/site/oauth2/access_token', [
      $query = $this->http_client->post('https://bitbucket.org/site/oauth2/access_token', [
        'form_params' => ['grant_type' => 'client_credentials'],
        'headers' => [
          'Authorization' => $auth,
        ]
      ]);

      $contents = json_decode($query->getBody()->getContents());
      return $contents->access_token;
    } catch (ClientException $e) {
      throw $e;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function run() {

    $access_token = $this->getAccessToken();

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

    /* @TODO: Fix this part */
    $object = [
     'author' => $this->configuration['api_commiter_info'],
      'branch' => $this->configuration['api_branch'],
      'update_runner.json' => json_encode(['time' => time()]),
      'message' => 'Update Runner Commit',
    ];

    // make sure previous commit is parent
    if (!empty($contents)) {
      $object['parents'] = $contents->target->hash;
    }

    // does the push
    $query = $this->http_client->post($this->configuration['api_endpoint']  . '/repositories/' . $this->configuration['api_repository'] . '/src', [
      'form_params' => (array)($object),
      'headers' => [
        'Authorization' => $auth,
        'Content-Type' => 'application/x-www-form-urlencoded'
      ]
    ]);

    return TRUE;
  }

  public function optionsKeys() {
    return ['api_endpoint', 'api_repository', 'api_key', 'api_secret', 'api_branch', 'api_commiter_info'];
  }

  /**
   * @param \Drupal\update_runner\Plugin\UpdateRunnerProcessorPlugin\EntityInterface|NULL $entity
   * @return array
   */
  public function formOptions(EntityInterface $entity = NULL) {

    $formOptions = [];
    $defaultValues = [];

    if (!empty($entity) && !empty($entity->get('data'))) {
      $defaultValues = unserialize($entity->get('data'));
    }

    $formOptions['api_endpoint'] = [
      '#type' => 'textfield',
      '#title' => t('API Endpoint'),
      '#description' => t('In case of bitbucket.com, should be https://api.bitbucket.org/2.0'),
      '#default_value' => !empty($defaultValues['api_endpoint']) ? $defaultValues['api_endpoint'] : ''
    ];

    $formOptions['api_repository'] = [
      '#type' => 'textfield',
      '#title' => t('Repository'),
      '#description' => t('Repository to use'),
      '#default_value' => !empty($defaultValues['api_repository']) ? $defaultValues['api_repository'] : ''
    ];

    $formOptions['api_key'] = [
      '#type' => 'textfield',
      '#title' => t('Key'),
      '#description' => t('Key to use'),
      '#default_value' => !empty($defaultValues['api_key']) ? $defaultValues['api_key'] : ''
    ];

    $formOptions['api_secret'] = [
      '#type' => 'textfield',
      '#title' => t('Secret'),
      '#description' => t('Secret to use'),
      '#default_value' => !empty($defaultValues['api_secret']) ? $defaultValues['api_secret'] : ''
    ];

    $formOptions['api_branch'] = [
      '#type' => 'textfield',
      '#title' => t('Branch'),
      '#description' => t('The branch to use'),
      '#default_value' => !empty($defaultValues['api_branch']) ? $defaultValues['api_branch'] : ''
    ];

    $formOptions['api_commiter'] = [
      '#type' => 'fieldset',
      '#title' => t('Committer information'),
    ];

    $formOptions['api_commiter']['api_commiter_info'] = [
      '#type' => 'textfield',
      '#title' => t('Committer info'),
      '#description' => t('Name <email>'),
      '#default_value' => !empty($defaultValues['api_commiter_info']) ? $defaultValues['api_commiter_info'] : ''
    ];

    return $formOptions;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginId() {
    // Gets the plugin_id of the plugin instance.
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginDefinition() {
    // Gets the definition of the plugin implementation.
  }

}
