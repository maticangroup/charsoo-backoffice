<?php

namespace App\Controller\Inventory;

use App\FormModels\Inventory\InventoryDeedModel;
use App\FormModels\Inventory\ShelveItemProductsModel;
use App\FormModels\Inventory\ShelveModel;
use App\FormModels\Inventory\ShelveStatusModel;
use App\FormModels\ModelSerializer;
use App\FormModels\Repository\ItemModel;
use App\FormModels\Repository\ItemProductsModel;
use App\FormModels\Repository\PersonModel;
use App\FormModels\Repository\PhoneModel;
use App\FormModels\Repository\ProductModel;
use App\General\AuthUser;
use App\Permissions\ServerPermissions;
use Matican\Actions\Repository\PersonActions;
use Matican\Core\Entities\Repository;
use Matican\Core\Servers;
use Matican\Core\Transaction\ResponseStatus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Matican\Core\Entities\Inventory as InventoryEntity;
use Matican\Core\Transaction\Request as Req;

/**
 * @Route("/inventory/shelve", name="inventory_shelve")
 */
class ShelveController extends AbstractController
{
    /**
     * @Route("/list", name="_inventory_shelve_list")
     */
    public function fetchAll()
    {
        if (AuthUser::if_is_allowed(ServerPermissions::inventory_shelve_all)) {

            $request = new Req(Servers::Inventory, InventoryEntity::Shelve, 'all');
            $response = $request->send();

            /**
             * @var $shelves ShelveModel[]
             */
            $shelves = [];
            foreach ($response->getContent() as $shelve) {
                $shelves[] = ModelSerializer::parse($shelve, ShelveModel::class);
            }


            return $this->render('inventory/shelve/list.html.twig', [
                'shelves' => $shelves,
            ]);

        } else {
            return $this->redirect($this->generateUrl('inventory_shelve_inventory_shelve_create'));
        }
    }

    /**
     * @Route("/create", name="_inventory_shelve_create")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \ReflectionException
     */
    public function create(Request $request)
    {

        if (AuthUser::if_is_allowed(ServerPermissions::inventory_shelve_new)) {


            $inputs = $request->request->all();

            /**
             * @var $shelveModel ShelveModel
             */
            $shelveModel = ModelSerializer::parse($inputs, ShelveModel::class);

            if (!empty($inputs)) {
                $request = new Req(Servers::Inventory, InventoryEntity::Shelve, 'new');
                $request->add_instance($shelveModel);
                $response = $request->send();
                if ($response->getStatus() == ResponseStatus::successful) {
                    /**
                     * @var $shelveModel ShelveModel
                     */
                    $shelveModel = ModelSerializer::parse($response->getContent(), ShelveModel::class);
                    $this->addFlash('success', $response->getMessage());
                    return $this->redirect($this->generateUrl('inventory_shelve_inventory_shelve_edit', ['id' => $shelveModel->getShelveId()]));
                }
                $this->addFlash('failed', $response->getMessage());
            }

            $allPersonsRequest = new Req(Servers::Repository, Repository::Person, PersonActions::all);
            $allPersonsResponse = $allPersonsRequest->send();

            /**
             * @var $persons PersonModel[]
             */
            $persons = [];
            foreach ($allPersonsResponse->getContent() as $person) {
                $persons[] = ModelSerializer::parse($person, PersonModel::class);
            }


            return $this->render('inventory/shelve/create.html.twig', [
                'shelveModel' => $shelveModel,
                'persons' => $persons,
            ]);
        } else {
            return $this->redirect($this->generateUrl('inventory_shelve_inventory_shelve_list'));
        }
    }


