<?php

namespace App\Controller\Crm;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/crm/customer", name="crm_customer")
 */
class CustomerController extends AbstractController
{
    /**
     * @Route("/list", name="_crm_customer_list")
     */
    public function fetchAll()
    {
        return $this->render('crm/customer/list.html.twig', [
            'controller_name' => 'CustomerController',
        ]);
    }

    /**
     * @Route("/customer-info", name="_crm_customer_info")
     */
    public function customerInfo() {
        return $this->render('crm/customer/read-info.html.twig', [
            'controller_name' => 'CustomerController',
        ]);
    }

    /**
     * @Route("/sms-log", name="_crm_customer_sms_log")
     */
    public function customerSMSLog() {
        return $this->render('crm/customer/read-sms.html.twig', [
            'controller_name' => 'CustomerController',
        ]);
    }

    /**
     * @Route("/order", name="_crm_customer_order")
     */
    public function customerOrder() {
        return $this->render('crm/customer/read-order.html.twig', [
            'controller_name' => 'CustomerController',
        ]);
    }

    /**
     * @Route("/favorite", name="_crm_customer_favorite")
     */
    public function customerFavorite() {
        return $this->render('crm/customer/read-favorite.html.twig', [
            'controller_name' => 'CustomerController',
        ]);
    }

    /**
     * @Route("/cart", name="_crm_customer_cart")
     */
    public function customerCart() {
        return $this->render('crm/customer/read-cart.html.twig', [
            'controller_name' => 'CustomerController',
        ]);
    }

    /**
     * @Route("/gift-coupon", name="_crm_customer_gift_coupon")
     */
    public function customerGiftCoupon() {
        return $this->render('crm/customer/read-gift-coupon.html.twig', [
            'controller_name' => 'CustomerController',
        ]);
    }

    /**
     * @Route("/company-group", name="_crm_customer_company_group")
     */
    public function customerCompanyGroup() {
        return $this->render('crm/customer/read-company-group.html.twig', [
            'controller_name' => 'CustomerController',
        ]);
    }

    /**
     * @Route("/payment-deed", name="_crm_customer_payment-deed")
     */
    public function customerPaymentDeed() {
        return $this->render('crm/customer/read-payment-deed.html.twig', [
            'controller_name' => 'CustomerController',
        ]);
    }

    /**
     * @Route("/invoice", name="_crm_customer_invoice")
     */
    public function customerInvoice() {
        return $this->render('crm/customer/read-invoice.html.twig', [
            'controller_name' => 'CustomerController',
        ]);
    }

    /**
     * @Route("/bank-account", name="_crm_customer_bank_account")
     */
    public function customerBankAccount() {
        return $this->render('crm/customer/read-bank-account.html.twig', [
            'controller_name' => 'CustomerController',
        ]);
    }

    /**
     * @Route("/invitation", name="_crm_customer_invitation")
     */
    public function customerInvitation() {
        return $this->render('crm/customer/read-invitation.html.twig', [
            'controller_name' => 'CustomerController',
        ]);
    }

    /**
     * @Route("/comment", name="_crm_customer_comment")
     */
    public function customerComment() {
        return $this->render('crm/customer/read-comment.html.twig', [
            'controller_name' => 'CustomerController',
        ]);
    }
}
