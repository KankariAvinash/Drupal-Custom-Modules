<?php

namespace  Drupal\custom_view_bulk_operations\Plugin\Action;

use Drupal\views_bulk_operations\Action\ViewsBulkOperationsActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\node\Entity\Node;

/**
 * Action description.
 *
 * @Action(
 *   id = "custom_bulk_operation",
 *   label = @Translation("custom Bulk Operation"),
 *   type = "",
 *   confirm = FALSE,
 * 
 * )
 */

class custom_view_bulk_operation_action extends ViewsBulkOperationsActionBase
{
    use StringTranslationTrait;
    protected $entity_type = "example";
    protected $create_entity_type = "page";

    /**
     * {@inheritdoc}
     */
    public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE)
    {
        $result = $object->access('update', $account, TRUE);
        return $return_as_object ? $result : $result->isAllowed();
    }
    /**
     * {@inheritdoc}
     */
    public function execute(ContentEntityInterface $entity = NULL)
    {
        $nid = $entity->id();
        $node = \Drupal::entityTypeManager()->getStorage('node')->load($nid);
        if ($node->getType() == $this->entity_type) {
            if ($node->field_content == $this->create_entity_type) {
                \Drupal::messenger()->addMessage('The content has this entitiy field');
            } else {
                $create_node = Node::create(array(
                    'type' => $this->create_entity_type,
                    'title' => $this->t("Entity Reference Field"),
                ));
                $create_node->save();
                $node->field_content = $create_node;
                $node->save();
            }
        }
    }
}
