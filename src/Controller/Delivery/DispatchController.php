<?php

namespace App\Controller\Delivery;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/delivery/dispatch", name="delivery_dispatch")
 */
class DispatchController extends AbstractController
{
    /**
     * @Route("/list", name="_delivery_dispatch_list")
     */
    public function fetchAll()
    {
        return $this->render('delivery/dispatch/list.html.twig', [
            'controller_name' => 'DispatchController',
        ]);
    }

    /**
     * @Route("/edit", name="_delivery_dispatch_edit")
     */
    public function edit()
    {
        return $this->render('delivery/dispatch/edit.html.twig', [
            'controller_name' => 'DispatchController',
        ]);
    }

    /**
     * @Route("/update", name="_delivery_dispatch_update")
     */
    public function update()
    {
        return $this->render('delivery/dispatch/edit.html.twig', [
            'controller_name' => 'DispatchController',
        ]);
    }
}
