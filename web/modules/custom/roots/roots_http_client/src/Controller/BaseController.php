<?php

namespace Drupal\roots_http_client\Controller;

use Drupal\Core\Link;
use Drupal\Core\Controller\ControllerBase;
use Drupal\http_client_manager\HttpClientManagerFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use \Drupal\node\Entity\Node;

/**
 * Class AcmePagesController.
 *
 * @package Drupal\acme_pages\Controller
 */
class BaseController extends ControllerBase {

  protected $entity;

  protected $_mapping = [];

  /**
   * An ACME Services - Contents HTTP Client.
   *
   * @var \Drupal\http_client_manager\HttpClientInterface
   */
  protected $httpClient;

  /**
   * AcmePagesController constructor.
   *
   * @param \Drupal\http_client_manager\HttpClientManagerFactoryInterface $http_client_factory
   *   The HTTP Client Manager Factory service.
   */
  public function __construct(HttpClientManagerFactoryInterface $http_client_factory) {
    $this->httpClient = $http_client_factory->get('content_services_yaml');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client_manager.factory')
    );
  }

  /**
   * Display the markup.
   *
   * @return array
   *   Return markup array.
   */
  public function execute() {
    $client = $this->httpClient;
    $post_link = TRUE;
    $command = 'Find' . $this->entity;
    $params = ['pageCount' => 10000];

    if (!empty($postId)) {
      $post_link = FALSE;
      $command = 'FindPlace';
      $params = ['placeId' => (int) $postId];
    }
    $response = $client->$command($params);
    if (!empty($postId)) {
      $response = [$postId => $response->toArray()];
    }

    $build = [];

    foreach ($response['data'] as $id => $post) {
      // $build[$id] = $this->buildPostResponse($post, $post_link);
      // Create node object with attached file.
      $entity_type="node";
      $bundle="player";

      //get definition of target entity type
      $entity_def = \Drupal::entityManager()->getDefinition($entity_type);

      //load up an array for creation
      $name = isset($post['nickName']) ? sprintf("%s %s (%s)", $post['firstName'], $post['lastName'], $post['nickName']) : sprintf("%s %s", $post['firstName'], $post['lastName']);
      $new_node=array(
        //set title
        'uuid' => $post['id'],
        'status' => 1,
        'title' => $name,
        'field_firstname' => $post['firstName'],
        'field_lastname' => $post['lastName'],
        'field_nickname' => isset($post['nickName']) ? $post['nickName'] : "",
        //set body
        // 'body' => 'this is a test body, can also be set as an array with "value" and "format" as keys I believe',
        $entity_def->get('entity_keys')['bundle']=>$bundle
      );

      $new_post = \Drupal::entityManager()->getStorage($entity_type)->create($new_node);
      $new_post->save();
    }

    return $build;
  }

  /**
   * Build Post response.
   *
   * @param array $post
   *   The Post response item.
   * @param bool $post_link
   *   TRUE for a "Read more" link, otherwise "Back to list" link.
   * @param bool $advanced
   *   Boolean indicating if we are using the basic or advanced usage.
   *
   * @return array
   *   A render array of the post.
   */
  protected function buildPostResponse(array $post, $post_link, $advanced = FALSE) {
    $route = $advanced ? 'http_client_manager_example.find_posts.advanced' : 'http_client_manager_example.find_posts';
    $link_text = $post_link ? $this->t('Read more') : $this->t('Back to list');
    $route_params = $post_link ? ['postId' => $post['id']] : [];

    $output = [
      '#type' => 'fieldset',
      '#title' => $post['name'],
      'body' => [
        '#markup' => '<p>' . $post['street'] . '</p>',
      ],
      'link' => [
        '#markup' => Link::createFromRoute($link_text, $route, $route_params)
          ->toString(),
      ],
    ];

    return $output;
  }

  protected function getNidByUuid($contenType, $uuid){
    //TODO add static cache to improve performance
    $query = \Drupal::database()->select('node', 'n');
      $query->addField('n', 'nid');
      $query->condition('n.type', $contenType);
      $query->condition('n.uuid', $uuid);
      $result = $query->execute()->fetch();
      if($result){
        return $result->nid;
      }
      return null;
  }

  protected function createNode($bundle, $fields, $entity_type = 'node'){

      //get definition of target entity type
      $entity_def = \Drupal::entityManager()->getDefinition($entity_type);

      $new_node= array_merge($fields, array($entity_def->get('entity_keys')['bundle']=>$bundle));

      $new_post = \Drupal::entityManager()->getStorage($entity_type)->create($new_node);
      $new_post->save();
      return $new_post;
  }

}
