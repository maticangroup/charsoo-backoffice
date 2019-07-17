<?php

namespace App\Controller\Delivery;

use App\FormModels\Delivery\DeliveryMethodModel;
use App\FormModels\Delivery\DeliveryPersonModel;
use App\FormModels\Delivery\DeliveryPersonStatusModel;
use App\FormModels\Delivery\DispatchModel;
use App\FormModels\Delivery\DispatchPaymentModel;
use App\FormModels\Delivery\DispatchPaymentStatusModel;
use App\FormModels\Delivery\DistrictModel;
use App\FormModels\ModelSerializer;
use App\FormModels\Repository\ProvinceModel;
use Matican\Core\Entities\Delivery;
use Matican\Core\Entities\Repository;
use Matican\Core\Servers;
use Matican\Core\Transaction\ResponseStatus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Matican\Core\Transaction\Request as Req;

/**
 * @Route("/delivery/delivery-person", name="delivery_person")
 */
class DeliveryPersonController extends AbstractController
{
    /**
     * @Route("/list", name="_list")
     */
    public function fetchAll()
    {
        $request = new Req(Servers::Delivery, Delivery::DeliveryPerson, 'all');
        $response = $request->send();

        /**
         * @var $deliveryPersons DeliveryPersonModel[]
         */
        $deliveryPersons = [];
        if ($response->getContent()) {
            foreach ($response->getContent() as $deliveryPerson) {
                $deliveryPersons[] = ModelSerializer::parse($deliveryPerson, DeliveryPersonModel::class);
            }
        }

        return $this->render('delivery/delivery_person/list.html.twig', [
            'controller_name' => 'DeliveryPersonController',
            'deliveryPersons' => $deliveryPersons,
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
         * @var $deliveryPersonModel DeliveryPersonModel
         */
        $deliveryPersonModel = ModelSerializer::parse($inputs, DeliveryPersonModel::class);
        $deliveryPersonModel->setDeliveryPersonId($id);
        $request = new Req(Servers::Delivery, Delivery::DeliveryPerson, 'fetch');
        $request->add_instance($deliveryPersonModel);
        $response = $request->send();
        /**
         * @var $deliveryPersonModel DeliveryPersonModel
         */
        $deliveryPersonModel = ModelSerializer::parse($response->getContent(), DeliveryPersonModel::class);

        /**
         * @var $allowedDistricts DistrictModel[]
         */
        $allowedDistricts = [];
        if ($deliveryPersonModel->getDeliveryPersonAllowedDistricts()) {
            foreach ($deliveryPersonModel->getDeliveryPersonAllowedDistricts() as $district) {
                $allowedDistricts[] = ModelSerializer::parse($district, DistrictModel::class);
            }
        }

        /**
         * @var $deliveryMethods DeliveryMethodModel[]
         */
        $deliveryMethods = [];
        if ($deliveryPersonModel->getDeliveryPersonDeliveryMethods()) {
            foreach ($deliveryPersonModel->getDeliveryPersonDeliveryMethods() as $deliveryMethod) {
                $deliveryMethods[] = ModelSerializer::parse($deliveryMethod, DeliveryMethodModel::class);
            }
        }

        $provincesRequest = new Req(Servers::Repository, Repository::Location, 'get_provinces');
        $provincesResponse = $provincesRequest->send();

        /**
         * @var $provinces ProvinceModel[]
         */
        $provinces = [];
        if ($provincesResponse->getContent()) {
            foreach ($provincesResponse->getContent() as $province) {
                $provinces[] = ModelSerializer::parse($province, ProvinceModel::class);
            }
        }

        /**
         * @var $dispatches DispatchModel[]
         */
        $dispatches = [];
        if ($deliveryPersonModel->getDeliveryPersonDispatches()) {
            foreach ($deliveryPersonModel->getDeliveryPersonDispatches() as $dispatch) {
                $dispatches[] = ModelSerializer::parse($dispatch, DispatchModel::class);
            }
        }


        return $this->render('delivery/delivery_person/edit.html.twig', [
            'controller_name' => 'DeliveryPersonController',
            'deliveryPersonModel' => $deliveryPersonModel,
            'allowedDistricts' => $allowedDistricts,
            'deliveryMethods' => $deliveryMethods,
            'provinces' => $provinces,
            'dispatches' => $dispatches,
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
         * @var $deliveryPersonModel DeliveryPersonModel
         */
        $deliveryPersonModel = ModelSerializer::parse($inputs, DeliveryPersonModel::class);
        $deliveryPersonModel->setDeliveryPersonId($id);
        $request = new Req(Servers::Delivery, Delivery::DeliveryPerson, 'fetch');
        $request->add_instance($deliveryPersonModel);
        $response = $request->send();
        /**
         * @var $deliveryPersonModel DeliveryPersonModel
         */
        $deliveryPersonModel = ModelSerializer::parse($response->getContent(), DeliveryPersonModel::class);

        /**
         * @var $allowedDistricts DistrictModel[]
         */
        $allowedDistricts = [];
        if ($deliveryPersonModel->getDeliveryPersonAllowedDistricts()) {
            foreach ($deliveryPersonModel->getDeliveryPersonAllowedDistricts() as $district) {
                $allowedDistricts[] = ModelSerializer::parse($district, DistrictModel::class);
            }
        }

        /**
         * @var $deliveryMethods DeliveryMethodModel[]
         */
        $deliveryMethods = [];
        if ($deliveryPersonModel->getDeliveryPersonDeliveryMethods()) {
            foreach ($deliveryPersonModel->getDeliveryPersonDeliveryMethods() as $deliveryMethod) {
                $deliveryMethods[] = ModelSerializer::parse($deliveryMethod, DeliveryMethodModel::class);
            }
        }

        $provincesRequest = new Req(Servers::Repository, Repository::Location, 'get_provinces');
        $provincesResponse = $provincesRequest->send();

        /**
         * @var $provinces ProvinceModel[]
         */
        $provinces = [];
        if ($provincesResponse->getContent()) {
            foreach ($provincesResponse->getContent() as $province) {
                $provinces[] = ModelSerializer::parse($province, ProvinceModel::class);
            }
        }

        /**
         * @var $dispatches DispatchModel[]
         */
        $dispatches = [];
        if ($deliveryPersonModel->getDeliveryPersonDispatches()) {
            foreach ($deliveryPersonModel->getDeliveryPersonDispatches() as $dispatch) {
                $dispatches[] = ModelSerializer::parse($dispatch, DispatchModel::class);
            }
        }

        return $this->render('delivery/delivery_person/read.html.twig', [
            'controller_name' => 'DeliveryPersonController',
            'deliveryPersonModel' => $deliveryPersonModel,
            'allowedDistricts' => $allowedDistricts,
            'deliveryMethods' => $deliveryMethods,
            'provinces' => $provinces,
            'dispatches' => $dispatches,
        ]);
    }

    /**
     * @Route("/status/{delivery_person_id}/{machine_name}", name="_status")
     * @param $delivery_person_id
     * @param $machine_name
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ReflectionException
     */
    public function changeDeliveryPersonAvailability($delivery_person_id, $machine_name)
    {
        $deliveryPersonStatusModel = new DeliveryPersonStatusModel();
        if ($machine_name == 'active') {
            $deliveryPersonStatusModel->setDeliveryPersonId($delivery_person_id);
            $deliveryPersonStatusModel->setDeliveryPersonStatusMachineName('deactive');
        } else {
            $deliveryPersonStatusModel->setDeliveryPersonId($delivery_person_id);
            $deliveryPersonStatusModel->setDeliveryPersonStatusMachineName('active');
        }

        $request = new Req(Servers::Delivery, Delivery::DeliveryPerson, 'change_status');
        $request->add_instance($deliveryPersonStatusModel);
        $response = $request->send();
//        dd($response);
        if ($response->getStatus() == ResponseStatus::successful) {
            $this->addFlash('s', $response->getMessage());
        } else {
            $this->addFlash('f', $response->getMessage());
        }
        return $this->redirect($this->generateUrl('delivery_person_list'));
    }

    /**
     * @Route("/add-district/{delivery_person_id}", name="_add_district")
     * @param $delivery_person_id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ReflectionException
     */
    public function addDistrict($delivery_person_id, Request $request)
    {
        $inputs = $request->request->all();
        /**
         * @var $districtModel DistrictModel
         */
        $districtModel = ModelSerializer::parse($inputs, DistrictModel::class);
        $districtModel->setDeliveryPersonId($delivery_person_id);
//        dd($districtModel);
        $request = new Req(Servers::Delivery, Delivery::DeliveryPerson, 'add_allowed_district');
        $request->add_instance($districtModel);
        $response = $request->send();
//        dd($response);
        if ($response->getStatus() == ResponseStatus::successful) {
            $this->addFlash('s', $response->getMessage());
        } else {
            $this->addFlash('f', $response->getMessage());
        }
        return $this->redirect($this->generateUrl('delivery_person_edit', ['id' => $delivery_person_id]));
    }


    /**
     * @Route("/remove-district/{delivery_person_id}/{district_id}", name="_remove_district")
     * @param $delivery_person_id
     * @param $district_id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ReflectionException
     */
    public function removeDistrict($delivery_person_id, $district_id)
    {
        $districtModel = new DistrictModel();
        $districtModel->setDeliveryPersonId($delivery_person_id);
        $districtModel->setDistrictId($district_id);
//        dd($districtModel);
        $request = new Req(Servers::Delivery, Delivery::DeliveryPerson, 'remove_allowed_district');
        $request->add_instance($districtModel);
        $response = $request->send();
//        dd($response);
        if ($response->getStatus() == ResponseStatus::successful) {
            $this->addFlash('s', $response->getMessage());
        } else {
            $this->addFlash('f', $response->getMessage());
        }
        return $this->redirect($this->generateUrl('delivery_person_edit', ['id' => $delivery_person_id]));
    }

    /**
     * @Route("/dispatch-payment-status/{dispatch_id}/{machine_name}/{delivery_person_id}", name="_dispatch_payment_status")
     * @param $dispatch_id
     * @param $machine_name
     * @param $delivery_person_id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ReflectionException
     */
    public function dispatchPayment($dispatch_id, $machine_name, $delivery_person_id)
    {
        $dispatchPaymentStatusModel = new DispatchPaymentStatusModel();
        if ($machine_name == 'paid_back') {
            $dispatchPaymentStatusModel->setDispatchId($dispatch_id);
            $dispatchPaymentStatusModel->setDispatchPaymentStatusMachineName('pending');
        } else {
            $dispatchPaymentStatusModel->setDispatchId($dispatch_id);
            $dispatchPaymentStatusModel->setDispatchPaymentStatusMachineName('paid_back');
        }

        $request = new Req(Servers::Delivery, Delivery::Dispatch, 'confirm_dispatch_payment');
        $request->add_instance($dispatchPaymentStatusModel);
        $response = $request->send();
//        dd($response);
        if ($response->getStatus() == ResponseStatus::successful) {
            $this->addFlash('s', $response->getMessage());
        } else {
            $this->addFlash('f', $response->getMessage());
        }
        return $this->redirect($this->generateUrl('delivery_person_edit', ['id' => $delivery_person_id]));
    }
}
