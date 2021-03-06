<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Event\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ComplaintController extends AbstractActionController
{
   protected $complaintTable;
    protected $eventTable;
    public function __construct() {
        $this->_options = new \Zend\Config\Config(include APPLICATION_PATH . '/config/autoload/global.php');
    }
    public function mapaAction()
    {
        
//         $categorias = $this->getEventTable()->complaintFull();
//         var_dump($categorias);exit;
        return new ViewModel();
    }
    
    
    
    public function jsonLugaresAction() {
        $view = new ViewModel();
        header('Content-type: application/x-javascript');
        header("Status: 200");
        $denuncias = $this->getEventTable()->complaintFull();
        $rutaImagen = $this->_options->host->rootImgDinamic;
        if ($denuncias == null) {
            $denunciasMolina = array('mensaje' => 'no existen denuncias');
        }
        for ($i = 0; $i < count($denuncias); $i++) {
            $denunciasMolina[$i]['id'] = (int) $denuncias[$i]['idcomplaint'];
            $denunciasMolina[$i]['latitude'] = $denuncias[$i]['latitude'];
            $denunciasMolina[$i]['longitude'] = $denuncias[$i]['longitude'];
            $denunciasMolina[$i]['description'] = $denuncias[$i]['description'];
           // $denunciasMolina[$i]['coords'] = [$eventos[$i]['latitude'], $eventos[$i]['longitude']];
            $denunciasMolina[$i]['img'] = $rutaImagen.'/complaint/origin/'.$denuncias[$i]['picture'];
        }
        echo json_encode($denunciasMolina);
        exit();
        $view->setTerminal(true);
        return $view;
    }
    
     public function getEventTable() {
        if (!$this->eventTable) {
            $sm = $this->getServiceLocator();
            $this->eventTable = $sm->get('Event\Model\EventTable');
        }
        return $this->eventTable;
    }
     public function getComplaintTable()
    {
        if (! $this->complaintTable) {
            $sm = $this->getServiceLocator();
            $this->complaintTable = $sm->get('Event\Model\ComplaintTable');
        }
        return $this->complaintTable;
    }
}
