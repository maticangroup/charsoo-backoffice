<?php

namespace App\Controller\Accounting;

use Matican\Core\Transaction\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/accounting/coupon-group", name="accounting_coupon_group")
 */
class CouponGroupController extends AbstractController
{
    /**
     * @Route("/list", name="_list")
     */
    public function fetchAll()
    {
//        $request = new Request('Accounting','CouponGroup','all');
//        $response = $request->send();
//        $couponGroups = $response->getContent();
        return $this->render('accounting/coupon_group/list.html.twig', [
            'controller_name' => 'CouponGroupController',
//            'couponGroups' => $couponGroups
        ]);
    }

    /**
     * @Route("/create", name="_create")
     */
    public function create()
    {

        return $this->render('accounting/coupon_group/create.html.twig', [
            'controller_name' => 'CouponGroupController',
        ]);
    }

    /**
     * @Route("/read", name="_read")
     */
    public function read()
    {

        return $this->render('accounting/coupon_group/read.html.twig', [
            'controller_name' => 'CouponGroupController',
        ]);
    }

    /**
     * @Route("/edit", name="_edit")
     */
    public function edit()
    {
        return $this->render('accounting/coupon_group/edit.html.twig', [
            'controller_name' => 'CouponGroupController',
        ]);
    }

    public function addPerson()
    {

    }

    public function removePerson()
    {

    }

    public function changeAvailability()
    {

    }

    public function changeConfirmation()
    {

    }

    public function fetchAllPersons()
    {

    }
}
