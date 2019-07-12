<?php

namespace App\Controller\Notification;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/notification/sms-template", name="notification_sms_template")
 */
class SMSTemplateController extends AbstractController
{
    /**
     * @Route("/list", name="_notification_sms_template_list")
     */
    public function fetchAll()
    {
        return $this->render('notification/sms_template/list.html.twig', [
            'controller_name' => 'SMSTemplateController',
        ]);
    }

    /**
     * @Route("/edit", name="_notification_sms_template_edit")
     */
    public function edit()
    {
        return $this->render('notification/sms_template/edit.html.twig', [
            'controller_name' => 'SMSTemplateController',
        ]);
    }

    /**
     * @Route("/update", name="_notification_sms_template_update")
     */
    public function update()
    {
        return $this->render('notification/sms_template/edit.html.twig', [
            'controller_name' => 'SMSTemplateController',
        ]);
    }
}
