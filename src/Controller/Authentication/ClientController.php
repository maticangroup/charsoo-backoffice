<?php

namespace App\Controller\Authentication;

use App\FormModels\Authentication\ClientModel;
use App\FormModels\Authentication\RoleModel;
use App\FormModels\ModelSerializer;
use Matican\Core\Entities\Authentication;
use Matican\Core\Servers;
use Matican\Core\Transaction\ResponseStatus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Matican\Core\Transaction\Request as Req;

/**
 * @Route("/authentication/client", name="authentication_client")
 */
class ClientController extends AbstractController
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
         * @var $clientModel ClientModel
         */
        $clientModel = ModelSerializer::parse($inputs, ClientModel::class);

        if (!empty($inputs)) {
            $request = new Req(Servers::Authentication, Authentication::Client, 'new');
            $request->add_instance($clientModel);
            $response = $request->send();
            if ($response->getStatus() == ResponseStatus::successful) {
                $this->addFlash('s', $response->getMessage());
            } else {
                $this->addFlash('f', $response->getMessage());
            }
        }

        /**
         * @var $clients ClientModel[]
         */
        $clients = [];
        $allClientsRequest = new Req(Servers::Authentication, Authentication::Client, 'all');
        $allClientsResponse = $allClientsRequest->send();
        if ($allClientsResponse->getContent()) {
            foreach ($allClientsResponse->getContent() as $client) {
                $clients[] = ModelSerializer::parse($client, ClientModel::class);
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


        return $this->render('authentication/client/create.html.twig', [
            'controller_name' => 'ClientController',
            'clientModel' => $clientModel,
            'clients' => $clients,
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
         * @var $clientModel ClientModel
         */
        $clientModel = ModelSerializer::parse($inputs, ClientModel::class);
        $clientModel->setClientId($id);
        $request = new Req(Servers::Authentication, Authentication::Client, 'fetch');
        $request->add_instance($clientModel);
        $response = $request->send();
        /**
         * @var $clientModel ClientModel
         */
        $clientModel = ModelSerializer::parse($response->getContent(), ClientModel::class);


        /**
         * @var $clients ClientModel[]
         */
        $clients = [];
        $allClientsRequest = new Req(Servers::Authentication, Authentication::Client, 'all');
        $allClientsResponse = $allClientsRequest->send();
        if ($allClientsResponse->getContent()) {
            foreach ($allClientsResponse->getContent() as $client) {
                $clients[] = ModelSerializer::parse($client, ClientModel::class);
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

        if (!empty($inputs)) {
            $clientModel->setClientId($id);
            $request = new Req(Servers::Authentication, Authentication::Client, 'update');
            $request->add_instance($clientModel);
            $response = $request->send();
            if ($response->getStatus() == ResponseStatus::successful) {
                $this->addFlash('s', $response->getMessage());
                return $this->redirect($this->generateUrl('authentication_client_create'));
            } else {
                $this->addFlash('s', $response->getMessage());
            }
        }


        return $this->render('authentication/client/edit.html.twig', [
            'controller_name' => 'ClientController',
            'clientModel' => $clientModel,
            'clients' => $clients,
            'roles' => $roles,
        ]);
    }
}
