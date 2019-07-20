<?php

namespace App\Controller\Authentication;

use App\FormModels\Authentication\RoleModel;
use App\FormModels\Authentication\ServerPermissionModel;
use App\FormModels\ModelSerializer;
use Matican\Core\Entities\Authentication;
use Matican\Core\Servers;
use Matican\Core\Transaction\ResponseStatus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Matican\Core\Transaction\Request as Req;

/**
 * @Route("/authentication/role", name="authentication_role")
 */
class RoleController extends AbstractController
{

    /**
     * @Route("/create", name="_create")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \ReflectionException
     */
    public function create(Request $request)
    {
        $inputs = $request->request->all();
        /**
         * @var $roleModel RoleModel
         */
        $roleModel = ModelSerializer::parse($inputs, RoleModel::class);

        if (!empty($inputs)) {
            $request = new Req(Servers::Authentication, Authentication::Role, 'new');
            $request->add_instance($roleModel);
            $response = $request->send();
            if ($response->getStatus() == ResponseStatus::successful) {
                $this->addFlash('s', $response->getMessage());
            } else {
                $this->addFlash('f', $response->getMessage());
            }
        }

        /**
         * @var $roles RoleModel[]
         */
        $roles = [];
        $allRolesRequest = new Req(Servers::Authentication, Authentication::Role, 'all');
        $allRolesResponse = $allRolesRequest->send();
        if ($allRolesResponse->getContent()) {
            foreach ($allRolesResponse->getContent() as $role) {
                $roles[] = ModelSerializer::parse($role, RoleModel::class);
            }
        }


        return $this->render('authentication/role/create.html.twig', [
            'controller_name' => 'RoleController',
            'roleModel' => $roleModel,
            'roles' => $roles,
        ]);
    }

    /**
     * @Route("/edit/{id}", name="_edit")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \ReflectionException
     */
    public function edit($id, Request $request)
    {
        $inputs = $request->request->all();
        /**
         * @var $roleModel RoleModel
         */
        $roleModel = ModelSerializer::parse($inputs, RoleModel::class);
        $roleModel->setRoleId($id);
        $request = new Req(Servers::Authentication, Authentication::Role, 'fetch');
        $request->add_instance($roleModel);
        $response = $request->send();
        /**
         * @var $roleModel RoleModel
         */
        $roleModel = ModelSerializer::parse($response->getContent(), RoleModel::class);

        $allServerPermissionsRequest = new Req(Servers::Authentication, Authentication::PermissionGroup, 'all');
        $allServerPermissionsResponse = $allServerPermissionsRequest->send();

        /**
         * @var $serverPermissions ServerPermissionModel[]
         */
        $serverPermissions = [];
        if ($allServerPermissionsResponse->getContent()) {
            foreach ($allServerPermissionsResponse->getContent() as $server) {
                $serverPermissions[] = ModelSerializer::parse($server, ServerPermissionModel::class);
            }
        }


        return $this->render('authentication/role/edit.html.twig', [
            'controller_name' => 'RoleController',
            'roleModel' => $roleModel,
            'serverPermissions' => $serverPermissions,
        ]);
    }


    public function addPermission()
    {

    }

    public function removePermission()
    {

    }
}