    /**
     * @Route("/edit/{id}", name="_inventory_shelve_edit")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \ReflectionException
     */
    public function edit($id, Request $request)
    {
        if (AuthUser::if_is_allowed(ServerPermissions::inventory_shelve_fetch)) {


            $inputs = $request->request->all();

            /**
             * @var $shelveModel ShelveModel
             */
            $shelveModel = ModelSerializer::parse($inputs, ShelveModel::class);
            $shelveModel->setShelveId($id);
            $request = new Req(Servers::Inventory, InventoryEntity::Shelve, 'fetch');
            $request->add_instance($shelveModel);
            $response = $request->send();
            /**
             * @var $shelveModel ShelveModel
             */
            $shelveModel = ModelSerializer::parse($response->getContent(), ShelveModel::class);

//        dd($inventoryModel);

            if (!empty($inputs)) {
                $shelveModel = ModelSerializer::parse($inputs, ShelveModel::class);
                $shelveModel->setShelveId($id);
                $request = new Req(Servers::Inventory, InventoryEntity::Shelve, 'update');
                $request->add_instance($shelveModel);
                $response = $request->send();

//            dd($response);

                if ($response->getStatus() == ResponseStatus::successful) {
                    $this->addFlash('s', '');
                    return $this->redirect($this->generateUrl('inventory_shelve_inventory_shelve_edit', ['id' => $id]));
                } else {
                    $this->addFlash('f', '');
                }
            }

            /**
             * @var $phones PhoneModel[]
             */
            $phones = [];
            if ($shelveModel->getShelvePhones()) {
                foreach ($shelveModel->getShelvePhones() as $phone) {
                    $phones[] = ModelSerializer::parse($phone, PhoneModel::class);
                }
            }

            $allPersonsRequest = new Req(Servers::Repository, Repository::Person, PersonActions::all);
            $allPersonsResponse = $allPersonsRequest->send();

            /**
             * @var $persons PersonModel[]
             */
            $persons = [];
            foreach ($allPersonsResponse->getContent() as $person) {
                $persons[] = ModelSerializer::parse($person, PersonModel::class);
            }

            return $this->render('inventory/shelve/edit.html.twig', [
                'controller_name' => 'InventoryController',
                'shelveModel' => $shelveModel,
                'phones' => $phones,
                'persons' => $persons,

            ]);
        } else {
            return $this->redirect($this->generateUrl('inventory_shelve_inventory_shelve_list'));
        }
    }

    /**
     * @Route("/read/{id}", name="_shelve_read")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \ReflectionException
     */
    public function read($id, Request $request)
    {
        if (AuthUser::if_is_allowed(ServerPermissions::inventory_shelve_fetch)) {


            $inputs = $request->request->all();

            /**
             * @var $shelveModel ShelveModel
             */
            $shelveModel = ModelSerializer::parse($inputs, ShelveModel::class);
            $shelveModel->setShelveId($id);
            $request = new Req(Servers::Inventory, InventoryEntity::Shelve, 'fetch');
            $request->add_instance($shelveModel);
            $response = $request->send();
            $shelveModel = ModelSerializer::parse($response->getContent(), ShelveModel::class);

            /**
             * @var $phones PhoneModel[]
             */
            $phones = [];
            if ($shelveModel->getShelvePhones()) {
                foreach ($shelveModel->getShelvePhones() as $phone) {
                    $phones[] = ModelSerializer::parse($phone, PhoneModel::class);
                }
            }

            /**
             * @var $itemProducts ItemProductsModel[]
             */
            $itemProducts = [];
            if ($shelveModel->getShelveItemProducts()) {
                foreach ($shelveModel->getShelveItemProducts() as $itemProduct) {
                    $itemProducts[] = ModelSerializer::parse($itemProduct, ItemProductsModel::class);
                }
            }


//        dd($itemProducts);

            /**
             * @var $deeds InventoryDeedModel[]
             */
            $deeds = [];
            if ($shelveModel->getShelveDeeds()) {
                foreach ($shelveModel->getShelveDeeds() as $deed) {
                    $deeds[] = ModelSerializer::parse($deed, InventoryDeedModel::class);
                }
            }


            return $this->render('inventory/shelve/read.html.twig', [
                'shelveModel' => $shelveModel,
                'phones' => $phones,
                'itemProducts' => $itemProducts,
                'deeds' => $deeds,
            ]);
        } else {
            return $this->redirect($this->generateUrl('inventory_shelve_inventory_shelve_list'));
        }
    }


    /**
     * @Route("/add-phone/{shelve_id}", name="_shelve_add_phone")
     * @param $shelve_id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ReflectionException
     */
    public function addPhone($shelve_id, Request $request)
    {
        if (AuthUser::if_is_allowed(ServerPermissions::inventory_shelve_add_phone)) {

            $inputs = $request->request->all();
            /**
             * @var $phoneModel PhoneModel
             */
            $phoneModel = ModelSerializer::parse($inputs, PhoneModel::class);
            $phoneModel->setShelveID($shelve_id);
//        dd($phoneModel);
            $request = new Req(Servers::Inventory, InventoryEntity::Shelve, 'add_phone');
            $request->add_instance($phoneModel);
            $response = $request->send();
//        dd($response);
            if ($response->getStatus() == ResponseStatus::successful) {
                $this->addFlash('s', $response->getMessage());
            } else {
                $this->addFlash('f', $response->getMessage());
            }
            return $this->redirect($this->generateUrl('inventory_shelve_inventory_shelve_edit', ['id' => $shelve_id]));

        } else {
            return $this->redirect($this->generateUrl('inventory_shelve_inventory_shelve_edit', ['id' => $shelve_id]));

        }
    }

