<?php

namespace App\Controller\Crm;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/crm/customer-group", name="crm_customer_group")
 */
class CustomerGroupController extends AbstractController
{

    /**
     * @Route("/create", name="_crm_customer_group_create")
     */
    public function create()
    {
        return $this->render('crm/customer_group/create.html.twig', [
            'controller_name' => 'CustomerGroupController',
        ]);
    }

    /**
     * @Route("/save", name="_crm_customer_group_save")
     */
    public function save()
    {
        return $this->render('crm/customer_group/create.html.twig', [
            'controller_name' => 'CustomerGroupController',
        ]);
    }

    /**
     * @Route("/edit", name="_crm_customer_group_edit")
     */
    public function edit()
    {
        return $this->render('crm/customer_group/edit.html.twig', [
            'controller_name' => 'CustomerGroupController',
        ]);
    }

    /**
     * @Route("/update", name="_crm_customer_group_update")
     */
    public function update()
    {
        return $this->render('crm/customer_group/create.html.twig', [
            'controller_name' => 'CustomerGroupController',
        ]);
    }

    /**
     * @Route("/all-customer-groups", name="_crm_customer_group_all_customer_groups")
     */
    public function fetchAllCustomerGroups()
    {

    }

    /**
     * @Route("/all-customers", name="_crm_customer_group_all_customers")
     */
    public function fetchAllCustomers() {

    }

    public function changeCustomerGroupAvailability()
    {

    }

    public function addCustomer()
    {

    }

    public function removeCustomer()
    {

    }
}
