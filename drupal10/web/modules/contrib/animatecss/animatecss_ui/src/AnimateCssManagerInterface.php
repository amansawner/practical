<?php

namespace Drupal\animatecss_ui;

/**
 * Provides an interface defining an animate manager.
 */
interface AnimateCssManagerInterface {

  /**
   * Returns if this Animate css selector is added.
   *
   * @param string $animate
   *   The Animate css selector to check.
   *
   * @return bool
   *   TRUE if the Animate css selector is added, FALSE otherwise.
   */
  public function isAnimate($animate);

  /**
   * Finds an added animate css selector by its ID.
   *
   * @return string|false
   *   Either the added animate selector or FALSE if none exist with that ID.
   */
  public function loadAnimate();

  /**
   * Add an Animate css selector.
   *
   * @param int $animate_id
   *   The Animate id for edit.
   * @param string $animate
   *   The Animate selector to add.
   * @param string $label
   *   The label of animate selector.
   * @param string $comment
   *   The comment for animate options.
   * @param int $changed
   *   The expected modification time.
   * @param int $status
   *   The status for animate.
   * @param string $options
   *   The Animate selector options.
   *
   * @return int|null|string
   *   The last insert ID of the query, if one exists.
   */
  public function addAnimate($animate_id, $animate, $label, $comment, $changed, $status, $options);

  /**
   * Remove an Animate css selector.
   *
   * @param int $animate_id
   *   The Animate id to remove.
   */
  public function removeAnimate($animate_id);

  /**
   * Finds all added Animate css selector.
   *
   * @param array $header
   *   The animate header to sort selector and label.
   * @param string $search
   *   The animate search key to filter selector.
   * @param int|null $status
   *   The animate status to filter selector.
   *
   * @return \Drupal\Core\Database\StatementInterface
   *   The result of the database query.
   */
  public function findAll($header, $search, $status);

  /**
   * Finds an added Animate css selector by its ID.
   *
   * @param int $animate_id
   *   The ID for an added Animate selector.
   *
   * @return string|false
   *   Either the added Animate selector or FALSE if none exist with that ID.
   */
  public function findById($animate_id);

}
