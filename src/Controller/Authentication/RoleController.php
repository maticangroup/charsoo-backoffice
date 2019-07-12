<?php

namespace App\Controller\Authentication;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/authentication/role", name="authentication_role")
 */
class RoleController extends AbstractController
{

    /**
     * @Route("/create", name="_authentication_role_create")
     */
    public function create()
    {
        return $this->render('authentication/role/create.html.twig', [
            'controller_name' => 'RoleController',
        ]);
    }

    /**
     * @Route("/save", name="_authentication_role_save")
     */
    public function save()
    {
        return $this->render('authentication/role/create.html.twig', [
            'controller_name' => 'RoleController',
        ]);
    }


    public function addPermission()
    {

    }

    public function removePermission()
    {

    }

    /**
     * @Route("/all-permissions", name="_authentication_role_all_permissions")
     */
    public function fetchAllPermissions()
    {

    }
}
