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
class EventsController extends BaseController {
  protected $entity = 'Events';
  const DATETIME_STORAGE_FORMAT = "Y-m-d\TH:i:s";
  const DATE_STORAGE_FORMAT = "Y-m-d";
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
      $command = 'FindEvent';
      $params = ['placeId' => (int) $postId];
    }
    $response = $client->FindEvents($params);
    if (!empty($postId)) {
      $response = [$postId => $response->toArray()];
    }

    $build = [];

    foreach ($response['data'] as $id => $post) {
      // $build[$id] = $this->buildPostResponse($post, $post_link);
      // Create node object with attached file.
      var_dump($post['start']);
      $entity_type="node";
      $bundle="event";

      //get definition of target entity type
      $entity_def = \Drupal::entityManager()->getDefinition($entity_type);
      $dtime = \DateTime::createFromFormat("Y-m-d\TH:i:s.u\Z", $post['start']);
      $dtimeFormat = $dtime->format(self::DATETIME_STORAGE_FORMAT);
      $dtimeEnd = \DateTime::createFromFormat("Y-m-d\TH:i:s.u\Z", $post['end']);
      $dtimeFormatEnd = $dtime->format(self::DATETIME_STORAGE_FORMAT);
      var_dump($dtimeFormat);
      //load up an array for creation
      $new_node=array(
        //set title
        'uuid' => $post['id'],
        'title' => $post['name'],
        'field_start' => $dtimeFormat,
        'field_end_date' => $dtimeFormatEnd,
        'field_place' => $post['place']['id'],
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
