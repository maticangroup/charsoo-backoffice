<?php

namespace App\Controller\Accounting;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/accounting/gift-card-group", name="accounting_gift_card_group")
 */
class GiftCardGroupController extends AbstractController
{

    /**
     * @Route("/create", name="_accounting_gift_card_group_create")
     */
    public function create()
    {
        return $this->render('accounting/gift_card_group/create.html.twig', [
            'controller_name' => 'GiftCardGroupController',
        ]);
    }

    /**
     * @Route("/save", name="_accounting_gift_card_group_save")
     */
    public function save()
    {
        return $this->render('accounting/gift_card_group/create.html.twig', [
            'controller_name' => 'GiftCardGroupController',
        ]);
    }

    /**
     * @Route("/read", name="_accounting_gift_card_group_read")
     */
    public function read()
    {
        return $this->render('accounting/gift_card_group/read.html.twig', [
            'controller_name' => 'GiftCardGroupController',
        ]);
    }

    /**
     * @Route("/edit", name="_accounting_gift_card_group_edit")
     */
    public function edit()
    {
        return $this->render('accounting/gift_card_group/edit.html.twig', [
            'controller_name' => 'GiftCardGroupController',
        ]);
    }

    /**
     * @Route("/update", name="_accounting_gift_card_group_update")
     */
    public function update()
    {
        return $this->render('accounting/gift_card_group/edit.html.twig', [
            'controller_name' => 'GiftCardGroupController',
        ]);
    }

    public function fetchAllGiftCardGroups()
    {

    }

    public function fetchAllGiftCards()
    {

    }

    public function changeAvailability()
    {

    }

    public function changeConfirmation()
    {

    }
}
