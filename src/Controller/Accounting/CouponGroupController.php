<?php

namespace App\Controller\Accounting;

use App\FormModels\Accounting\CouponGroupModel;
use App\FormModels\Accounting\CouponGroupStatusModel;
use App\FormModels\Accounting\UsedCouponModel;
use App\FormModels\ModelSerializer;
use App\FormModels\Repository\PersonModel;
use Matican\Core\Entities\Accounting;
use Matican\Core\Entities\Repository;
use Matican\Core\Servers;
use Matican\Core\Transaction\ResponseStatus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Matican\Core\Transaction\Request as Req;

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

        $request = new Req(Servers::Accounting, Accounting::CouponGroup, 'all');
        $response = $request->send();

        /**
         * @var $couponGroups CouponGroupModel[]
         */
        $couponGroups = [];
        if ($response->getContent()) {
            foreach ($response->getContent() as $item) {
                $couponGroups[] = ModelSerializer::parse($item, CouponGroupModel::class);
            }
        }

//        $couponGroups = $response->getContent();
        return $this->render('accounting/coupon_group/list.html.twig', [
            'controller_name' => 'CouponGroupController',
            'couponGroups' => $couponGroups
        ]);
    }

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
         * @var $couponGroupModel CouponGroupModel
         */
        $couponGroupModel = ModelSerializer::parse($inputs, CouponGroupModel::class);

        if (!empty($inputs)) {
            /**
             * @var $couponGroupModel CouponGroupModel
             */
            $couponGroupModel = ModelSerializer::parse($inputs, CouponGroupModel::class);
//            dd($couponGroupModel);
            $request = new Req(Servers::Accounting, Accounting::CouponGroup, 'new');
            $request->add_instance($couponGroupModel);
            $response = $request->send();
//            dd($response);
            if ($response->getStatus() == ResponseStatus::successful) {
                $this->addFlash('s', $response->getMessage());
                /**
                 * @var $couponGroupModel CouponGroupModel
                 */
                $couponGroupModel = ModelSerializer::parse($response->getContent(), CouponGroupModel::class);
                return $this->redirect($this->generateUrl('accounting_coupon_group_edit', ['id' => $couponGroupModel->getCouponGroupId()]));
            } else {
                $this->addFlash('s', $response->getMessage());
            }
        }


        return $this->render('accounting/coupon_group/create.html.twig', [
            'controller_name' => 'CouponGroupController',
            'couponGroupModel' => $couponGroupModel,
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
        $inputs = $request->request->all();

        /**
         * @var $couponGroupModel CouponGroupModel
         */
        $couponGroupModel = ModelSerializer::parse($inputs, CouponGroupModel::class);
        $couponGroupModel->setCouponGroupId($id);
        $request = new Req(Servers::Accounting, Accounting::CouponGroup, 'fetch');
        $request->add_instance($couponGroupModel);
        $response = $request->send();
        /**
         * @var $couponGroupModel CouponGroupModel
         */
        $couponGroupModel = ModelSerializer::parse($response->getContent(), CouponGroupModel::class);


        /**
         * @var $usedCoupons UsedCouponModel[]
         */
        $usedPeopleCoupons = [];
        if ($couponGroupModel->getCouponGroupUsedPeople()) {
            foreach ($couponGroupModel->getCouponGroupUsedPeople() as $usedPeopleCoupon) {
                $usedPeopleCoupons[] = ModelSerializer::parse($usedPeopleCoupon, UsedCouponModel::class);
            }
        }

        $allPersonsRequest = new Req(Servers::Repository, Repository::Person, 'all');
        $allPersonsResponse = $allPersonsRequest->send();

        /**
         * @var $persons PersonModel[]
         */
        $persons = [];
        if ($allPersonsResponse->getContent()) {
            foreach ($allPersonsResponse->getContent() as $person) {
                $persons[] = ModelSerializer::parse($person, PersonModel::class);
            }
        }

        /**
         * @var $selectedPersons PersonModel[]
         */
        $selectedPersons = [];
        if ($couponGroupModel->getCouponGroupAllowedPeople()) {
            foreach ($couponGroupModel->getCouponGroupAllowedPeople() as $person) {
                $selectedPersons[] = ModelSerializer::parse($person, PersonModel::class);
            }
        }

//        if (!empty($inputs)) {
//            /**
//             * @var $couponGroupModel CouponGroupModel
//             */
//            $couponGroupModel = ModelSerializer::parse($inputs, CouponGroupModel::class);
//            $couponGroupModel->setCouponGroupId($id);
//            $request = new Req(Servers::Accounting, Accounting::CouponGroup, 'update');
//            $request->add_instance($couponGroupModel);
//            $response = $request->send();
//            if ($response->getStatus() == ResponseStatus::successful) {
//                $this->addFlash('s', $response->getMessage());
//                /**
//                 * @var $couponGroupModel CouponGroupModel
//                 */
//                $couponGroupModel = ModelSerializer::parse($response->getContent(), CouponGroupModel::class);
//                return $this->redirect($this->generateUrl('', ['id' => $couponGroupModel->getCouponGroupId()]));
//            } else {
//                $this->addFlash('s', $response->getMessage());
//            }
//        }

        return $this->render('accounting/coupon_group/edit.html.twig', [
            'controller_name' => 'CouponGroupController',
            'couponGroupModel' => $couponGroupModel,
            'usedPeopleCoupons' => $usedPeopleCoupons,
            'persons' => $persons,
            'selectedPersons' => $selectedPersons,
        ]);
    }

    /**
     * @Route("/read/{id}", name="_read")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \ReflectionException
     */
    public function read($id, Request $request)
    {
        $inputs = $request->request->all();

        /**
         * @var $couponGroupModel CouponGroupModel
         */
        $couponGroupModel = ModelSerializer::parse($inputs, CouponGroupModel::class);
        $couponGroupModel->setCouponGroupId($id);
        $request = new Req(Servers::Accounting, Accounting::CouponGroup, 'fetch');
        $request->add_instance($couponGroupModel);
        $response = $request->send();
        /**
         * @var $couponGroupModel CouponGroupModel
         */
        $couponGroupModel = ModelSerializer::parse($response->getContent(), CouponGroupModel::class);


        /**
         * @var $usedCoupons UsedCouponModel[]
         */
        $usedPeopleCoupons = [];
        if ($couponGroupModel->getCouponGroupUsedPeople()) {
            foreach ($couponGroupModel->getCouponGroupUsedPeople() as $usedPeopleCoupon) {
                $usedPeopleCoupons[] = ModelSerializer::parse($usedPeopleCoupon, UsedCouponModel::class);
            }
        }

        /**
         * @var $selectedPersons PersonModel[]
         */
        $selectedPersons = [];
        if ($couponGroupModel->getCouponGroupAllowedPeople()) {
            foreach ($couponGroupModel->getCouponGroupAllowedPeople() as $person) {
                $selectedPersons[] = ModelSerializer::parse($person, PersonModel::class);
            }
        }


        return $this->render('accounting/coupon_group/read.html.twig', [
            'controller_name' => 'CouponGroupController',
            'couponGroupModel' => $couponGroupModel,
            'usedPeopleCoupons' => $usedPeopleCoupons,
            'selectedPersons' => $selectedPersons,
        ]);
    }

    /**
     * @Route("/confirm-coupon-group/{coupon_group_id}", name="_confirm_coupon_group")
     * @param $coupon_group_id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ReflectionException
     */
    public function confirmCouponGroup($coupon_group_id)
    {
        $couponGroupModel = new CouponGroupModel();
        $couponGroupModel->setCouponGroupId($coupon_group_id);
        $request = new Req(Servers::Accounting, Accounting::CouponGroup, 'confirm');
        $request->add_instance($couponGroupModel);
        $response = $request->send();
        if ($response->getStatus() == ResponseStatus::successful) {
            $this->addFlash('s', $response->getMessage());
            return $this->redirect($this->generateUrl('accounting_coupon_group_edit', ['id' => $coupon_group_id]));
        } else {
            $this->addFlash('s', $response->getMessage());
        }
    }

    /**
     * @Route("/reject-coupon-group/{coupon_group_id}", name="_reject_coupon_group")
     * @param $coupon_group_id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ReflectionException
     */
    public function rejectCouponGroup($coupon_group_id)
    {
        $couponGroupModel = new CouponGroupModel();
        $couponGroupModel->setCouponGroupId($coupon_group_id);
        $request = new Req(Servers::Accounting, Accounting::CouponGroup, 'stop');
        $request->add_instance($couponGroupModel);
        $response = $request->send();
        if ($response->getStatus() == ResponseStatus::successful) {
            $this->addFlash('s', $response->getMessage());
            return $this->redirect($this->generateUrl('accounting_coupon_group_edit', ['id' => $coupon_group_id]));
        } else {
            $this->addFlash('s', $response->getMessage());
        }
    }


    /**
     * @Route("/add-person/{coupon_group_id}", name="_add_person")
     * @param $coupon_group_id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ReflectionException
     */
    public function addPerson($coupon_group_id, Request $request)
    {
        $inputs = $request->request->all();
        /**
         * @var $personModel PersonModel
         */
        $personModel = ModelSerializer::parse($inputs, PersonModel::class);
        $personModel->setCouponGroupId($coupon_group_id);
        $request = new Req(Servers::Accounting, Accounting::CouponGroup, 'add_allowed_customer');
        $request->add_instance($personModel);
        $response = $request->send();
//        dd($response);

        if ($response->getStatus() == ResponseStatus::successful) {
            $this->addFlash('s', $response->getMessage());
        } else {
            $this->addFlash('f', $response->getMessage());
        }
        return $this->redirect($this->generateUrl('accounting_coupon_group_edit', ['id' => $coupon_group_id]));
    }


    /**
     * @Route("/remove-person/{person_id}/{coupon_group_id}", name="_remove_person")
     * @param $person_id
     * @param $coupon_group_id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ReflectionException
     */
    public function removePerson($person_id, $coupon_group_id)
    {
        $personModel = new PersonModel();
        $personModel->setId($person_id);
        $personModel->setCouponGroupId($coupon_group_id);
        $request = new Req(Servers::Accounting, Accounting::CouponGroup, 'remove_allowed_customer');
        $request->add_instance($personModel);
        $response = $request->send();
        if ($response->getStatus() == ResponseStatus::successful) {
            $this->addFlash('s', $response->getMessage());
        } else {
            $this->addFlash('f', $response->getMessage());
        }
        return $this->redirect($this->generateUrl('accounting_coupon_group_edit', ['id' => $coupon_group_id]));
    }

    /**
     * @Route("/coupon-group-status/{coupon_group_id}/{machine_name}", name="_status")
     * @param $coupon_group_id
     * @param $machine_name
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ReflectionException
     */
    public function changeAvailability($coupon_group_id, $machine_name)
    {
        $couponGroupStatusModel = new CouponGroupStatusModel();
        if ($machine_name == 'active') {
            $couponGroupStatusModel->setCouponGroupId($coupon_group_id);
            $couponGroupStatusModel->setCouponGroupStatusMachineName('deactive');
        } else {
            $couponGroupStatusModel->setCouponGroupId($coupon_group_id);
            $couponGroupStatusModel->setCouponGroupStatusMachineName('active');
        }
        $request = new Req(Servers::Accounting, Accounting::CouponGroup, 'change_status');
        $request->add_instance($couponGroupStatusModel);
        $response = $request->send();
//        dd($response);
        if ($response->getStatus() == ResponseStatus::successful) {
            $this->addFlash('s', $response->getMessage());
        } else {
            $this->addFlash('f', $response->getMessage());
        }
        return $this->redirect($this->generateUrl('accounting_coupon_group_list'));
    }

}
