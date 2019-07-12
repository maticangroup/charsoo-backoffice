<?php

namespace App\Controller\Notification;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/notification/sms-log", name="notification_sms_log")
 */
class SMSLogController extends AbstractController
{
    /**
     * @Route("/list", name="_notification_sms_log_list")
     */
    public function fetchAll()
    {
        return $this->render('notification/sms_log/list.html.twig', [
            'controller_name' => 'SMSLogController',
        ]);
    }

    /**
     * @Route("/read", name="_notification_sms_log_read")
     */
    public function read()
    {
        return $this->render('notification/sms_log/read.html.twig', [
            'controller_name' => 'SMSLogController',
        ]);
    }
}
