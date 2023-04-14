<?php

namespace Drupal\hahn_redirect\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @route("/hello-ajax-response}", name="hello-ajax-response", methods="POST")
 * @param $request
 * @return Response
 */
class hahn_block_controller extends ControllerBase
{
    public function content()
    {
        $nodeId = $_POST["data"];
        $nid = Node::load($nodeId);       
        $response['nid'] =$nodeId;
        $response['first_name'] =$nid->field_first_name[0]->value;
        $response['second_name'] =$nid->field_second_name[0]->value;
        $response['Email'] =$nid->field_email[0]->value;
        $response['Phone_number'] =$nid->field_phone_number[0]->value;
        return new JsonResponse($response);
    }
}
