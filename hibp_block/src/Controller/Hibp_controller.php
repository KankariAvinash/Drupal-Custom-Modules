<?php

namespace Drupal\hibp_block\Controller;

use Drupal\Core\Controller\ControllerBase;

class Hibp_controller extends ControllerBase
{
    public function showdata()
    {
        $id = 1;
        $result = \Drupal::database()->select('hibp_table', 'n')->fields('n', array('id','IP_Address', 'Filter_Name','Response','Date'))->orderBy('id','DESC')->execute()->fetchAll();
        foreach ($result as $row => $content) {
            $rows[] = array(
                'data' => array(
                    $id++,$content->Date,$content->IP_Address, $content->Filter_Name,$content->Response
                )
            );
        }
        $header = array(
            'Id' => $this->t('S.No'),
            'Date' => $this->t('Date'),
            'IP_Address' => $this->t('IP Address'),
            'Filter_Name' => $this->t('Filter Name'),
            'Response' => $this->t('Pwned/Not Pwned'),
        );

        $output = array(
            '#theme' => 'table',
            '#header' => $header,
            '#rows' => $rows
        );

        return $output;
    }
}
