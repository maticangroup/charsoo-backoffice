<?php

namespace App\Controller\Authentication;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/authentication/user", name="authentication_user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/list", name="authentication_user_list")
     */
    public function fetchAll()
    {
        return $this->render('authentication/user/list.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    /**
     * @Route("/send-password", name="authentication_user_send_password")
     */
    public function sendPassword()
    {
        
    }
}
