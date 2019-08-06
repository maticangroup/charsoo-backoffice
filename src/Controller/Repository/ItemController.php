<?php

namespace App\Controller\Repository;

use App\FormModels\Media\ImageModel;
use App\FormModels\ModelSerializer;
use App\FormModels\Repository\BarcodeModel;
use App\FormModels\Repository\BrandModel;
use App\FormModels\Repository\BrandSuppliersModel;
use App\FormModels\Repository\CompanyModel;
use App\FormModels\Repository\GuaranteeModel;
use App\FormModels\Repository\ItemCategoriesModel;
use App\FormModels\Repository\ItemCategoryModel;
use App\FormModels\Repository\ItemCategorySpecKeyModel;
use App\FormModels\Repository\ItemColorModel;
use App\FormModels\Repository\ItemModel;
use App\FormModels\Repository\ItemTypeModel;
use App\FormModels\Repository\SpecKeyModel;
use App\FormModels\Repository\SpecKeyValueModel;
use App\General\AuthUser;
use App\Permissions\ServerPermissions;
use Matican\Core\Entities\Repository;
use Matican\Core\Servers;
use Matican\Core\Transaction\Response;
use Matican\Core\Transaction\ResponseStatus;
use Matican\Models\Media\Image;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Matican\Core\Transaction\Request as Req;

/**
 * @Route("/repository/item", name="repository_item")
 */