    /**
     * @Route("/remove-phone/{shelve_id}/{phone_id}", name="_shelve_remove_phone")
     * @param $shelve_id
     * @param $phone_id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ReflectionException
     */
    public function removePhone($shelve_id, $phone_id)
    {
        if (AuthUser::if_is_allowed(ServerPermissions::inventory_shelve_remove_phone)) {


            $phoneModel = new PhoneModel();
            $phoneModel->setShelveID($shelve_id);
            $phoneModel->setId($phone_id);
            $request = new Req(Servers::Inventory, InventoryEntity::Shelve, 'remove_phone');
            $request->add_instance($phoneModel);
            $response = $request->send();
            if ($response->getStatus() == ResponseStatus::successful) {
                $this->addFlash('s', $response->getMessage());
            } else {
                $this->addFlash('f', $response->getMessage());
            }
            return $this->redirect($this->generateUrl('inventory_shelve_inventory_shelve_edit', ['id' => $shelve_id]));
        } else {
            return $this->redirect($this->generateUrl('inventory_shelve_inventory_shelve_edit', ['id' => $shelve_id]));
        }
    }

    /**
     * @Route("/status/{shelve_id}/{machine_name}", name="_shelve_status")
     * @param $shelve_id
     * @param $machine_name
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ReflectionException
     */
    public
    function changeShelveAvailability($shelve_id, $machine_name)
    {
        if (AuthUser::if_is_allowed(ServerPermissions::inventory_shelve_change_status)) {


            $shelveStatusModel = new ShelveStatusModel();
            if ($machine_name == 'active') {
                $shelveStatusModel->setShelveId($shelve_id);
                $shelveStatusModel->setShelveStatusMachineName('deactive');
            } else {
                $shelveStatusModel->setShelveId($shelve_id);
                $shelveStatusModel->setShelveStatusMachineName('active');
            }

            $request = new Req(Servers::Inventory, InventoryEntity::Shelve, 'change_status');
            $request->add_instance($shelveStatusModel);
            $response = $request->send();
//        dd($response);
            if ($response->getStatus() == ResponseStatus::successful) {
                $this->addFlash('s', $response->getMessage());
            } else {
                $this->addFlash('f', $response->getMessage());
            }
            return $this->redirect($this->generateUrl('inventory_shelve_inventory_shelve_list'));
        } else {
            return $this->redirect($this->generateUrl('inventory_shelve_inventory_shelve_list'));
        }
    }

    /**
     * @Route("/item-products-read/{item_id}/{shelve_id}", name="_shelve_item_products_read")
     * @param $item_id
     * @param $shelve_id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \ReflectionException
     */
    public
    function itemProducts($item_id, $shelve_id, Request $request)
    {


        $inputs = $request->request->all();

        /**
         * @var $itemModel ItemModel
         */
        $itemModel = ModelSerializer::parse($inputs, ItemModel::class);
        $itemModel->setItemID($item_id);
        $request = new Req(Servers::Repository, Repository::Item, 'fetch');
        $request->add_instance($itemModel);
        $response = $request->send();

        /**
         * @var $itemModel ItemModel
         */
        $itemModel = ModelSerializer::parse($response->getContent(), ItemModel::class);


        $shelveItemProductsModel = new ShelveItemProductsModel();
        $shelveItemProductsModel->setShelveId($shelve_id);
        $shelveItemProductsModel->setItemId($item_id);

        $shelveItemProductsRequest = new Req(Servers::Inventory, InventoryEntity::Shelve, 'get_shelve_item_products');
        $shelveItemProductsRequest->add_instance($shelveItemProductsModel);
        $shelveItemProductsResponse = $shelveItemProductsRequest->send();

        /**
         * @var $shelveItemProductsModel ShelveItemProductsModel
         */
        $shelveItemProductsModel = ModelSerializer::parse($shelveItemProductsResponse->getContent(), ShelveItemProductsModel::class);


        /**
         * @var $products ProductModel[]
         */
        $products = [];
        foreach ($shelveItemProductsModel->getProducts() as $product) {
            $products[] = ModelSerializer::parse($product, ProductModel::class);
        }


        return $this->render('inventory/shelve/read-item-products.html.twig', [
            'controller_name' => 'InventoryController',
            'itemModel' => $itemModel,
            'products' => $products,

        ]);

    }
}
