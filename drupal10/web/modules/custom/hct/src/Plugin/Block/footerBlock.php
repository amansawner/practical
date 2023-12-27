<?php

namespace Drupal\hct\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'work Block Example' block.
 *
 * @Block(
 *   id = "footer",
 *   admin_label = @Translation("footer Block"),
 * )
 */
class footerBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build['#attached']['library'][] = 'hct/yours.tree';
    $build['#attached']['library'][] = 'hct/animate-css1';
    $content = [
      '#markup' => '<div class="custom-block-foter">
                    <p> © 2023 Elephant in the Boardroom - Melbourne Digital Agency | Development, Design, Technology and Digital Marketing.</p>
                    </div>',
    ];

    return $content;
  }

}