<?php

namespace Drupal\animatecss_ui;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\PagerSelectExtender;
use Drupal\Core\Database\Query\TableSortExtender;

/**
 * Animate manager.
 */
class AnimateCssManager implements AnimateCssManagerInterface {

  /**
   * The database connection used to check the selector against.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Constructs a AnimateCssManager object.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection which will be used to check the selector
   *   against.
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public function isAnimate($animate) {
    return (bool) $this->connection->query("SELECT * FROM {animatecss} WHERE [selector] = :selector", [':selector' => $animate])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function loadAnimate() {
    $query = $this->connection
      ->select('animatecss', 'a')
      ->fields('a', ['aid', 'selector', 'options'])
      ->condition('status', 1);

    return $query->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function addAnimate($animate_id, $animate, $label, $comment, $changed, $status, $options) {
    $this->connection->merge('animatecss')
      ->key('aid', $animate_id)
      ->fields([
        'selector' => $animate,
        'label'    => $label,
        'comment'  => $comment,
        'changed'  => $changed,
        'status'   => $status,
        'options'  => $options,
      ])
      ->execute();

    return $this->connection->lastInsertId();
  }

  /**
   * {@inheritdoc}
   */
  public function removeAnimate($animate_id) {
    $this->connection->delete('animatecss')
      ->condition('aid', $animate_id)
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function findAll($header = [], $search = '', $status = NULL) {
    $query = $this->connection
      ->select('animatecss', 'a')
      ->extend(PagerSelectExtender::class)
      ->extend(TableSortExtender::class)
      ->orderByHeader($header)
      ->limit(50)
      ->fields('a');

    if (!empty($search) && !empty(trim((string) $search)) && $search !== NULL) {
      $search = trim((string) $search);
      // Escape for LIKE matching.
      $search = $this->connection->escapeLike($search);
      // Replace wildcards with MySQL/PostgreSQL wildcards.
      $search = preg_replace('!\*+!', '%', $search);
      // Add selector and the label field columns.
      $group = $query->orConditionGroup()
        ->condition('selector', '%' . $search . '%', 'LIKE')
        ->condition('label', '%' . $search . '%', 'LIKE');
      // Run the query to find matching targets.
      $query->condition($group);
    }

    // Check if status is set.
    if (!is_null($status) && $status != '') {
      $query->condition('status', $status);
    }

    return $query->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function findById($animate_id) {
    return $this->connection->query("SELECT [selector], [label], [comment], [status], [options] FROM {animatecss} WHERE [aid] = :aid", [':aid' => $animate_id])
      ->fetchAssoc();
  }

}
