<?php

namespace Drupal\roots_http_client\Controller;

use Drupal\Core\Link;
use Drupal\Core\Controller\ControllerBase;
use Drupal\http_client_manager\HttpClientManagerFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AcmePagesController.
 *
 * @package Drupal\acme_pages\Controller
 */
class RankingsController extends BaseController {
  protected $entity = 'Rankings';

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
    $params = ['pageCount' => 1000];

    if (!empty($postId)) {
      $post_link = FALSE;
      $command = 'FindPlace';
      $params = ['placeId' => (int) $postId];
    }
    $response = $client->$command($params);
    // var_dump($response);
    if (!empty($postId)) {
      $response = [$postId => $response->toArray()];
    }

    $build = [];

    foreach ($response['data'] as $id => $post) {
      // $build[$id] = $this->buildPostResponse($post, $post_link);
      // Create node object with attached file.
      $entity_type="node";
      $bundle="rankings";

      //get definition of target entity type
      $entity_def = \Drupal::entityManager()->getDefinition($entity_type);

      //load up an array for creation
      $new_node=array(
        //set title
        'uuid' => $post['id'],
        'status' => 1,
        'title' => $post['name'],
        'field_player_count' => $post['playersCount'],
        'field_tournament_count' => $post['tournamentsCount'],
        //set body
        // 'body' => 'this is a test body, can also be set as an array with "value" and "format" as keys I believe',
        $entity_def->get('entity_keys')['bundle']=>$bundle
      );

      $new_post = \Drupal::entityManager()->getStorage($entity_type)->create($new_node);
      $new_post->save();
    }

    return $build;
  }
}
