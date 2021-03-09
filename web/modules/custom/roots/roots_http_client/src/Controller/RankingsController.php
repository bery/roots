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
    if (!empty($postId)) {
      $response = [$postId => $response->toArray()];
    }

    $build = [];
    foreach ($response['data'] as $id => $post) {
      // $build[$id] = $this->buildPostResponse($post, $post_link);
      // Create node object with attached file.
      $bundle="rankings";

      $new_node_data=array(
        //set title
        'uuid' => $post['id'],
        'status' => 1,
        'title' => $post['name'],
        'field_player_count' => $post['playersCount'],
        'field_tournament_count' => $post['tournamentsCount'],
        //set body
        // 'body' => 'this is a test body, can also be set as an array with "value" and "format" as keys I believe',
      );

      try{
        // $new_node = $this->createNode($bundle, $new_node_data);
        $res = $client->FindRanking(['postId' => $post['id']]);
        $bundle="ranking_player";
        if(isset($res['players']) && count($res['players']>0)){
          foreach($res['players'] as $player){
            $player_nid = $this->getNidByUuid("player", $player['id']);
            if($player_nid){
              $new_node_data=array(
                //set title
                // 'uuid' => $player['id'],
                'field_player' => $player_nid,
                'field_ranking' => $new_node->nid,
                'status' => 1,
                'title' => sprintf("%s - %s %s (%s)",$post['name'], $player['firstName'], $player['lastName'], $player['rank']),
                'field_rank' => $player['rank'],
                'field_ranking_points' => $player['points'],
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
