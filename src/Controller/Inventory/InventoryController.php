<?php

namespace App\Controller\Inventory;

use App\FormModels\Inventory\InventoryDeedModel;
use App\FormModels\Inventory\InventoryItemProductsModel;
use App\FormModels\Inventory\InventoryModel;
use App\FormModels\Inventory\InventoryStatusModel;
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
use Matican\Models\Inventory\Inventory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Matican\Core\Transaction\Request as Req;
use Matican\Core\Entities\Inventory as InventoryEntity;
use Symfony\Component\Serializer\Tests\Model;

/**
 * @Route("/inventory", name="inventory")
 */
class InventoryController extends AbstractController
{

    /**
     * @Route("/list", name="_inventory_list")
     */
    public function fetchAll()
    {
        $canSeeAll = AuthUser::if_is_allowed(ServerPermissions::inventory_inventory_all);
        $canCreateNew = AuthUser::if_is_allowed(ServerPermissions::inventory_inventory_new);
        $canUpdate = AuthUser::if_is_allowed(ServerPermissions::inventory_inventory_update);
        $canRead = AuthUser::if_is_allowed(ServerPermissions::accounting_coupongroup_fetch);
        $canChangeStatus = AuthUser::if_is_allowed(ServerPermissions::inventory_inventory_change_status);
        if ($canSeeAll) {


            $inventories = [];
            if ($canSeeAll) {
                $request = new Req(Servers::Inventory, InventoryEntity::Inventory, 'all');
                $response = $request->send();

                /**
                 * @var $inventories InventoryModel[]
                 */
                foreach ($response->getContent() as $inventory) {
                    $inventories[] = ModelSerializer::parse($inventory, InventoryModel::class);
                }
            }

            return $this->render('inventory/inventory/list.html.twig', [
                'controller_name' => 'InventoryController',
                'inventories' => $inventories,
                'canCreateNew' => $canCreateNew,
                'canSeeAll' => $canSeeAll,
                'canUpdate' => $canUpdate,
                'canRead' => $canRead,
                'canChangeStatus' => $canChangeStatus
            ]);
        } else {
            return $this->redirect($this->generateUrl('inventory_deed_inventory_deed_create'));
        }
    }

