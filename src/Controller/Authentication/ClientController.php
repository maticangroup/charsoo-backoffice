<?php

namespace App\Controller\Authentication;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/authentication/client", name="authentication_client")
 */
class ClientController extends AbstractController
{

    /**
     * @Route("/create", name="_authentication_client_create")
     */
    public function create()
    {
        return $this->render('authentication/client/create.html.twig', [
            'controller_name' => 'ClientController',
        ]);
    }

    /**
     * @Route("/save", name="_authentication_client_save")
     */
    public function save()
    {
        return $this->render('authentication/client/create.html.twig', [
            'controller_name' => 'ClientController',
        ]);
    }

    /**
     * @Route("/edit", name="_authentication_client_edit")
     */
    public function edit()
    {
        return $this->render('authentication/client/edit.html.twig', [
            'controller_name' => 'ClientController',
        ]);
    }

    /**
     * @Route("/update", name="_authentication_client_update")
     */
    public function update()
    {
        return $this->render('authentication/client/create.html.twig', [
            'controller_name' => 'ClientController',
        ]);
    }

    public function fetchAll()
    {

    }
}