class ItemController extends AbstractController
{
    /**
     * @Route("/list", name="_repository_item_list")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function fetchAll()
    {
        $canCreate = AuthUser::if_is_allowed(ServerPermissions::repository_item_new);
        $canEdit = AuthUser::if_is_allowed(ServerPermissions::repository_item_fetch);
        $canSeeAll = AuthUser::if_is_allowed(ServerPermissions::repository_color_all);

        if ($canSeeAll) {
            $request = new Req(Servers::Repository, Repository::Item, 'all');
            $response = $request->send();

            /**
             * @var $items ItemModel[]
             */
            $items = [];
            foreach ($response->getContent() as $item) {
                $items[] = ModelSerializer::parse($item, ItemModel::class);
            }
            return $this->render('repository/item/list.html.twig', [
                'controller_name' => 'ItemController',
                'items' => $items,
                'canCreate' => $canCreate,
                'canEdit' => $canEdit,
                'canSeeAll' => $canSeeAll,
            ]);
        } else {
            return $this->redirect($this->generateUrl('repository_item_repository_item_create'));
        }


    }

    /**
     * @Route("/create", name="_repository_item_create")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \ReflectionException
     */
    public function create(Request $request)
    {
        $canCreate = AuthUser::if_is_allowed(ServerPermissions::repository_item_new);

        if ($canCreate) {

            $inputs = $request->request->all();

            /**
             * @var $itemModel ItemModel
             */
            $itemModel = ModelSerializer::parse($inputs, ItemModel::class);
//        dd($itemModel);
            if (!empty($inputs)) {
//            dd($inputs);
                $request = new Req(Servers::Repository, Repository::Item, 'new');
                $request->add_instance($itemModel);
                $response = $request->send();
//            dd($response);
                if ($response->getStatus() == ResponseStatus::successful) {
                    /**
                     * @var $itemModel ItemModel
                     */
                    $itemModel = ModelSerializer::parse($response->getContent(), ItemModel::class);
                    $this->addFlash('s', $response->getMessage());
                    return $this->redirect($this->generateUrl('repository_item_repository_item_edit', ['id' => $itemModel->getItemID()]));
                }
                $this->addFlash('f', $response->getMessage());
            }


            $allBrandsRequest = new Req(Servers::Repository, Repository::Brand, 'all');
            $allBrandsResponse = $allBrandsRequest->send();

            /**
             * @var $brands BrandModel[]
             */
            $brands = [];
            foreach ($allBrandsResponse->getContent() as $brand) {
                $brands[] = ModelSerializer::parse($brand, BrandModel::class);
            }

            $allItemTypesRequest = new Req(Servers::Repository, Repository::Item, 'get_types');
            $allItemTypesResponse = $allItemTypesRequest->send();

            /**
             * @var $itemTypes ItemTypeModel[]
             */
            $itemTypes = [];
            foreach ($allItemTypesResponse->getContent() as $itemType) {
                $itemTypes[] = ModelSerializer::parse($itemType, ItemTypeModel::class);
            }

            return $this->render('repository/item/create.html.twig', [
                'controller_name' => 'ItemController',
                'itemModel' => $itemModel,
                'brands' => $brands,
                'itemTypes' => $itemTypes,
                'canCreate' => $canCreate
            ]);
        } else {
            return $this->redirect($this->generateUrl('repository_item_repository_item_list'));
        }

    }

    /**
     * @Route("/edit/{id}", name="_repository_item_edit")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \ReflectionException
     */
    public function edit($id, Request $request)
    {
        $canUpdate = AuthUser::if_is_allowed(ServerPermissions::repository_item_update);

        if ($canUpdate) {
            $inputs = $request->request->all();

            /**
             * @var $itemModel ItemModel
             */
            $itemModel = ModelSerializer::parse($inputs, ItemModel::class);
            $itemModel->setItemID($id);
            $request = new Req(Servers::Repository, Repository::Item, 'fetch');
            $request->add_instance($itemModel);
            $response = $request->send();
            $itemModel = ModelSerializer::parse($response->getContent(), ItemModel::class);
//        dd($itemModel);

            if (!empty($inputs)) {
                $itemModel = ModelSerializer::parse($inputs, ItemModel::class);
                $itemModel->setItemID($id);
                $request = new Req(Servers::Repository, Repository::Item, 'update');
                $request->add_instance($itemModel);
                $response = $request->send();
                if ($response->getStatus() == ResponseStatus::successful) {
                    $this->addFlash('s', $response->getMessage());
                    return $this->redirect($this->generateUrl('repository_item_repository_item_edit', ['id' => $id]));
                } else {
                    $this->addFlash('f', $response->getMessage());
                }
            }

            $allBrandsRequest = new Req(Servers::Repository, Repository::Brand, 'all');
            $allBrandsResponse = $allBrandsRequest->send();

            /**
             * @var $brands BrandModel[]
             */
            $brands = [];
            foreach ($allBrandsResponse->getContent() as $brand) {
                $brands[] = ModelSerializer::parse($brand, BrandModel::class);
            }

            $allItemTypesRequest = new Req(Servers::Repository, Repository::Item, 'get_types');
            $allItemTypesResponse = $allItemTypesRequest->send();

            /**
             * @var $itemTypes ItemTypeModel[]
             */
            $itemTypes = [];
            if ($allItemTypesResponse->getContent()) {
                foreach ($allItemTypesResponse->getContent() as $itemType) {
                    $itemTypes[] = ModelSerializer::parse($itemType, ItemTypeModel::class);
                }
            }


            $allColorsRequest = new Req(Servers::Repository, Repository::Color, 'all');
            $allColorsResponse = $allColorsRequest->send();

            /**
             * @var $colors ItemColorModel[]
             */
            $colors = [];
            if ($allColorsResponse->getContent()) {
                foreach ($allColorsResponse->getContent() as $color) {
                    $colors[] = ModelSerializer::parse($color, ItemColorModel::class);
                }
            }


            $guaranteeRequest = new Req(Servers::Repository, Repository::Guarantee, 'all');
            $guaranteeResponse = $guaranteeRequest->send();

            /**
             * @var $guarantees GuaranteeModel[]
             */
            $guarantees = [];
            foreach ($guaranteeResponse->getContent() as $guarantee) {
                $guarantees[] = ModelSerializer::parse($guarantee, GuaranteeModel::class);
            }

            $brandSuppliersModel = new BrandSuppliersModel();
            $brandSuppliersModel->setBrandId($itemModel->getItemBrandId());
            $supplierRequest = new Req(Servers::Repository, Repository::Brand, 'get_suppliers');
            $supplierRequest->add_instance($brandSuppliersModel);
            $supplierResponse = $supplierRequest->send();
            $supplierContent = $supplierResponse->getContent();
//            dd();
//        dd($supplierContent);

            /**
             * @var $suppliers CompanyModel[]
             */
            $suppliers = [];
            if ($supplierContent) {
                if ($supplierContent['brandSuppliers']) {
                    foreach ($supplierContent['brandSuppliers'] as $supplier) {
                        $suppliers[] = ModelSerializer::parse($supplier, CompanyModel::class);
                    }
                }
            }

//        dd($suppliers);

            $allItemCategoriesRequest = new Req(Servers::Repository, Repository::ItemCategory, 'all');
            $allItemCategoriesResponse = $allItemCategoriesRequest->send();


            $itemCategories = json_decode(json_encode($allItemCategoriesResponse->getContent()), true);

//            dd($itemCategories);

            foreach ($itemCategories as $key => $itemCategory) {
//                dd($itemCategory);
//                /**
//                 * @var $itemCategoryModel ItemCategoryModel
//                 */
//                $itemCategoryModel = ModelSerializer::parse($itemCategory, ItemCategoryModel::class);
                if ($itemModel->getItemCategoriesIds()) {
                    if (in_array($itemCategory['category'][0]['itemCategoryID'], $itemModel->getItemCategoriesIds())) {
                        $itemCategories[$key]['category']['is_checked'] = true;
                    }
                }

            }

//        print_r(json_encode($itemModel->getItemSpecGroupsKeys())); die('s');

            $specGroupsKeys = json_decode(json_encode($itemModel->getItemSpecGroupsKeys()), true);
//        print_r($specGroupsKeys); die;

            /**
             * @var $itemImages ImageModel[]
             */
            $itemImages = [];
            if ($itemModel->getItemImages()) {
                foreach ($itemModel->getItemImages() as $itemImage) {
                    $itemImages[] = ModelSerializer::parse($itemImage, ImageModel::class);
                }
            }

            return $this->render('repository/item/edit.html.twig', [
                'controller_name' => 'ItemController',
                'itemModel' => $itemModel,
                'brands' => $brands,
                'itemTypes' => $itemTypes,
                'colors' => $colors,
                'guarantees' => $guarantees,
                'suppliers' => $suppliers,
                'itemCategories' => $itemCategories,
                'specGroupKeys' => $specGroupsKeys,
                'canUpdate' => $canUpdate,
                'itemImages' => $itemImages,
            ]);
        } else {
            return $this->redirect($this->generateUrl('repository_item_repository_item_list'));
        }


    }

    /**
     * @Route("/duplicate/{id}", name="_duplicate")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ReflectionException
     */
    public function duplicate($id)
    {
        $itemModel = new ItemModel();
        $itemModel->setItemID($id);
        $request = new Req(Servers::Repository, Repository::Item, 'duplicate');
        $request->add_instance($itemModel);
        $response = $request->send();
        if ($response->getStatus() == ResponseStatus::successful) {
            $this->addFlash('s', $response->getMessage());
        } else {
            $this->addFlash('f', $response->getMessage());
        }
        return $this->redirect($this->generateUrl('repository_item_repository_item_list'));
    }


    /**
     * @Route("/add_barcode/{item_id}", name="_repository_item_add_barcode")
     * @param $item_id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ReflectionException
     */
    public function addBarcode($item_id, Request $request)
    {
        $inputs = $request->request->all();

        /**
         * @var $barcodeModel BarcodeModel
         */
        $barcodeModel = ModelSerializer::parse($inputs, BarcodeModel::class);
        $barcodeModel->setItemId($item_id);
        $request = new Req(Servers::Repository, Repository::Item, 'add_barcode');
        $request->add_instance($barcodeModel);
        $response = $request->send();
//        dd($response);
        if ($response->getStatus() == ResponseStatus::successful) {
            $this->addFlash('s', $response->getMessage());
        } else {
            $this->addFlash('f', $response->getMessage());
        }
        return $this->redirect($this->generateUrl('repository_item_repository_item_edit', ['id' => $item_id]));


    }

    /**
     * @Route("/remove_barcode/{barcode_id}/{item_id}", name="_repository_item_remove_barcode")
     * @param $barcode_id
     * @param $item_id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ReflectionException
     */
    public function removeBarcode($barcode_id, $item_id)
    {
        $barcodeModel = new BarcodeModel();
        $barcodeModel->setBarcodeId($barcode_id);
        $barcodeModel->setItemId($item_id);
        $request = new Req(Servers::Repository, Repository::Item, 'remove_barcode');
        $request->add_instance($barcodeModel);
        $response = $request->send();
        if ($response->getStatus() == ResponseStatus::successful) {
            $this->addFlash('s', $response->getMessage());
        } else {
            $this->addFlash('f', $response->getMessage());
        }
        return $this->redirect($this->generateUrl('repository_item_repository_item_edit', ['id' => $item_id]));
    }


    /**
     * @Route("/add_color/{item_id}", name="_repository_item_add_color")
     * @param $item_id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ReflectionException
     */
    public function addColor($item_id, Request $request)
    {
        $inputs = $request->request->all();

        /**
         * @var $colorModel ItemColorModel
         */
        $colorModel = ModelSerializer::parse($inputs, ItemColorModel::class);
        $colorModel->setItemId($item_id);
        $request = new Req(Servers::Repository, Repository::Item, 'add_available_color');
        $request->add_instance($colorModel);
        $response = $request->send();
//        dd($response);
        if ($response->getStatus() == ResponseStatus::successful) {
            $this->addFlash('s', $response->getMessage());
        } else {
            $this->addFlash('f', $response->getMessage());
        }
        return $this->redirect($this->generateUrl('repository_item_repository_item_edit', ['id' => $item_id]));
    }


    /**
     * @Route("/remove_color/{color_id}/{item_id}", name="_repository_item_remove_color")
     * @param $color_id
     * @param $item_id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ReflectionException
     */
    public function removeColor($color_id, $item_id)
    {
        $colorModel = new ItemColorModel();
        $colorModel->setItemColorID($color_id);
        $colorModel->setItemId($item_id);
        $request = new Req(Servers::Repository, Repository::Item, 'remove_available_color');
        $request->add_instance($colorModel);
        $response = $request->send();
        if ($response->getStatus() == ResponseStatus::successful) {
            $this->addFlash('s', $response->getMessage());
        } else {
            $this->addFlash('f', $response->getMessage());
        }
        return $this->redirect($this->generateUrl('repository_item_repository_item_edit', ['id' => $item_id]));
    }


    /**
     * @Route("/add_guarantee/{item_id}", name="_repository_item_add_guarantee")
     * @param $item_id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ReflectionException
     */
    public function addGuarantee($item_id, Request $request)
    {
        $inputs = $request->request->all();

        /**
         * @var $guaranteeModel GuaranteeModel
         */
        $guaranteeModel = ModelSerializer::parse($inputs, GuaranteeModel::class);
        $guaranteeModel->setItemId($item_id);
        $request = new Req(Servers::Repository, Repository::Item, 'add_available_guarantee');
        $request->add_instance($guaranteeModel);
        $response = $request->send();
        if ($response->getStatus() == ResponseStatus::successful) {
            $this->addFlash('s', $response->getMessage());
        } else {
            $this->addFlash('f', $response->getMessage());
        }
        return $this->redirect($this->generateUrl('repository_item_repository_item_edit', ['id' => $item_id]));
    }


    /**
     * @Route("/remove_guarantee/{guarantee_id}/{item_id}", name="_repository_item_remove_guarantee")
     * @param $guarantee_id
     * @param $item_id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ReflectionException
     */
    public function removeGuarantee($guarantee_id, $item_id)
    {
        $guaranteeModel = new GuaranteeModel();
        $guaranteeModel->setGuaranteeID($guarantee_id);
        $guaranteeModel->setItemId($item_id);
//        dd($guaranteeModel);
        $request = new Req(Servers::Repository, Repository::Item, 'remove_available_guarantee');
        $request->add_instance($guaranteeModel);
//        dd($request);
        $response = $request->send();
//        dd($response);
        if ($response->getStatus() == ResponseStatus::successful) {
            $this->addFlash('s', $response->getMessage());
        } else {
            $this->addFlash('f', $response->getMessage());
        }
        return $this->redirect($this->generateUrl('repository_item_repository_item_edit', ['id' => $item_id]));
    }


    /**
     * @Route("/add_supplier/{item_id}", name="_repository_item_add_supplier")
     * @param $item_id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ReflectionException
     */
    public function addSupplier($item_id, Request $request)
    {
        $inputs = $request->request->all();

        /**
         * @var $supplierModel CompanyModel
         */
        $supplierModel = ModelSerializer::parse($inputs, CompanyModel::class);
        $supplierModel->setItemId($item_id);
        $request = new Req(Servers::Repository, Repository::Item, 'add_available_supplier');
        $request->add_instance($supplierModel);
        $response = $request->send();
        if ($response->getStatus() == ResponseStatus::successful) {
            $this->addFlash('s', $response->getMessage());
        } else {
            $this->addFlash('f', $response->getMessage());
        }
        return $this->redirect($this->generateUrl('repository_item_repository_item_edit', ['id' => $item_id]));
    }


    /**
     * @Route("/remove_supplier/{supplier_id}/{item_id}", name="_repository_item_remove_supplier")
     * @param $supplier_id
     * @param $item_id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ReflectionException
     */
    public function removeSupplier($supplier_id, $item_id)
    {
        $supplierModel = new CompanyModel();
        $supplierModel->setCompanyID($supplier_id);
        $supplierModel->setItemId($item_id);
        $request = new Req(Servers::Repository, Repository::Item, 'remove_available_supplier');
        $request->add_instance($supplierModel);
        $response = $request->send();
        if ($response->getStatus() == ResponseStatus::successful) {
            $this->addFlash('s', $response->getMessage());
        } else {
            $this->addFlash('f', $response->getMessage());
        }
        return $this->redirect($this->generateUrl('repository_item_repository_item_edit', ['id' => $item_id]));
    }

    /**
     * @Route("/add_image/{item_id}", name="_repository_item_add_image")
     * @param Request $request
     * @param $item_id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addImage(Request $request, $item_id)
    {
        $file = $request->files->get('item_image');

        $uploadRequest = new Req(Servers::Repository, Repository::Item, 'add_image');
        $itemModel = new ItemModel();
        $itemModel->setItemID($item_id);
        /**
         * @var $uploadResponse Response
         */
        $uploadResponse = $uploadRequest->uploadImage($file, $itemModel);
        if ($uploadResponse->getStatus() == ResponseStatus::successful) {
            $this->addFlash('s', $uploadResponse->getMessage());
        }
        $this->addFlash('f', $uploadResponse->getMessage());
        return $this->redirect($this->generateUrl('repository_item_repository_item_edit', ['id' => $item_id]));
    }

    /**
     * @Route("/remove_image/{image_id}/{item_id}", name="_repository_item_remove_image")
     * @param $image_id
     * @param $item_id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ReflectionException
     */
    public function removeImage($image_id, $item_id)
    {
        $imageModel = new ImageModel();
        $imageModel->setImageSerial($image_id);
        $imageModel->setItemID($item_id);
        $request = new Req(Servers::Repository, Repository::Item, 'remove_image');
        $request->add_instance($imageModel);
//        dd($request);
        $response = $request->send();
//        dd($response);
        if ($response->getStatus() == ResponseStatus::successful) {
            $this->addFlash('s', $response->getMessage());
        } else {
            $this->addFlash('f', $response->getMessage());
        }
        return $this->redirect($this->generateUrl('repository_item_repository_item_edit', ['id' => $item_id]));

    }

    /**
     * @Route("/update_categories/{item_id}", name="_repository_item_update_categories")
     * @param $item_id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ReflectionException
     */
    public function updateCategories($item_id, Request $request)
    {
        $inputs = $request->request->all();

        /**
         * @var $itemCategoriesModel ItemCategoriesModel
         */
        $itemCategoriesModel = ModelSerializer::parse($inputs, ItemCategoriesModel::class);
        $itemCategoriesModel->setItemId($item_id);
//        dd($itemCategoriesModel);
        $request = new Req(Servers::Repository, Repository::Item, 'update_category');
        $request->add_instance($itemCategoriesModel);
        $response = $request->send();
//        dd($response);
        if ($response->getStatus() == ResponseStatus::successful) {
            $this->addFlash('s', $response->getMessage());
        } else {
            $this->addFlash('s', $response->getMessage());
        }

        return $this->redirect($this->generateUrl('repository_item_repository_item_edit', ['id' => $item_id]));
    }


    /**
     * @Route("/submit-key-value", name="_submit_key_value")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ReflectionException
     */
    public function submitKeyValue(Request $request)
    {
        $inputs = $request->request->all();
//        dd($inputs);
        /**
         * @var $specKeyValueModel SpecKeyValueModel
         */
        $specKeyValueModel = ModelSerializer::parse($inputs, SpecKeyValueModel::class);
//        $specKeyModel->setSpecKeySpecGroupName();
        $request = new Req(Servers::Repository, Repository::SpecKey, 'submit_key_value');
        $request->add_instance($specKeyValueModel);
        $response = $request->send();
        if ($response->getStatus() == ResponseStatus::successful) {
            $this->addFlash('s', $response->getMessage());
        } else {
            $this->addFlash('s', $response->getMessage());
        }

        return $this->redirect($this->generateUrl('repository_item_repository_item_edit', ['id' => $specKeyValueModel->getItemId()]));
    }

    /**
     * @Route("/remove-key-value/{item_id}/{key_id}/{value}", name="_remove_key_value")
     * @param $item_id
     * @param $key_id
     * @param $value
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ReflectionException
     */
    public function removeKeyValue($item_id, $key_id, $value)
    {
        $keyValueModel = new SpecKeyValueModel();
        $keyValueModel->setItemId($item_id);
        $keyValueModel->setKeyId($key_id);
        $keyValueModel->setValue($value);

//        dd($keyValueModel);

        $request = new Req(Servers::Repository, Repository::SpecKey, 'remove_key_value');
        $request->add_instance($keyValueModel);
        $response = $request->send();
//        dd($response);
        if ($response->getStatus() == ResponseStatus::successful) {
            $this->addFlash('s', $response->getMessage());
        } else {
            $this->addFlash('s', $response->getMessage());
        }

        return $this->redirect($this->generateUrl('repository_item_repository_item_edit', ['id' => $item_id]));
    }
}