    /**
     * @Route("/create", name="_inventory_create")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \ReflectionException
     */
    public function create(Request $request)
    {
        $canCreate = AuthUser::if_is_allowed(ServerPermissions::inventory_inventory_new);
        if ($canCreate) {
            $inputs = $request->request->all();

            /**
             * @var $inventoryModel InventoryModel
             */
            $inventoryModel = ModelSerializer::parse($inputs, InventoryModel::class);
            if (!empty($inputs)) {
                $request = new Req(Servers::Inventory, InventoryEntity::Inventory, 'new');
                $request->add_instance($inventoryModel);
                $response = $request->send();
                if ($response->getStatus() == ResponseStatus::successful) {
                    /**
                     * @var $inventoryModel InventoryModel
                     */
                    $inventoryModel = ModelSerializer::parse($response->getContent(), InventoryModel::class);
                    $this->addFlash('success', $response->getMessage());
                    return $this->redirect($this->generateUrl('inventory_inventory_edit', ['id' => $inventoryModel->getInventoryId()]));
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


            return $this->render('inventory/inventory/create.html.twig', [
                'controller_name' => 'InventoryController',
                'inventoryModel' => $inventoryModel,
                'persons' => $persons,
            ]);
        } else {
            return $this->redirect($this->generateUrl('inventory_inventory_list'));
        }
    }


    /**
     * @Route("/edit/{id}", name="_inventory_edit")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \ReflectionException
     */
    public function edit($id, Request $request)
    {
        if (AuthUser::if_is_allowed(ServerPermissions::inventory_inventory_update)) {

            $inputs = $request->request->all();

            /**
             * @var $inventoryModel InventoryModel
             */
            $inventoryModel = ModelSerializer::parse($inputs, InventoryModel::class);
            $inventoryModel->setInventoryId($id);
            $request = new Req(Servers::Inventory, InventoryEntity::Inventory, 'fetch');
            $request->add_instance($inventoryModel);
            $response = $request->send();
            $inventoryModel = ModelSerializer::parse($response->getContent(), InventoryModel::class);

//        dd($inventoryModel);

            if (!empty($inputs)) {
                $inventoryModel = ModelSerializer::parse($inputs, InventoryModel::class);
                $inventoryModel->setInventoryId($id);
                $request = new Req(Servers::Inventory, InventoryEntity::Inventory, 'update');
                $request->add_instance($inventoryModel);
                $response = $request->send();
                if ($response->getStatus() == ResponseStatus::successful) {
                    $this->addFlash('s', '');
                    return $this->redirect($this->generateUrl('inventory_inventory_edit', ['id' => $id]));
                } else {
                    $this->addFlash('f', '');
                }
            }

            /**
             * @var $phones PhoneModel[]
             */
            $phones = [];
            if ($inventoryModel->getInventoryPhones()) {
                foreach ($inventoryModel->getInventoryPhones() as $phone) {
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

            return $this->render('inventory/inventory/edit.html.twig', [
                'controller_name' => 'InventoryController',
                'inventoryModel' => $inventoryModel,
                'phones' => $phones,
                'persons' => $persons,

            ]);
        } else {
            return $this->redirect($this->generateUrl('inventory_inventory_list'));
        }

    }

    /**
     * @Route("/read/{id}", name="_inventory_read")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \ReflectionException
     */
    public
    function read($id, Request $request)
    {
        if (AuthUser::if_is_allowed(ServerPermissions::inventory_inventory_fetch)) {
            $inputs = $request->request->all();
            /**
             * @var $inventoryModel InventoryModel
             */
            $inventoryModel = ModelSerializer::parse($inputs, InventoryModel::class);
            $inventoryModel->setInventoryId($id);
            $request = new Req(Servers::Inventory, InventoryEntity::Inventory, 'fetch');
            $request->add_instance($inventoryModel);
            $response = $request->send();
            $inventoryModel = ModelSerializer::parse($response->getContent(), InventoryModel::class);

            /**
             * @var $phones PhoneModel[]
             */
            $phones = [];
            if ($inventoryModel->getInventoryPhones()) {
                foreach ($inventoryModel->getInventoryPhones() as $phone) {
                    $phones[] = ModelSerializer::parse($phone, PhoneModel::class);
                }
            }

            /**
             * @var $itemProducts ItemProductsModel[]
             */
            $itemProducts = [];
            if ($inventoryModel->getInventoryItemProducts()) {
                foreach ($inventoryModel->getInventoryItemProducts() as $itemProduct) {
                    $itemProducts[] = ModelSerializer::parse($itemProduct, ItemProductsModel::class);
                }
            }


            /**
             * @var $deeds InventoryDeedModel[]
             */
            $deeds = [];
            if ($inventoryModel->getInventoryDeeds()) {
                foreach ($inventoryModel->getInventoryDeeds() as $deed) {
                    $deeds[] = ModelSerializer::parse($deed, InventoryDeedModel::class);
                }
            }


            return $this->render('inventory/inventory/read.html.twig', [
                'controller_name' => 'InventoryController',
                'inventoryModel' => $inventoryModel,
                'phones' => $phones,
                'itemProducts' => $itemProducts,
                'deeds' => $deeds,
            ]);
        } else {
            return $this->redirect($this->generateUrl('inventory_inventory_list'));
        }
    }


    /**
     * @Route("/add-phone/{inventory_id}", name="_inventory_add_phone")
     * @param $inventory_id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ReflectionException
     */
    public
    function addPhone($inventory_id, Request $request)
    {
        if (AuthUser::if_is_allowed(ServerPermissions::inventory_inventory_add_phone)) {
            $inputs = $request->request->all();
            /**
             * @var $phoneModel PhoneModel
             */
            $phoneModel = ModelSerializer::parse($inputs, PhoneModel::class);
            $phoneModel->setInventoryID($inventory_id);
            $request = new Req(Servers::Inventory, InventoryEntity::Inventory, 'add_phone');
            $request->add_instance($phoneModel);
            $response = $request->send();
            if ($response->getStatus() == ResponseStatus::successful) {
                $this->addFlash('s', $response->getMessage());
            } else {
                $this->addFlash('f', $response->getMessage());
            }
        }
        return $this->redirect($this->generateUrl('inventory_inventory_edit', ['id' => $inventory_id]));
    }

    /**
     * @Route("/remove-phone/{inventory_id}/{phone_id}", name="_inventory_remove_phone")
     * @param $inventory_id
     * @param $phone_id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ReflectionException
     */
    public
    function removePhone($inventory_id, $phone_id)
    {
        if (AuthUser::if_is_allowed(ServerPermissions::inventory_inventory_remove_phone)) {
            $phoneModel = new PhoneModel();
            $phoneModel->setInventoryID($inventory_id);
            $phoneModel->setId($phone_id);
            $request = new Req(Servers::Inventory, InventoryEntity::Inventory, 'remove_phone');
            $request->add_instance($phoneModel);
            $response = $request->send();
            if ($response->getStatus() == ResponseStatus::successful) {
                $this->addFlash('s', $response->getMessage());
            } else {
                $this->addFlash('f', $response->getMessage());
            }
        }
        return $this->redirect($this->generateUrl('inventory_inventory_edit', ['id' => $inventory_id]));
    }

    /**
     * @Route("/status/{inventory_id}/{machine_name}", name="_inventory_status")
     * @param $inventory_id
     * @param $machine_name
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ReflectionException
     */
    public
    function changeInventoryAvailability($inventory_id, $machine_name)
    {
        if (AuthUser::if_is_allowed(ServerPermissions::inventory_inventory_change_status)) {
            $inventoryStatusModel = new InventoryStatusModel();
            if ($machine_name == 'active') {
                $inventoryStatusModel->setInventoryId($inventory_id);
                $inventoryStatusModel->setInventoryStatusMachineName('deactive');
            } else {
                $inventoryStatusModel->setInventoryId($inventory_id);
                $inventoryStatusModel->setInventoryStatusMachineName('active');
            }

            $request = new Req(Servers::Inventory, InventoryEntity::Inventory, 'change_status');
            $request->add_instance($inventoryStatusModel);
            $response = $request->send();
//        dd($response);
            if ($response->getStatus() == ResponseStatus::successful) {
                $this->addFlash('s', $response->getMessage());
            } else {
                $this->addFlash('f', $response->getMessage());
            }
        }
        return $this->redirect($this->generateUrl('inventory_inventory_list'));
    }

    /**
     * @Route("/item-products-read/{item_id}/{inventory_id}", name="_inventory_item_products_read")
     * @param $item_id
     * @param $inventory_id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \ReflectionException
     */
    public
    function itemProducts($item_id, $inventory_id, Request $request)
    {
        if (AuthUser::if_is_allowed(ServerPermissions::inventory_inventory_get_item_products)) {

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


            $inventoryItemProductsModel = new InventoryItemProductsModel();
            $inventoryItemProductsModel->setInventoryId($inventory_id);
            $inventoryItemProductsModel->setItemId($item_id);
            $inventoryItemProductsRequest = new Req(Servers::Inventory, InventoryEntity::Inventory, 'get_inventory_item_products');
            $inventoryItemProductsRequest->add_instance($inventoryItemProductsModel);
            $inventoryItemProductsResponse = $inventoryItemProductsRequest->send();

            /**
             * @var $inventoryItemProductsModel InventoryItemProductsModel
             */
            $inventoryItemProductsModel = ModelSerializer::parse($inventoryItemProductsResponse->getContent(), InventoryItemProductsModel::class);


            /**
             * @var $products ProductModel[]
             */
            $products = [];
            foreach ($inventoryItemProductsModel->getProducts() as $product) {
                $products[] = ModelSerializer::parse($product, ProductModel::class);
            }


            return $this->render('inventory/inventory/read-item-products.html.twig', [
                'controller_name' => 'InventoryController',
                'itemModel' => $itemModel,
                'products' => $products,

            ]);
        } else {
            return $this->redirect($this->generateUrl('inventory_inventory_edit', ['id' => $inventory_id]));
        }
    }
}
