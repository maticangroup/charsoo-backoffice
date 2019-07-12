<?php

namespace App\Controller\Repository;

use App\FormModels\ModelSerializer;
use App\FormModels\Repository\ItemCategoryModel;
use App\FormModels\Repository\ItemCategorySpecKeyModel;
use App\FormModels\Repository\SizeModel;
use App\FormModels\Repository\SpecGroupModel;
use App\FormModels\Repository\SpecKeyModel;
use Matican\Core\Entities\Repository;
use Matican\Core\Servers;
use Matican\Core\Transaction\ResponseStatus;
use Matican\Models\Repository\ItemCategory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Matican\Core\Transaction\Request as Req;

/**
 * @Route("/repository/item-category", name="repository_item_category")
 */
class ItemCategoryController extends AbstractController
{

    /**
     * @Route("/create", name="_repository_item_category_create")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \ReflectionException
     */
    public function create(Request $request)
    {
        $inputs = $request->request->all();

        /**
         * @var $itemCategoryModel ItemCategoryModel
         */
        $itemCategoryModel = ModelSerializer::parse($inputs, ItemCategoryModel::class);

        if (!empty($inputs)) {
            $request = new Req(Servers::Repository, Repository::ItemCategory, 'new');
            $request->add_instance($itemCategoryModel);
            $response = $request->send();
            if ($response->getStatus() == ResponseStatus::successful) {
                /**
                 * @var $newItemCategory ItemCategoryModel
                 */
                $newItemCategory = ModelSerializer::parse($response->getContent(), ItemCategoryModel::class);
                $this->addFlash('s', $response->getMessage());
                return $this->redirect($this->generateUrl('repository_item_category_repository_item_category_edit', ['id' => $newItemCategory->getItemCategoryID()]));
            }
            $this->addFlash('f', $response->getMessage());
        }

        $allItemCategoriesRequest = new Req(Servers::Repository, Repository::ItemCategory, 'all');
        $allItemCategoriesResponse = $allItemCategoriesRequest->send();
        /**
         * @var $itemCategories ItemCategoryModel[]
         */
        $itemCategories = [];
        foreach ($allItemCategoriesResponse->getContent() as $itemCategory) {
            $itemCategories[] = ModelSerializer::parse($itemCategory, ItemCategoryModel::class);
        }

        $allSizesRequest = new Req(Servers::Repository, Repository::Size, 'all');
        $allSizesResponse = $allSizesRequest->send();
        /**
         * @var $sizes SizeModel[]
         */
        $sizes = [];
        foreach ($allSizesResponse->getContent() as $size) {
            $sizes[] = ModelSerializer::parse($size, SizeModel::class);
        }


        return $this->render('repository/item_category/create.html.twig', [
            'controller_name' => 'ItemCategoryController',
            'itemCategoryModel' => $itemCategoryModel,
            'itemCategories' => $itemCategories,
            'sizes' => $sizes,

        ]);
    }


