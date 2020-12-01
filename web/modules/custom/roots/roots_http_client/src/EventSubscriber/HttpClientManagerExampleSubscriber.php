<?php

namespace Drupal\roots_http_client\EventSubscriber;

use Drupal\http_client_manager\Event\HttpClientEvents;
use Drupal\http_client_manager\Event\HttpClientHandlerStackEvent;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class HttpClientManagerExampleSubscriber.
 */
class HttpClientManagerExampleSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      HttpClientEvents::HANDLER_STACK => ['onHandlerStack'],
    ];
  }

  /**
   * This method is called whenever the http_client.handler_stack event is
   * dispatched.
   *
   * @param \Drupal\http_client_manager\Event\HttpClientHandlerStackEvent $event
   *   The HTTP Client Handler stack event.
   */
  public function onHandlerStack(HttpClientHandlerStackEvent $event) {
    if ($event->getHttpServiceApi() != 'content_services_yaml') {
      return;
    }

    $handler = $event->getHandlerStack();
    $middleware = Middleware::mapRequest([$this, 'addExampleServiceHttpHeader']);
    $handler->push($middleware, 'content_services_yaml');
  }

  /**
   * Add example service HTTP Header.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   The current Request object.
   *
   * @return \Psr\Http\Message\MessageInterface
   *   Return an instance with the provided value for the specified header.
   */
  public function addExampleServiceHttpHeader(RequestInterface $request) {
    return $request->withHeader('x-api-key', 'uSaU7Ca6YFLC2fKS7esBMDaTheGE2LmJ');
  }

}
