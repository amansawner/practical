<?php

namespace Drupal\wowjs_ui\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('animatecss.settings')) {
      $route->setDefault('_form', 'Drupal\wowjs_ui\Form\WowJsSettings');
    }
  }

}
