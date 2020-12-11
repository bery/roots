<?php

namespace Drupal\roots_http_client\Plugin\HttpServiceApiWrapper;

use Drupal\http_client_manager\Plugin\HttpServiceApiWrapper\HttpServiceApiWrapperBase;
use Drupal\roots_http_client\api\Commands\Events;

/**
 * Class HttpServiceApiWrapperPosts.
 *
 * @package Drupal\http_client_manager\Plugin\HttpServiceApiWrapper
 */
class HttpServiceApiWrapperEvents extends HttpServiceApiWrapperBase {

  /**
   * {@inheritdoc}
   */
  public function getHttpClient() {
    return $this->httpClientFactory->get('content_services_yaml');
  }

  // /**
  //  * Create Post.
  //  *
  //  * @param string $title
  //  *   The Post title.
  //  * @param mixed $body
  //  *   The Post body.
  //  *
  //  * @return array
  //  *   The service response array.
  //  */
  // public function createPost($title, $body) {
  //   $args = [
  //     'userId' => $this->currentUser->id(),
  //     'title' => $title,
  //     'body' => $body,
  //   ];
  //   return $this->call(Posts::CREATE_POST, $args)->toArray();
  // }

  // /**
  //  * Find Posts.
  //  *
  //  * @return array
  //  *   An array of posts.
  //  */
  // public function findTournaments() {
  //   return $this->call(Tournaments::FIND_POSTS)->toArray();
  // }

  /**
   * Find Post.
   *
   * @param int $postId
   *   The Post id.
   *
   * @return array
   *   An array representing the Post matching the provided id.
   */
  public function findEvents($postId) {
    $args = [
      'eventId' => (int) $postId,
    ];
    return $this->call(Events::FIND_EVENTS, $args)->toArray();
  }

  // /**
  //  * Find Comments.
  //  *
  //  * @param null|int $postId
  //  *   The Post id from which extract the related Comments.
  //  *
  //  * @return array
  //  *   An array of Comments related to the given Post.
  //  */
  // public function findComments($postId = NULL) {
  //   $args = is_null($postId) ? [] : ['postId' => (int) $postId];
  //   return $this->call(Posts::FIND_COMMENTS, $args)->toArray();
  // }

}
