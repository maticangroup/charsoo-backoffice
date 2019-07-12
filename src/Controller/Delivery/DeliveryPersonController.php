<?php

namespace App\Controller\Delivery;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/delivery/delivery-person", name="delivery_delivery_person")
 */
class DeliveryPersonController extends AbstractController
{
    /**
     * @Route("/list", name="_delivery_delivery_person_list")
     */
    public function fetchAll()
    {
        return $this->render('delivery/delivery_person/list.html.twig', [
            'controller_name' => 'DeliveryPersonController',
        ]);
    }

    /**
     * @Route("/add", name="_delivery_delivery_person_add")
     */
    public function add()
    {
        return $this->render('delivery/delivery_person/read.html.twig', [
            'controller_name' => 'DeliveryPersonController',
        ]);
    }

    /**
     * @Route("/edit", name="_delivery_delivery_person_edit")
     */
    public function edit()
    {
        return $this->render('delivery/delivery_person/edit.html.twig', [
            'controller_name' => 'DeliveryPersonController',
        ]);
    }

    /**
     * @Route("/update", name="_delivery_delivery_person_update")
     */
    public function update()
    {
        return $this->render('delivery/delivery_person/edit.html.twig', [
            'controller_name' => 'DeliveryPersonController',
        ]);
    }

    /**
     * @Route("/read", name="_delivery_delivery_person_read")
     */
    public function read()
    {
        return $this->render('delivery/delivery_person/read.html.twig', [
            'controller_name' => 'DeliveryPersonController',
        ]);
    }

    public function changeDeliveryPersonAvailability()
    {

    }

    public function addDistrict()
    {

    }

    public function removeDistrict()
    {

    }
}
