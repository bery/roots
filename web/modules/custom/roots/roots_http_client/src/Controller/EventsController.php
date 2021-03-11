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
    $params = ['pageCount' => 10000];

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
      $bundle="event";

      //get definition of target entity type
      $dtimeFormat = $this->formatDate($post['start']);
      $dtimeFormatEnd = $this->formatDate($post['end']);
      $uuid = $post['place']['id'];
      $place_nid = $this->getNidByUuid('place', $uuid);
      //load up an array for creation
      $new_node_data=array(
        //set title
        'uuid' => $post['id'],
        'title' => $post['name'],
        'status' => 1,
        'field_start' => $dtimeFormat,
        'field_end_date' => $dtimeFormatEnd,
        'field_place' => $place_nid,
        'field_state' => $post['state'],
      );

      try{
        $new_node = $this->createNode($bundle, $new_node_data);
        $res = $client->FindEvent(['postId' => $post['id']]);
        $bundle="tournament";
        if(isset($res['tournaments']) && count($res['tournaments'])>0){
          var_dump($res);
          foreach($res['tournaments'] as $player){
            // $player_nid = $this->getNidByUuid("event", $player['id']);
            if($place_nid && $new_node->nid){
              $dtimeFormatStart = $this->formatDate($player['start']);
              // $dtimeFormatEnd = $this->formatDate($player['end']);
              $new_node_data=array(
                //set title
                'uuid' => $player['id'],
                'field_tournament_event' => $new_node->nid,
                'field_tournament_place' => $place_nid,
                'status' => 1,
                'title' => $player['name'],
                'field_tournament_end' => $dtimeFormatEnd,
                'field_tournament_start' => $dtimeFormatStart,
                //set body
                // 'body' => 'this is a test body, can also be set as an array with "value" and "format" as keys I believe',
              );
              $new_node = $this->createNode($bundle, $new_node_data);
            } else {
              var_dump("player not found " . $player['id']);
              var_dump($player);
            }
          }
        }

      } catch (\Exception $e){
        var_dump($e->getMessage());
      }
    }

    return $build;
  }
}
