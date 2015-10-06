<?php

/**
 * @file
 * Contains Drupal\expanded_menu_block\ExpandedTreeManipulators.
 */

namespace Drupal\expanded_menu_block;

use Drupal\Core\Menu\MenuTreeParameters;


/**
 * Class ExpandedTreeManipulators.
 *
 * @package Drupal\expanded_menu_block
 */
class ExpandedTreeManipulators {

  public function expandChildItems(array $tree) {
    foreach ($tree as $key => $element) {
      if ($element->hasChildren) {
        $menu_tree = \Drupal::menuTree();
        $parameters = new MenuTreeParameters();
        $parameters->setRoot($element->link->getPluginId())->excludeRoot()->setMaxDepth(1)->onlyEnabledLinks();

        $subtree = $menu_tree->load(NULL, $parameters);

        if ($subtree) {
          $tree[$key]->subtree = $this->expandChildItems($subtree);
        }
      }
    }
    return $tree;
  }

}
