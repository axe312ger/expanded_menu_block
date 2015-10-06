<?php

/**
 * @file
 * Contains \Drupal\system\Plugin\Block\SystemMenuBlock.
 */

namespace Drupal\expanded_menu_block\Plugin\Block;

use Drupal\system\Plugin\Block\SystemMenuBlock;
use Drupal\Core\Form\FormStateInterface;


/**
 * Provides a generic Menu block.
 *
 * @Block(
 *   id = "expanded_menu_block",
 *   admin_label = @Translation("Menu - expanded"),
 *   category = @Translation("Menus - Expanded"),
 *   deriver = "Drupal\system\Plugin\Derivative\SystemMenuBlock"
 * )
 */
class ExpandedMenuBlock extends SystemMenuBlock {

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->configuration;
    $defaults = $this->defaultConfiguration();

    $form['expand'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Expand all childs'),
      '#default_value' => isset($config['expand']) ? $config['expand'] : $defaults['expand'],
      '#return_value' => 1,
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $this->configuration['expand'] = $form_state->getValue('expand');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $menu_name = $this->getDerivativeId();
    $parameters = $this->menuTree->getCurrentRouteMenuTreeParameters($menu_name);

    // Adjust the menu tree parameters based on the block's configuration.
    $level = $this->configuration['level'];
    $depth = $this->configuration['depth'];
    $parameters->setMinDepth($level);
    // When the depth is configured to zero, there is no depth limit. When depth
    // is non-zero, it indicates the number of levels that must be displayed.
    // Hence this is a relative depth that we must convert to an actual
    // (absolute) depth, that may never exceed the maximum depth.
    if ($depth > 0) {
      $parameters->setMaxDepth(min($level + $depth - 1, $this->menuTree->maxDepth()));
    }

    $tree = $this->menuTree->load($menu_name, $parameters);
    $manipulators = array(
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    );

    if ($this->configuration['expand']) {
      $manipulators[] = ['callable' => 'menu.expanded_tree_manipulators:expandChildItems'];
    }

    $tree = $this->menuTree->transform($tree, $manipulators);
    $build = $this->menuTree->build($tree);

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
      'expand' => 0
    ];
  }
}
