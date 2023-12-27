<?php

namespace Drupal\hct\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'work Block Example' block.
 *
 * @Block(
 *   id = "front",
 *   admin_label = @Translation("frontpage Block"),
 * )
 */
class frontpage extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build['#attached']['library'][] = 'hct/yours.tree';
    $build['#attached']['library'][] = 'hct/animate-css1';
    $content = [
      '#markup' => '<section class="pr__hero uk-section" id="pr__hero">
      <div class="uk-container">
          <div class="section-inner">
              <div class="hero-content uk-grid uk-grid-stack" data-uk-grid="">
                  <div class="uk-width-2-3@s uk-first-column">
                      <hr class="line pr__hr__primary" style="width: 70px;">
                      <h1 class="title uk-heading-hero" style="opacity: 1; transform: translateY(0px);">
                          <p>Website Design &amp; Digital Marketing Agency</p>
                      </h1>
                      <p class="sub-heading">The One-Stop Digital Agency For Website Design, Website Development &amp; Digital Marketing In Melbourne</p>
                      <div class="aman12">
                      <a id="aman1" class="button uk-button uk-button-default" href="/services" style="margin-top: 31px; opacity: 1; transform: translateY(0px);">Our Services</a>
                      <a id="aman1" class="button uk-button uk-button-default services" href="/contact-us" style="margin-top: 31px; margin-left: 10px; opacity: 1; transform: translateY(0px);">Get a Free Quote</a>
                      </div>
                  </div>
              </div>
          </div>
          <div class="elepahnt-image">
          <img src ="https://drupal10.lndo.site/sites/default/files/2023-12/depositphotos_124567934-stock-illustration-illustration-of-elephant-hipster-dressed.jpg" alt="Elephant Image">
          </div>
      </div>
  </section>',
    ];

    return $content;
  }

}