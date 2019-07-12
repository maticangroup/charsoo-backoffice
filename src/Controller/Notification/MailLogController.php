<?php

namespace App\Controller\Notification;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/notification/mail-log", name="notification_mail_log")
 */
class MailLogController extends AbstractController
{
    /**
     * @Route("/list", name="_notification_mail_log_list")
     */
    public function fetchAll()
    {
        return $this->render('notification/mail_log/list.html.twig', [
            'controller_name' => 'MailLogController',
        ]);
    }

    /**
     * @Route("/read", name="_notification_mail_log_read")
     */
    public function read()
    {
        return $this->render('notification/mail_log/read.html.twig', [
            'controller_name' => 'MailLogController',
        ]);
    }
}
