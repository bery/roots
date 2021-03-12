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
class TournamentsController extends BaseController {
  protected $entity = 'Tournaments';

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
    $tournaments = $this->getUuidListByContentType('tournament');
    $t = new \stdClass();
    $t->uuid = "ab932072-4142-11eb-a3ea-2a19bd117ff6";
    $tournaments = [
      $t
    ];
    $build = [];
    foreach ($tournaments as $tournament) {
      // $build[$id] = $this->buildPostResponse($post, $post_link);
      // Create node object with attached file.
      try{
        $res = $client->FindTournamentResults(['tournamentId' => $tournament->uuid]);
        $bundle="tournament_result";
        var_dump($res);
        if(isset($res) && count($res)>0){
          var_dump($res);
          continue;
          foreach($res as $tournamentResult){
            $playerIds = [];
            foreach($res['players'] as $player){
              $playerIds[] = $this->getNidByUuid("player", $player['id']);
            }

            $rankindId = $this->getNidByUuid("rankings", $post['id']);
            if($playerIds){
              $new_node_data=array(
                //set title
                // 'uuid' => $player['playerRankingId'],
                'field_player' => $playerIds,
                'field_tournament' => $tournament,
                'status' => 1,
                'title' => "Tournament result",
                'field_tournament_rank' => $tournamentResult['rank'],
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
