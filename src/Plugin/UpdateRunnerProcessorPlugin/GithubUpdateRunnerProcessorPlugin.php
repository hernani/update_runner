<?php

namespace Drupal\update_runner\Plugin\UpdateRunnerProcessorPlugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * @UpdateRunnerProcessorPlugin(
 *  id = "github_update_runner_processor_plugin",
 *  label = @Translation("Github Processor"),
 * )
 */
class GithubUpdateRunnerProcessorPlugin extends PluginBase implements ContainerFactoryPluginInterface, PluginInspectionInterface {

  /**
   * Constructs a Automatic object.
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

  /**
   * {@inheritdoc}
   */
  public function run() {

    $auth = 'Basic ' . base64_encode($this->configuration['api_username'] . ':' . $this->configuration['api_token']);

    // check if file already exists
    try {
      $query = $this->http_client->get($this->configuration['api_endpoint'] . '/repos/' . $this->configuration['api_repository'] . '/contents/update_runner.json', [
        'headers' => [
          'Authorization' => $auth,
        ]
      ]);

      $contents = json_decode($query->getBody()->getContents());
    } catch (ConnectException $e) {
      return UPDATE_RUNNER_JOB_FAILED;
    }


    $object = [
      'committer' => [
        'name' => $this->configuration['api_commiter_name'],
        'email' => $this->configuration['api_commiter_mail']
      ],
      'message' => 'Automatic Updates Commit',
      'content' => base64_encode(json_encode(['time' => time()]))
    ];

    // file already exists, just updates
    if (!empty($contents)) {
      $object['sha'] = $contents->sha;
    }

    $query = $this->http_client->put($this->configuration['api_endpoint'] . '/repos/ ' .  $this->configuration['api_repository'] . '/contents/update_runner.json', [
      'body' => json_encode($object),
      'headers' => [
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
        'Authorization' => $auth,
      ]
    ]);

    var_dump($query);
    var_dump('ole');
    $build = [];

    // Implement your logic

    return UPDATE_RUNNER_JOB_PROCESSED;
  }

  /**
   * @param \Drupal\Core\Entity\EntityInterface|NULL $entity
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
      '#description' => t('In case of github.com, should be https://api.github.com/'),
      '#default_value' => !empty($defaultValues['api_endpoint']) ? $defaultValues['api_endpoint'] : ''
    ];

    $formOptions['api_repository'] = [
      '#type' => 'textfield',
      '#title' => t('Repository'),
      '#description' => t('Repository that should be used (format organization/repository)'),
      '#default_value' => !empty($defaultValues['api_repository']) ? $defaultValues['api_repository'] : ''
    ];

    $formOptions['api_username'] = [
      '#type' => 'textfield',
      '#title' => t('Username'),
      '#description' => t('The username with access to the repository'),
      '#default_value' => !empty($defaultValues['api_username']) ? $defaultValues['api_username'] : ''
    ];

    $formOptions['api_token'] = [
      '#type' => 'textfield',
      '#title' => t('Token'),
      '#description' => t('Token field to use'),
      '#default_value' => !empty($defaultValues['api_token']) ? $defaultValues['api_token'] : ''
    ];

    $formOptions['api_commiter'] = [
      '#type' => 'fieldset',
      '#title' => t('Committer information')
    ];

    $formOptions['api_commiter']['api_commiter_name'] = [
      '#type' => 'textfield',
      '#title' => t('Name'),
      '#label' => t('Name'),
      '#default_value' => !empty($defaultValues['api_commiter_name']) ? $defaultValues['api_commiter_name'] : ''
    ];

    $formOptions['api_commiter']['api_committer_email'] = [
      '#type' => 'textfield',
      '#title' => t('Email'),
      '#label' => t('Email'),
      '#default_value' => !empty($defaultValues['api_commiter_email']) ? $defaultValues['api_commiter_email'] : ''
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
