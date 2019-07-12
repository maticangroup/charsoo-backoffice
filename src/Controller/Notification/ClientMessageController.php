<?php

namespace App\Controller\Notification;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/notification/client-message", name="notification_client_message")
 */
class ClientMessageController extends AbstractController
{
    /**
     * @Route("/list", name="_notification_client_message_list")
     */
    public function fetchAll()
    {
        return $this->render('notification/client_message/list.html.twig', [
            'controller_name' => 'ClientMessageController',
        ]);
    }

    /**
     * @Route("/read", name="_notification_client_message_read")
     */
    public function read()
    {
        return $this->render('notification/client_message/read.html.twig', [
            'controller_name' => 'ClientMessageController',
        ]);
    }
}
