<?php

namespace App\Controller\Accounting;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/accounting/invoice", name="accounting_invoice")
 */
class InvoiceController extends AbstractController
{
    /**
     * @Route("/list", name="_accounting_invoice_list")
     */
    public function fetchAll()
    {
        return $this->render('accounting/invoice/list.html.twig', [
            'controller_name' => 'InvoiceController',
        ]);
    }

    /**
     * @Route("/create", name="_accounting_invoice_create")
     */
    public function create()
    {
        return $this->render('accounting/invoice/create.html.twig', [
            'controller_name' => 'InvoiceController',
        ]);
    }

    /**
     * @Route("/save", name="_accounting_invoice_save")
     */
    public function save()
    {
        return $this->render('accounting/invoice/edit.html.twig', [
            'controller_name' => 'InvoiceController',
        ]);
    }

    /**
     * @Route("/edit", name="_accounting_invoice_edit")
     */
    public function edit()
    {
        return $this->render('accounting/invoice/edit.html.twig', [
            'controller_name' => 'InvoiceController',
        ]);
    }

    /**
     * @Route("/update", name="_accounting_invoice_update")
     */
    public function update()
    {
        return $this->render('accounting/invoice/edit.html.twig', [
            'controller_name' => 'InvoiceController',
        ]);
    }

    public function addInvoiceItem()
    {
        
    }

    public function removeInvoiceItem()
    {

    }

    public function addPayment()
    {

    }

    public function removePayment()
    {

    }

    public function changePaymentStatus()
    {

    }
}
