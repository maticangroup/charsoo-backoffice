<?php

namespace App\Controller\Notification;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/notification", name="notification")
 */
class NotificationController extends AbstractController
{
    /**
     * @Route("/list", name="_notification_list")
     */
    public function fetchAll()
    {
        return $this->render('notification/notification/list.html.twig', [
            'controller_name' => 'NotificationController',
        ]);
    }
}
