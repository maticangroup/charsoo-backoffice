<?php

namespace App\Controller\Crm;

use App\FormModels\CRM\CustomerGroupModel;
use App\FormModels\CRM\CustomerGroupStatusModel;
use App\FormModels\ModelSerializer;
use App\FormModels\Repository\PersonModel;
use App\General\AuthUser;
use App\Permissions\ServerPermissions;
use Matican\Core\Entities\CRM;
use Matican\Core\Entities\Repository;
use Matican\Core\Servers;
use Matican\Core\Transaction\ResponseStatus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Matican\Core\Transaction\Request as Req;

/**
 * @Route("/crm/customer-group", name="crm_customer_group")
 */
class CustomerGroupController extends AbstractController
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
         * @var $customerGroupModel CustomerGroupModel
         */
        $customerGroupModel = ModelSerializer::parse($inputs, CustomerGroupModel::class);
        if (!empty($inputs)) {
//            dd($customerGroupModel);
            $request = new Req(Servers::CRM, CRM::CustomerGroup, 'new');
            $request->add_instance($customerGroupModel);
            $response = $request->send();
//            dd($response);
            if ($response->getStatus() == ResponseStatus::successful) {
                $this->addFlash('s', $response->getMessage());
            } else {
                $this->addFlash('f', $response->getMessage());
            }
        }

        /**
         * @var $customerGroups CustomerGroupModel[]
         */
        $customerGroups = [];
        $allCustomerGroupsRequest = new Req(Servers::CRM, CRM::CustomerGroup, 'all');
        $allCustomerGroupsResponse = $allCustomerGroupsRequest->send();
        if ($allCustomerGroupsResponse->getContent()) {
            foreach ($allCustomerGroupsResponse->getContent() as $customerGroup) {
                $customerGroups[] = ModelSerializer::parse($customerGroup, CustomerGroupModel::class);
            }
        }
        if (!AuthUser::if_is_allowed(ServerPermissions::crm_customergroup_all)) {
            $customerGroups = [];
        }
        /**
         * @todo Authorization should be handled in twig
         */
        return $this->render('crm/customer_group/create.html.twig', [
            'controller_name' => 'CustomerGroupController',
            'customerGroupModel' => $customerGroupModel,
            'customerGroups' => $customerGroups,
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
        if (!AuthUser::if_is_allowed(ServerPermissions::crm_customergroup_fetch)) {
            return $this->redirect($this->generateUrl('crm_customer_group_create'));
        }

        $inputs = $request->request->all();
        /**
         * @var $customerGroupModel CustomerGroupModel
         */
        $customerGroupModel = ModelSerializer::parse($inputs, CustomerGroupModel::class);
        $customerGroupModel->setCustomerGroupId($id);
        $request = new Req(Servers::CRM, CRM::CustomerGroup, 'fetch');
        $request->add_instance($customerGroupModel);
        $response = $request->send();
//        dd($response);
        /**
         * @var $customerGroupModel CustomerGroupModel
         */
        $customerGroupModel = ModelSerializer::parse($response->getContent(), CustomerGroupModel::class);


        /**
         * @var $persons PersonModel[]
         */
        $persons = [];
        $allPersonsRequest = new Req(Servers::Repository, Repository::Person, 'all');
        $allPersonsResponse = $allPersonsRequest->send();
        if ($allPersonsResponse->getContent()) {
            foreach ($allPersonsResponse->getContent() as $person) {
                $persons[] = ModelSerializer::parse($person, PersonModel::class);
            }
        }

        /**
         * @var $selectedPersons PersonModel[]
         */
        $selectedPersons = [];
        if ($customerGroupModel->getCustomerGroupPersons()) {
            foreach ($customerGroupModel->getCustomerGroupPersons() as $person) {
                $selectedPersons[] = ModelSerializer::parse($person, PersonModel::class);
            }
        }


        if (!empty($inputs)) {
            /**
             * @var $customerGroupModel CustomerGroupModel
             */
            $customerGroupModel = ModelSerializer::parse($inputs, CustomerGroupModel::class);
            $customerGroupModel->setCustomerGroupId($id);
            $request = new Req(Servers::CRM, CRM::CustomerGroup, 'update');
            $request->add_instance($customerGroupModel);
            $response = $request->send();
            if ($response->getStatus() == ResponseStatus::successful) {
                $this->addFlash('s', $response->getMessage());
                return $this->redirect($this->generateUrl('crm_customer_group_edit', ['id' => $id]));
            } else {
                $this->addFlash('f', $response->getMessage());
            }
        }

        return $this->render('crm/customer_group/edit.html.twig', [
            'controller_name' => 'CustomerGroupController',
            'customerGroupModel' => $customerGroupModel,
            'persons' => $persons,
            'selectedPersons' => $selectedPersons,
        ]);
    }


    /**
     * @Route("/add-customer/{customer_group_id}", name="_add_customer")
     * @param $customer_group_id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \ReflectionException
     */
    public function addCustomer($customer_group_id, Request $request)
    {
        if (!AuthUser::if_is_allowed(ServerPermissions::crm_customergroup_add_person)) {
            return $this->redirect($this->generateUrl('crm_customer_group_edit', ['id' => $customer_group_id]));
        }
        $inputs = $request->request->all();
        /**
         * @var $personModel PersonModel
         */
        $personModel = ModelSerializer::parse($inputs, PersonModel::class);
        $personModel->setCustomerGroupId($customer_group_id);
//        dd($personModel);
        $request = new Req(Servers::CRM, CRM::CustomerGroup, 'add_person');
        $request->add_instance($personModel);
        $response = $request->send();
//        dd($response);
        if ($response->getStatus() == ResponseStatus::successful) {
            $this->addFlash('s', $response->getMessage());
        } else {
            $this->addFlash('f', $response->getMessage());
        }
        return $this->redirect($this->generateUrl('crm_customer_group_edit', ['id' => $customer_group_id]));
    }

    /**
     * @Route("/remove-customer/{person_id}/{customer_group_id}", name="_remove_customer")
     * @param $person_id
     * @param $customer_group_id
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \ReflectionException
     */
    public function removeCustomer($person_id, $customer_group_id)
    {
        if (!AuthUser::if_is_allowed(ServerPermissions::crm_customergroup_remove_person)) {
            return $this->redirect($this->generateUrl('crm_customer_group_edit', ['id' => $customer_group_id]));
        }
        $personModel = new PersonModel();
        $personModel->setId($person_id);
        $personModel->setCustomerGroupId($customer_group_id);
        $request = new Req(Servers::CRM, CRM::CustomerGroup, 'remove_person');
        $request->add_instance($personModel);
        $response = $request->send();
        if ($response->getStatus() == ResponseStatus::successful) {
            $this->addFlash('s', $response->getMessage());
        } else {
            $this->addFlash('f', $response->getMessage());
        }
        return $this->redirect($this->generateUrl('crm_customer_group_edit', ['id' => $customer_group_id]));
    }

    /**
     * @Route("/status/{customer_group_id}/{machine_name}", name="_status")
     * @param $customer_group_id
     * @param $machine_name
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ReflectionException
     */
    public function changeCustomerGroupAvailability($customer_group_id, $machine_name)
    {
        if (!AuthUser::if_is_allowed(ServerPermissions::crm_customergroup_set_status)) {
            return $this->redirect($this->generateUrl('crm_customer_group_create'));
        }
        $customerGroupStatusModel = new CustomerGroupStatusModel();
        if ($machine_name == 'active') {
            $customerGroupStatusModel->setCustomerGroupId($customer_group_id);
            $customerGroupStatusModel->setCustomerGroupStatusMachineName('deactive');
        } else {
            $customerGroupStatusModel->setCustomerGroupId($customer_group_id);
            $customerGroupStatusModel->setCustomerGroupStatusMachineName('active');
        }

//        dd($customerGroupStatusModel);
        $request = new Req(Servers::CRM, CRM::CustomerGroup, 'set_status');
        $request->add_instance($customerGroupStatusModel);
        $response = $request->send();
        if ($response->getStatus() == ResponseStatus::successful) {
            $this->addFlash('s', $response->getMessage());
        } else {
            $this->addFlash('f', $response->getMessage());
        }
        return $this->redirect($this->generateUrl('crm_customer_group_create'));
    }


}
