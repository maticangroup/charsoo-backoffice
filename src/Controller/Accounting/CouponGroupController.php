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
     * @Route("/list", name="_accounting_coupon_group_list")
     */
    public function fetchAll()
    {
        $request = new Request('Accounting','CouponGroup','all');
        $response = $request->send();
        $couponGroups = $response->getContent();
        return $this->render('accounting/coupon_group/list.html.twig', [
            'controller_name' => 'CouponGroupController',
            'couponGroups' => $couponGroups
        ]);
    }

    /**
     * @Route("/create", name="_accounting_coupon_group_create")
     */
    public function create()
    {
        $request = new Request('Accounting', 'CouponGroup','add');
        $request->add_query('','');
        $response = $request->send();
        $addCouponGroup = $response->getMessage();
        $this->addFlash('success', "This is a success");
        return $this->render('accounting/coupon_group/create.html.twig', [
            'controller_name' => 'CouponGroupController',
            'addCouponGroupMessage' => $addCouponGroup
        ]);
    }

    /**
     * @Route("/read", name="_accounting_coupon_group_read")
     */
    public function read($couponGroupId)
    {
        $request = new Request('Accounting', 'CouponGroup','read');
        $request->add_query('couponGroupId', $couponGroupId);
        $response = $request->send();
        $couponGroup = $response->getContent();
        return $this->render('accounting/coupon_group/read.html.twig', [
            'controller_name' => 'CouponGroupController',
            'couponGroup' => $couponGroup
        ]);
    }

    /**
     * @Route("/edit", name="_accounting_coupon_group_edit")
     */
    public function edit()
    {
        return $this->render('accounting/coupon_group/edit.html.twig', [
            'controller_name' => 'CouponGroupController',
        ]);
    }

    /**
     * @Route("/save", name="_accounting_coupon_group_save")
     */
    public function save()
    {
        return $this->render('accounting/coupon_group/edit.html.twig', [
            'controller_name' => 'CouponGroupController',
        ]);
    }

    /**
     * @Route("/update", name="_accounting_coupon_group_update")
     */
    public function update()
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