    /**
     * @Route("/edit/{id}", name="_repository_item_category_edit")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \ReflectionException
     */
    public function edit($id, Request $request)
    {
        $inputs = $request->request->all();
        /**
         * @var $itemCategoryModel ItemCategoryModel
         */
        $itemCategoryModel = ModelSerializer::parse($inputs, ItemCategoryModel::class);
        $itemCategoryModel->setItemCategoryID($id);
        $request = new Req(Servers::Repository, Repository::ItemCategory, 'fetch');
        $request->add_instance($itemCategoryModel);
        $response = $request->send();
//        dd($response);
        $itemCategoryModel = ModelSerializer::parse($response->getContent(), ItemCategoryModel::class);

        if (!empty($inputs)) {
            $itemCategoryModel = ModelSerializer::parse($inputs, ItemCategoryModel::class);
            $itemCategoryModel->setItemCategoryID($id);
//            dd($itemCategoryModel);
            $request = new Req(Servers::Repository, Repository::ItemCategory, 'update');
            $request->add_instance($itemCategoryModel);
            $response = $request->send();
//            dd($response);
            if ($response->getStatus() == ResponseStatus::successful) {
                $this->addFlash('s', $response->getMessage());
            }
            $this->addFlash('f', $response->getMessage());
        }

        $allItemCategoriesRequest = new Req(Servers::Repository, Repository::ItemCategory, 'all');
        $allItemCategoriesResponse = $allItemCategoriesRequest->send();
        /**
         * @var $itemCategories ItemCategoryModel[]
         */
        $itemCategories = [];
        foreach ($allItemCategoriesResponse->getContent() as $itemCategory) {
            $itemCategories[] = ModelSerializer::parse($itemCategory, ItemCategoryModel::class);
        }

        $allSpecGroupsRequest = new Req(Servers::Repository, Repository::SpecGroup, 'all');
        $allSpecGroupResponse = $allSpecGroupsRequest->send();

        $itemCategorySpecKeyIds = $itemCategoryModel->getItemCategorySpecKeysIds();

        /**
         * @var $specGroups SpecGroupModel[]
         */
        $specGroups = [];
        foreach ($allSpecGroupResponse->getContent() as $specGroup) {

            /**
             * @var $specGroupModel SpecGroupModel
             */
            $specGroupModel = ModelSerializer::parse($specGroup, SpecGroupModel::class);
            if ($specGroupModel->getSpecGroupSpecKeys()) {
                $keys = $specGroupModel->getSpecGroupSpecKeys();
                foreach ($keys as $key => $specKey) {
                    /**
                     * @var $specKeyModel SpecKeyModel
                     */
                    $specKeyModel = ModelSerializer::parse($specKey, SpecKeyModel::class);
                    if (in_array($specKeyModel->getSpecKeyID(), $itemCategorySpecKeyIds)) {

                        $specKeyModel->setSpecKeyIsChecked(true);
                    }
//                    dd($specKeyModel);
                    $keys[$key] = $specKeyModel;

                }
                $specGroupModel->setSpecGroupSpecKeys($keys);
            }
            $specGroups[] = $specGroupModel;
        }

//        dd($specGroups);

        $allSizesRequest = new Req(Servers::Repository, Repository::Size, 'all');
        $allSizesResponse = $allSizesRequest->send();
        /**
         * @var $sizes SizeModel[]
         */
        $sizes = [];
        foreach ($allSizesResponse->getContent() as $size) {
            $sizes[] = ModelSerializer::parse($size, SizeModel::class);
        }

//        dd($itemCategories);

        return $this->render('repository/item_category/edit.html.twig', [
            'controller_name' => 'ItemCategoryController',
            'itemCategoryModel' => $itemCategoryModel,
            'itemCategories' => $itemCategories,
            'specGroups' => $specGroups,
            'sizes' => $sizes,
        ]);
    }


    /**
     * @Route("/include-spec-key/{category_id}/", name="_repository_item_category_include")
     * @param Request $request
     * @param $category_id
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \ReflectionException
     */
    public function includeSpecKeys(Request $request, $category_id)
    {
        $inputs = $request->request->all();

        /**
         * @var $specKeyModel ItemCategorySpecKeyModel
         */
        $specKeyModel = ModelSerializer::parse($inputs, ItemCategorySpecKeyModel::class);

        $specKeyModel->setItemCategoryId($category_id);
        $request = new Req(Servers::Repository, Repository::ItemCategory, 'assign_keys');
        $request->add_instance($specKeyModel);
        $response = $request->send();

        if ($response->getStatus() == ResponseStatus::successful) {
            $this->addFlash('s', $response->getMessage());
        } else {
            $this->addFlash('s', $response->getMessage());
        }

        return $this->redirect($this->generateUrl('repository_item_category_repository_item_category_edit', ['id' => $category_id]));
    }


    public function generateCategory()
    {
        return '<li>  </li>';
    }
}
