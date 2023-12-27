<?php

namespace Drupal\hct\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'Custom Block Example' block.
 *
 * @Block(
 *   id = "custom_block_example",
 *   admin_label = @Translation("Custom Block Example"),
 * )
 */
class CustomBlockExample extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build['#attached']['library'][] = 'hct/yours.tree';
    $build['#attached']['library'][] = 'hct/animate-css1';
    $content = [
      '#markup' => '<div class="custom-block">
                      <h1>Contact Us</h1>
                    </div>',
    ];

    return $content;
  }

}