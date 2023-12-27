<?php

namespace Drupal\hct\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'work Block Example' block.
 *
 * @Block(
 *   id = "Work",
 *   admin_label = @Translation("Work Block"),
 * )
 */
class workBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build['#attached']['library'][] = 'hct/yours.tree';
    $build['#attached']['library'][] = 'hct/animate-css1';
    $content = [
      '#markup' => '<div class="custom-block">
                      <h1> Our Work</h1>
                    </div>',
    ];

    return $content;
  }

}