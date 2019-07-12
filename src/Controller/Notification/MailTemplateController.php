<?php

namespace App\Controller\Notification;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/notification/mail-template", name="notification_mail_template")
 */
class MailTemplateController extends AbstractController
{
    /**
     * @Route("/list", name="_notification_mail_template_list")
     */
    public function fetchAll()
    {
        return $this->render('notification/mail_template/list.html.twig', [
            'controller_name' => 'MailTemplateController',
        ]);
    }

    /**
     * @Route("/edit", name="_notification_mail_template_edit")
     */
    public function edit()
    {
        return $this->render('notification/mail_template/edit.html.twig', [
            'controller_name' => 'MailTemplateController',
        ]);
    }

    /**
     * @Route("/update", name="_notification_mail_template_update")
     */
    public function update()
    {
        return $this->render('notification/mail_template/edit.html.twig', [
            'controller_name' => 'MailTemplateController',
        ]);
    }
}
