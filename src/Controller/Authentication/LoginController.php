<?php

namespace App\Controller\Authentication;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;


class LoginController extends AbstractController
{
    /**
     * @Route("/login", name="authentication_login")
     */
    public function read()
    {
        return $this->render('authentication/login/read.html.twig', [
            'controller_name' => 'LoginController',
        ]);
    }

    public function sendPassword()
    {
        
    }

    public function login()
    {
        
    }
}
