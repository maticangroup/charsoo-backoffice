<?php

namespace App\Controller\Delivery;

use App\FormModels\Delivery\DeliveryMethodModel;
use App\FormModels\Delivery\DeliveryMethodTypeModel;
use App\FormModels\Delivery\DeliveryPersonModel;
use App\FormModels\Delivery\QueueModel;
use App\FormModels\Delivery\WeekDayModel;
use App\FormModels\ModelSerializer;
use App\FormModels\Repository\PersonModel;
use App\FormModels\Repository\SizeModel;
use Matican\Core\Entities\Delivery;
use Matican\Core\Entities\Repository;
use Matican\Core\Servers;
use Matican\Core\Transaction\ResponseStatus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Matican\Core\Transaction\Request as Req;

/**
 * @Route("/delivery/delivery-method", name="delivery_method")
 */
class DeliveryMethodController extends AbstractController
{
    /**
     * @Route("/list", name="_list")
     */
    public function fetchAll()
    {
        return $this->render('delivery/delivery_method/list.html.twig', [
            'controller_name' => 'DeliveryMethodController',
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
         * @var $deliveryMethodModel DeliveryMethodModel
         */
        $deliveryMethodModel = ModelSerializer::parse($inputs, DeliveryMethodModel::class);
        if (!empty($inputs)) {
            $request = new Req(Servers::Delivery, Delivery::DeliveryMethod, 'new');
            $request->add_instance($deliveryMethodModel);
            $response = $request->send();
            if ($response->getStatus() == ResponseStatus::successful) {
                /**
                 * @var $deliveryMethodModel DeliveryMethodModel
                 */
                $deliveryMethodModel = ModelSerializer::parse($response->getContent(), DeliveryMethodModel::class);
                $this->addFlash('s', $response->getMessage());
                $this->redirect($this->generateUrl('delivery_method_edit', ['id' => $deliveryMethodModel->getDeliveryMethodId()]));
            } else {
                $this->addFlash('s', $response->getMessage());
            }
        }

        $deliveryMethodTypesRequest = new Req(Servers::Delivery, Delivery::DeliveryMethod, 'get_all_types');
        $deliveryMethodTypesResponse = $deliveryMethodTypesRequest->send();

        /**
         * @var $deliveryMethodTypes DeliveryMethodTypeModel[]
         */
        $deliveryMethodTypes = [];
        if ($deliveryMethodTypesResponse->getContent()) {
            foreach ($deliveryMethodTypesResponse->getContent() as $type) {
                $deliveryMethodTypes[] = ModelSerializer::parse($type, DeliveryMethodTypeModel::class);
            }
        }

        return $this->render('delivery/delivery_method/create.html.twig', [
            'controller_name' => 'DeliveryMethodController',
            'deliveryMethodModel' => $deliveryMethodModel,
            'deliveryMethodTypes' => $deliveryMethodTypes,
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
         * @var $deliveryMethodModel DeliveryMethodModel
         */
        $deliveryMethodModel = ModelSerializer::parse($inputs, DeliveryMethodModel::class);
        $deliveryMethodModel->setDeliveryMethodId($id);
        $request = new Req(Servers::Delivery, Delivery::DeliveryMethod, 'fetch');
        $request->add_instance($deliveryMethodModel);
        $response = $request->send();
        /**
         * @var $deliveryMethodModel DeliveryMethodModel
         */
        $deliveryMethodModel = ModelSerializer::parse($response->getContent(), DeliveryMethodModel::class);


        $deliveryMethodTypesRequest = new Req(Servers::Delivery, Delivery::DeliveryMethod, 'get_all_types');
        $deliveryMethodTypesResponse = $deliveryMethodTypesRequest->send();

        /**
         * @var $deliveryMethodTypes DeliveryMethodTypeModel[]
         */
        $deliveryMethodTypes = [];
        if ($deliveryMethodTypesResponse->getContent()) {
            foreach ($deliveryMethodTypesResponse->getContent() as $type) {
                $deliveryMethodTypes[] = ModelSerializer::parse($type, DeliveryMethodTypeModel::class);
            }
        }

        $sizesRequest = new Req(Servers::Repository, Repository::Size, 'all');
        $sizesResponse = $sizesRequest->send();

        /**
         * @var $sizes SizeModel[]
         */
        $sizes = [];
        if ($sizesResponse->getContent()) {
            foreach ($sizesResponse->getContent() as $size) {
                $sizes[] = ModelSerializer::parse($size, SizeModel::class);
            }
        }


        $personsRequest = new Req(Servers::Repository, Repository::Person, 'all');
        $personsResponse = $personsRequest->send();

        /**
         * @var $persons PersonModel[]
         */
        $persons = [];
        if ($personsResponse->getContent()) {
            foreach ($personsResponse->getContent() as $person) {
                $persons[] = ModelSerializer::parse($person, PersonModel::class);
            }
        }


        /**
         * @var $selectedSizes SizeModel[]
         */
        $selectedSizes = [];
        if ($deliveryMethodModel->getDeliveryMethodSizes()) {
            foreach ($deliveryMethodModel->getDeliveryMethodSizes() as $selectedSize) {
                $selectedSizes[] = ModelSerializer::parse($selectedSize, SizeModel::class);
            }
        }

        /**
         * @var $deliveryPersons DeliveryPersonModel[]
         */
        $deliveryPersons = [];
        if ($deliveryMethodModel->getDeliveryMethodPersons()) {
            foreach ($deliveryMethodModel->getDeliveryMethodPersons() as $deliveryPerson) {
                $deliveryPersons[] = ModelSerializer::parse($deliveryPerson, DeliveryPersonModel::class);
            }
        }

        /**
         * @var $weekDays WeekDayModel[]
         */
        $weekDays = [];
        if ($deliveryMethodModel->getDeliveryMethodWeekDays()) {
            foreach ($deliveryMethodModel->getDeliveryMethodWeekDays() as $day) {
                $weekDays[] = ModelSerializer::parse($day, WeekDayModel::class);
            }
        }

        if (!empty($inputs)) {
            /**
             * @var $deliveryMethodModel DeliveryMethodModel
             */
            $deliveryMethodModel = ModelSerializer::parse($inputs, DeliveryMethodModel::class);
            $deliveryMethodModel->setDeliveryMethodId($id);
            $request = new Req(Servers::Delivery, Delivery::DeliveryMethod, 'update');
            $request->add_instance($deliveryMethodModel);
            $response = $request->send();
            if ($response->getStatus() == ResponseStatus::successful) {
                /**
                 * @var $deliveryMethodModel DeliveryMethodModel
                 */
                $deliveryMethodModel = ModelSerializer::parse($response->getContent(), DeliveryMethodModel::class);
                $this->addFlash('s', $response->getMessage());
                return $this->redirect($this->generateUrl('delivery_method_edit', ['id' => $deliveryMethodModel->getDeliveryMethodId()]));
            } else {
                $this->addFlash('s', $response->getMessage());
            }
        }


        return $this->render('delivery/delivery_method/edit.html.twig', [
            'controller_name' => 'DeliveryMethodController',
            'deliveryMethodModel' => $deliveryMethodModel,
            'deliveryMethodTypes' => $deliveryMethodTypes,
            'sizes' => $sizes,
            'selectedSizes' => $selectedSizes,
            'deliveryPersons' => $deliveryPersons,
            'persons' => $persons,
            'weekDays' => $weekDays,
        ]);
    }

    /**
     * @Route("/read", name="_read")
     */
    public function read()
    {
        return $this->render('delivery/delivery_method/read.html.twig', [
            'controller_name' => 'DeliveryMethodController',
        ]);
    }

    /**
     * @Route("/add-size/{delivery_method_id}", name="_add_size")
     * @param $delivery_method_id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ReflectionException
     */
    public function addSize($delivery_method_id, Request $request)
    {
        $inputs = $request->request->all();
        /**
         * @var $sizeModel SizeModel
         */
        $sizeModel = ModelSerializer::parse($inputs, SizeModel::class);
        $sizeModel->setDeliveryMethodId($delivery_method_id);
        $request = new Req(Servers::Delivery, Delivery::DeliveryMethod, 'add_size');
        $request->add_instance($sizeModel);
        $response = $request->send();
        if ($response->getStatus() == ResponseStatus::successful) {
            $this->addFlash('s', $response->getMessage());
        } else {
            $this->addFlash('f', $response->getMessage());
        }
        return $this->redirect($this->generateUrl('delivery_method_edit', ['id' => $delivery_method_id]));
    }


    /**
     * @Route("/remove-size/{delivery_method_id}/{size_id}", name="_remove_size")
     * @param $delivery_method_id
     * @param $size_id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ReflectionException
     */
    public function removeSize($delivery_method_id, $size_id)
    {
        $sizeModel = new SizeModel();
        $sizeModel->setDeliveryMethodId($delivery_method_id);
        $sizeModel->setSizeID($size_id);
        $request = new Req(Servers::Delivery, Delivery::DeliveryMethod, 'remove_size');
        $request->add_instance($sizeModel);
        $response = $request->send();
        if ($response->getStatus() == ResponseStatus::successful) {
            $this->addFlash('s', $response->getMessage());
        } else {
            $this->addFlash('f', $response->getMessage());
        }
        return $this->redirect($this->generateUrl('delivery_method_edit', ['id' => $delivery_method_id]));
    }


    /**
     * @Route("/add-delivery-person/{delivery_method_id}", name="_add_delivery_person")
     * @param $delivery_method_id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ReflectionException
     */
    public function addDeliveryPerson($delivery_method_id, Request $request)
    {
        $inputs = $request->request->all();
        /**
         * @var $deliveryPersonModel DeliveryPersonModel
         */
        $deliveryPersonModel = ModelSerializer::parse($inputs, DeliveryPersonModel::class);
        $deliveryPersonModel->setDeliveryPersonDeliveryMethodId($delivery_method_id);
        $request = new Req(Servers::Delivery, Delivery::DeliveryMethod, 'add_delivery_person');
        $request->add_instance($deliveryPersonModel);
        $response = $request->send();
        if ($response->getStatus() == ResponseStatus::successful) {
            $this->addFlash('s', $response->getMessage());
        } else {
            $this->addFlash('f', $response->getMessage());
        }
        return $this->redirect($this->generateUrl('delivery_method_edit', ['id' => $delivery_method_id]));
    }

    /**
     * @Route("/add-queue/{delivery_method_id}/{week_day_id}", name="_add_queue")
     * @param $delivery_method_id
     * @param $week_day_id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ReflectionException
     */
    public function addQueue($delivery_method_id, $week_day_id, Request $request)
    {
        $inputs = $request->request->all();
        /**
         * @var $queueModel QueueModel
         */
        $queueModel = ModelSerializer::parse($inputs, QueueModel::class);
        $queueModel->setDeliveryMethodId($delivery_method_id);
        $queueModel->setWeekDayId($week_day_id);
        $request = new Req(Servers::Delivery, Delivery::DeliveryMethod, 'add_queue');
        $request->add_instance($queueModel);
        $response = $request->send();
        if ($response->getStatus() == ResponseStatus::successful) {
            $this->addFlash('s', $response->getMessage());
        } else {
            $this->addFlash('f', $response->getMessage());
        }
        return $this->redirect($this->generateUrl('delivery_method_edit', ['id' => $delivery_method_id]));
    }


    /**
     * @Route("/remove-queue/{delivery_method_id}/{week_day_id}/{queue_id}", name="_remove_queue")
     * @param $delivery_method_id
     * @param $week_day_id
     * @param $queue_id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ReflectionException
     */
    public function removeQueue($delivery_method_id, $week_day_id, $queue_id)
    {
        $queueModel = new QueueModel();
        $queueModel->setDeliveryMethodId($delivery_method_id);
        $queueModel->setWeekDayId($week_day_id);
        $queueModel->setQueueId($queue_id);
        $request = new Req(Servers::Delivery, Delivery::DeliveryMethod, 'remove_queue');
        $request->add_instance($queueModel);
        $response = $request->send();
        if ($response->getStatus() == ResponseStatus::successful) {
            $this->addFlash('s', $response->getMessage());
        } else {
            $this->addFlash('f', $response->getMessage());
        }
        return $this->redirect($this->generateUrl('delivery_method_edit', ['id' => $delivery_method_id]));
    }



    public function changeAvailability()
    {

    }


    public function changeDeliveryPersonAvailability()
    {

    }


    public function changeQueueAvailability()
    {

    }
}
