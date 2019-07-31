<?php

namespace App\Controller\General;

use App\FormModels\Accounting\InvoiceItemModel;
use App\FormModels\Accounting\InvoiceModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\FormModels\ModelSerializer;

class InvoiceViewController extends AbstractController
{
    /**
     * @Route("/general/invoice/view", name="general_invoice_view")
     * @param $invoiceModel InvoiceModel
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index($invoiceModel)
    {

        /**
         * @var $invoiceItems InvoiceItemModel[]
         */
        $invoiceItems = [];
        if ($invoiceModel->getInvoiceItems()) {
            foreach ($invoiceModel->getInvoiceItems() as $invoiceItem) {
                $invoiceItems[] = ModelSerializer::parse($invoiceItem, InvoiceItemModel::class);
            }
        }


        return $this->render('general/invoice_view/index.html.twig', [
            'controller_name' => 'InvoiceViewController',
            'invoiceModel' => $invoiceModel,
            'invoiceItems' => $invoiceItems,
        ]);
    }
}
