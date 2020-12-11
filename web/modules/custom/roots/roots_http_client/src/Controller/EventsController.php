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
class EventsController extends ControllerBase {

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
    $command = 'FindEvents';
    $params = [];

    if (!empty($postId)) {
      $post_link = FALSE;
      $command = 'FindEvent';
      $params = ['postId' => (int) $postId];
    }
    $response = $client->call($command, $params);

    if (!empty($postId)) {
      $response = [$postId => $response->toArray()];
    }

    $build = [];
    foreach ($response as $id => $post) {
      $build[$id] = $this->buildPostResponse($post, $post_link);
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
      '#title' => $post['id'] . ') ' . $post['name'],
      'body' => [
        '#markup' => '<p>' . $post['name'] . '</p>',
      ],
      'link' => [
        '#markup' => Link::createFromRoute($link_text, $route, $route_params)
          ->toString(),
      ],
    ];

    return $output;
  }

}
