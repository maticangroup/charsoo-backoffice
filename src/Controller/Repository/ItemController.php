<?php

namespace App\Controller\Repository;

use App\Convert2;
use App\Entity\Media\ImageMedia;
use App\Entity\Repository\Barcode;
use App\Entity\Repository\Brand;
use App\Entity\Repository\Company;
use App\Entity\Repository\Guarantee;
use App\Entity\Repository\Item;
use App\Entity\Repository\ItemCategory;
use App\Entity\Repository\ItemColor;
use App\Entity\Repository\ItemType;
use App\Entity\Repository\SpecKey;
use App\Entity\Repository\SpecKeyValue;
use App\Entity\Repository\SpecValue;
use App\FormModels\Media\ImageModel;
use App\FormModels\ModelSerializer;
use App\FormModels\Repository\BarcodeModel;
use App\FormModels\Repository\CompanyModel;
use App\FormModels\Repository\GuaranteeModel;
use App\FormModels\Repository\ItemCategoriesModel;
use App\FormModels\Repository\ItemCategoryModel;
use App\FormModels\Repository\ItemColorModel;
use App\FormModels\Repository\ItemModel;
use App\FormModels\Repository\ItemTypeModel;
use App\FormModels\Repository\SpecKeyModel;
use App\General;
use App\Helpers;
use App\Json;
use App\Library\Search;
use App\Library\Serialize;
use App\Library\Validation;
use App\Repository\Repository\ItemRepository;
use App\Services\Database;
use Doctrine\Common\Persistence\ObjectManager;
use Matican\Core\Servers;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;


use Matican\Core\Transaction\Request as Req;
use Symfony\Component\HttpFoundation\Response;


class ItemController extends AbstractController
{
    public function all($request, ObjectManager $entityManager): JsonResponse
    {
        /**
         * @todo Related Items and Gifts assigned to items are not written even in the client side
         */
        /**
         * @var $items Item[]
         */
        $items = Database::all(Item::class, $entityManager);
//        $items = $this->getDoctrine()->getRepository(Item::class)->findAll();
        if ($items) {
            $itemsModelArray = [];
            foreach ($items as $item) {
                $itemModel = new ItemModel();

                $itemModel->setItemName(
                    $item->getName()
                );
                $itemModel->setItemID(
                    $item->getId()
                );


                /**
                 * @todo Check here
                 */
//                $item->getLastModified();
//                $item->getCreateDate();
                if ($item->getItemType()) {
                    /**
                     * @var $itemType ItemType
                     */
                    $itemType = $item->getItemType();
                    $itemTypeModel = new ItemTypeModel();
                    $itemTypeModel->setItemTypeName($itemType->getName());
                    $itemTypeModel->setItemTypeMachineName($itemType->getMachineName());
                    $itemTypeModel->setItemTypeId($itemType->getId());
                }
                if ($item->getBrand()) {
                    $itemModel->setItemBrandId($item->getBrand()->getId());
                    $itemModel->setItemBrandName($item->getBrand()->getName());
                }
                if ($item->getAvailableGuarantees()->count() > 0) {
                    $guaranteesModelArray = [];
                    foreach ($item->getAvailableGuarantees() as $guarantee) {
                        $guaranteeModel = new GuaranteeModel();
                        $guaranteeModel->setGuaranteeID($guarantee->getId());
                        $guaranteeModel->setGuaranteeName($guarantee->getName());
                        $guaranteesModelArray[] = $guaranteeModel;
                    }
                    $itemModel->setItemGuarantees($guaranteesModelArray);
                }
                if ($item->getAvailableSuppliers()->count() > 0) {
                    $supplierModelArray = [];
                    foreach ($item->getAvailableSuppliers() as $supplier) {
                        $supplierModelArray[] = \App\Library\Repository\Company::toModelForOverview($supplier);
                    }
                    $itemModel->setItemSuppliers($supplierModelArray);
                }
                if ($item->getAvailableColors()->count() > 0) {
                    $colorModelArray = [];
                    foreach ($item->getAvailableColors() as $color) {
                        $colorModel = new ItemColorModel();
                        $colorModel->setItemColorID($color->getId());
                        $colorModel->setItemColorName($color->getName());
                        $colorModel->setItemColorHex($color->getHexCode());
                        $colorModelArray[] = $colorModel;
                    }
                    $itemModel->setItemColors($colorModelArray);
                }
                if ($item->getBarcodes()->count() > 0) {
                    $barcodeModelArray = [];
                    foreach ($item->getBarcodes() as $barcode) {
                        $barcodeModel = new BarcodeModel();
                        $barcodeModel->setBarcodeName($barcode->getSerial());
                        $barcodeModel->setBarcodeId($barcode->getId());
                        $barcodeModelArray[] = $barcodeModel;
                    }
                    $itemModel->setItemBarcodes($barcodeModelArray);
                }
                $itemModel->setItemCreatedDate($item->getCreateDate());
                $itemModel->setItemUpdatedDate($item->getLastModified());
                if ($item->getImages()->count()) {
                    $imagesArray = [];
                    foreach ($item->getImages() as $image) {
                        $imageModel = new ImageModel();
                        $imageModel->setImageAlt($image->getAlt());
                        $imageModel->setUrl(Helpers::get_image_link($image->getSerial(), 'default_size'));
                        $imageModel->setImageSerial($image->getSerial());
                        $imageModel->setImageSize('default_size');
                        $imagesArray[] = $imageModel;
                    }
                    $itemModel->setItemImages($imagesArray);
                }
                if ($item->getItemCategories()->count()) {
                    /**
                     * @var $selectedCategories
                     */
                    $selectedCategories = $item->getItemCategories();
                    $itemModel->setSelectedItemCategories(
                        \App\Library\Repository\ItemCategory::define_categories_depth($selectedCategories)
                    );
                } else {
                    $itemModel->setSelectedItemCategories([]);
                }

                $itemModel->setItemCategoriesIds([]);
                $itemsModelArray[] = $itemModel;
            }
            return $this->json(Json::response($itemsModelArray, "suc", Json::successful));
        } else {
            return $this->json(Json::response(null, "suc but empty", Json::input_parameter_is_missing));
        }
    }

    /**
     * @param $request Request
     * @param ObjectManager $entityManager
     * @return JsonResponse
     * @throws \Exception
     */
    public function duplicate($request, ObjectManager $entityManager)
    {
        if (Validation::check_request($request)) {
            return $this->json(Validation::check_request($request));
        }
        /**
         * @var $itemModel ItemModel
         */
        $itemModel = Serialize::Model($request, ItemModel::class);
        if (Validation::check($itemModel->getItemID())) {
            return $this->json(Validation::check($itemModel->getItemID(), "Item ID"));
        }
        /**
         * @var $item Item
         */
        $item = Database::fetchOneById($itemModel->getItemID(), Item::class, $entityManager);
        $newItem = new Item();
        $newItem->setName($item->getName() . " - Duplicate*");
        $newItem->setCreateDate(new \DateTime('now'));
        if ($item->getBrand()) {
            $newItem->setBrand($item->getBrand());
        }
        if ($item->getItemSize()) {
            $newItem->setItemSize($item->getItemSize());
        }
        if ($item->getItemType()) {
            $newItem->setItemType($item->getItemType());
        }


        if ($item->getImages()) {
            foreach ($item->getImages() as $image) {
                $newItem->addImage($image);
            }
        }
        if ($item->getSpecKeyValues()) {
            foreach ($item->getSpecKeyValues() as $specKeyValue) {
                $newSpecKeyValue = new SpecKeyValue();
                $newSpecKeyValue->setItem($newItem);
                $newSpecKeyValue->setSpecValue($specKeyValue->getSpecValue());
                $newSpecKeyValue->setSpecKey($specKeyValue->getSpecKey());
                $entityManager->persist($newSpecKeyValue);
                $newItem->addSpecKeyValue($newSpecKeyValue);
            }
        }
        if ($item->getAvailableColors()) {
            foreach ($item->getAvailableColors() as $availableColor) {
                $newItem->addAvailableColor($availableColor);
            }
        }
        if ($item->getAvailableGuarantees()) {
            foreach ($item->getAvailableGuarantees() as $guarantee) {
                $newItem->addAvailableGuarantee($guarantee);
            }
        }
        if ($item->getAvailableSuppliers()) {
            foreach ($item->getAvailableSuppliers() as $availableSupplier) {
                $newItem->addAvailableSupplier($availableSupplier);
            }
        }
        if ($item->getBarcodes()) {
            foreach ($item->getBarcodes() as $barcode) {
                $newItem->addBarcode($barcode);
            }
        }
        if ($item->getItemCategories()) {
            foreach ($item->getItemCategories() as $itemCategory) {
                $newItem->addItemCategory($itemCategory);
            }
        }
        if ($item->getRelatedAccessories()) {
            foreach ($item->getRelatedAccessories() as $accessory) {
                $newItem->addRelatedAccessory($accessory);
            }
        }

        Search::persist_searchable($newItem, $entityManager);
        $entityManager->flush();
        return $this->json(Json::response($itemModel, "Record been duplicated", Json::successful));

    }

    public function fetch($request): JsonResponse
    {

        return Helpers::validateRequest($request, function ($reqParam) {
            /**
             * @var $reqParam Request
             */
            /**
             * @var $itemModel ItemModel
             */
            $itemModel = ModelSerializer::parse($reqParam->query->all(), ItemModel::class);
            if ($itemModel->getItemID()) {

//                $fileSystem = new Filesystem();
//                if ($fileSystem->exists(\App\Library\Repository\Item::cached_file_path(
//                    $itemModel->getItemID()
//                ))) {
////                    $this->forward(ItemController::class . "::purge_single_cache", [
////                        'itemID' => 1
////                    ]);
//                    $content = file_get_contents(\App\Library\Repository\Item::cached_file_path(
//                        $itemModel->getItemID()
//                    ));
//                    return $this->json(Json::response($content, "successful", Json::successful));
//                }

                /**
                 * @var $item Item
                 */
                $item = $this->getDoctrine()->getRepository(Item::class)->find($itemModel->getItemID());
                if ($item) {

                    $itemModel->setItemID(
                        $item->getId()
                    );
                    $itemModel->setItemName(
                        $item->getName()
                    );
                    if ($item->getBrand()) {
                        $itemModel->setItemBrandName(
                            $item->getBrand()->getName()
                        );
                        $itemModel->setItemBrandId(
                            $item->getBrand()->getId()
                        );
                    }
                    if ($item->getItemType()) {
                        $itemModel->setItemTypeId(
                            $item->getItemType()->getId()
                        );
                        $itemModel->setItemTypeName(
                            $item->getItemType()->getName()
                        );
                        $itemModel->setItemTypeMachineName(
                            $item->getItemType()->getMachineName()
                        );
                    }

                    if ($item->getBarcodes()->count() > 0) {
                        $barcodeModelArray = [];
                        foreach ($item->getBarcodes() as $barcode) {
                            $barcodeModel = new BarcodeModel();
                            $barcodeModel->setBarcodeId($barcode->getId());
                            $barcodeModel->setBarcodeName($barcode->getSerial());
                            $barcodeModelArray[] = $barcodeModel;
                        }
                        $itemModel->setItemBarcodes($barcodeModelArray);
                    }
                    if ($item->getAvailableSuppliers()->count() > 0) {
                        $suppliers = $item->getAvailableSuppliers();
                        $supplierModelArray = [];
                        foreach ($suppliers as $supplier) {
                            $supplierModelArray[] = \App\Library\Repository\Company::toModelForOverview($supplier);
                        }
                        $itemModel->setItemSuppliers($supplierModelArray);
                    }
                    if ($item->getAvailableColors()->count() > 0) {
                        $colors = $item->getAvailableColors();
                        $colorsModelArray = [];
                        foreach ($colors as $color) {
                            $colorModel = new ItemColorModel();
                            $colorModel->setItemColorHex($color->getHexCode());
                            $colorModel->setItemColorName($color->getName());
                            $colorModel->setItemColorID($color->getId());
                            $colorsModelArray[] = $colorModel;
                        }
                        $itemModel->setItemColors($colorsModelArray);
                    }
                    if ($item->getAvailableGuarantees()->count() > 0) {
                        $guarantees = $item->getAvailableGuarantees();
                        $guaranteesModelArray = [];
                        foreach ($guarantees as $guarantee) {
                            $guaranteeModel = new GuaranteeModel();
                            $guaranteeModel->setGuaranteeName($guarantee->getName());
                            $guaranteeModel->setGuaranteeID($guarantee->getId());
                            $guaranteesModelArray[] = $guaranteeModel;
                        }
                        $itemModel->setItemGuarantees($guaranteesModelArray);
                    }
                    if ($item->getItemCategories()->count() > 0) {
                        $categories = $item->getItemCategories();
                        $categoriesArray = [];
                        $specGroupKeysStack = [];
                        foreach ($categories as $category) {
                            $categoriesArray[] = $category->getId();
                            if ($category->getSpecKeys()) {
                                $specKeys = $category->getSpecKeys();
                                foreach ($specKeys as $specKey) {
                                    $specKeyModel = new SpecKeyModel();
                                    $specKeyModel->setSpecKeyName($specKey->getName());
                                    $specKeyModel->setSpecKeyID($specKey->getId());
                                    $specKeyModel->setSpecKeyIsSpecial($specKey->getIsSpecial());
                                    $specKeyModel->setSpecKeyDefaultValue($specKey->getDefaultValue());
                                    $specKeyModel->setSpecKeySpecGroupID($specKey->getSpecGroup()->getId());
                                    $specKeyModel->setSpecKeySpecGroupName($specKey->getSpecGroup()->getName());
                                    $submittedValuesArray = [];
                                    /**
                                     * @var $itemSpecKeyValues SpecKeyValue[]
                                     */
                                    $itemSpecKeyValues = $this->getDoctrine()
                                        ->getRepository(SpecKeyValue::class)
                                        ->findBy([
                                            'item' => $item,
                                            'spec_key' => $specKey
                                        ]);
                                    if ($itemSpecKeyValues) {
                                        foreach ($itemSpecKeyValues as $itemSpecKeyValue) {
                                            $value = $itemSpecKeyValue->getSpecValue()->getValue();
                                            $submittedValuesArray[] = $value;
                                        }
                                    }

                                    $specKeyModel->setSpecKeySubmittedValues($submittedValuesArray);
                                    $suggestionsArray = [];
                                    if ($specKey->getSpecKeyValues()->count()) {
                                        foreach ($specKey->getSpecKeyValues() as $keyValue) {
                                            $suggestion = $keyValue->getSpecValue()->getValue();
                                            $suggestionsArray[] = $suggestion;
                                        }
                                    }
                                    $specKeyModel->setSpecKeySuggestion(array_unique($suggestionsArray));
                                    $specGroupKeysStack[$specKey->getSpecGroup()->getName()][$specKey->getId()] = $specKeyModel;
                                }
                                $itemModel->setItemSpecGroupsKeys($specGroupKeysStack);
                            }
                        }
                        $itemModel->setItemCategoriesIds($categoriesArray);
                    }
                    $itemModel->setItemUpdatedDate(
                        $item->getLastModified()
                    );
                    $itemModel->setItemCreatedDate(
                        $item->getCreateDate()
                    );
                    $imageModelsArray = [];
                    if ($item->getImages()->count()) {
                        foreach ($item->getImages() as $image) {
                            $imageModel = new ImageModel();
                            $imageModel->setImageSize('default_size');
                            $imageModel->setImageSerial($image->getSerial());
                            $imageModel->setImageAlt($image->getAlt());
                            $imageModel->setUrl(Helpers::get_image_link($image->getSerial(), 'default_size'));
                            $imageModelsArray[] = $imageModel;
                        }
                    }
                    $itemModel->setItemImages($imageModelsArray);

                    return $this->json(Json::response($itemModel, "Suc", Json::successful));
                } else {
                    return $this->json(Json::response($itemModel, "Could not find item", Json::input_parameter_is_missing));

                }
            } else {
                return $this->json(Json::response($itemModel, "ID is required", Json::input_parameter_is_missing));
            }
        }, function ($reqParam) {
            return $this->json(Json::response(null, "Inputs are missing", Json::input_parameter_is_missing));
        });
    }



//
//
//    public function fetch_raw($itemID): JsonResponse
//    {
//        $itemModel = new ItemModel();
//
//        /**
//         * @var $item Item
//         */
//        $item = $this->getDoctrine()->getRepository(Item::class)->find($itemID);
//
//        $itemModel->setItemID(
//            $item->getId()
//        );
//        $itemModel->setItemName(
//            $item->getName()
//        );
//
//        if ($item->getBrand()) {
//            $itemModel->setItemBrandName(
//                $item->getBrand()->getName()
//            );
//            $itemModel->setItemBrandId(
//                $item->getBrand()->getId()
//            );
//        }
//        if ($item->getItemType()) {
//            $itemModel->setItemTypeId(
//                $item->getItemType()->getId()
//            );
//            $itemModel->setItemTypeName(
//                $item->getItemType()->getName()
//            );
//            $itemModel->setItemTypeMachineName(
//                $item->getItemType()->getMachineName()
//            );
//        }
//
//        if ($item->getBarcodes()->count() > 0) {
//            $barcodeModelArray = [];
//            foreach ($item->getBarcodes() as $barcode) {
//                $barcodeModel = new BarcodeModel();
//                $barcodeModel->setBarcodeId($barcode->getId());
//                $barcodeModel->setBarcodeName($barcode->getSerial());
//                $barcodeModelArray[] = $barcodeModel;
//            }
//            $itemModel->setItemBarcodes($barcodeModelArray);
//        }
//        if ($item->getAvailableSuppliers()->count() > 0) {
//            $suppliers = $item->getAvailableSuppliers();
//            $supplierModelArray = [];
//            foreach ($suppliers as $supplier) {
//                $supplierModelArray[] = \App\Library\Repository\Company::toModelForOverview($supplier);
//            }
//            $itemModel->setItemSuppliers($supplierModelArray);
//        }
//        if ($item->getAvailableColors()->count() > 0) {
//            $colors = $item->getAvailableColors();
//            $colorsModelArray = [];
//            foreach ($colors as $color) {
//                $colorModel = new ItemColorModel();
//                $colorModel->setItemColorHex($color->getHexCode());
//                $colorModel->setItemColorName($color->getName());
//                $colorModel->setItemColorID($color->getId());
//                $colorsModelArray[] = $colorModel;
//            }
//            $itemModel->setItemColors($colorsModelArray);
//        }
//        if ($item->getAvailableGuarantees()->count() > 0) {
//            $guarantees = $item->getAvailableGuarantees();
//            $guaranteesModelArray = [];
//            foreach ($guarantees as $guarantee) {
//                $guaranteeModel = new GuaranteeModel();
//                $guaranteeModel->setGuaranteeName($guarantee->getName());
//                $guaranteeModel->setGuaranteeID($guarantee->getId());
//                $guaranteesModelArray[] = $guaranteeModel;
//            }
//            $itemModel->setItemGuarantees($guaranteesModelArray);
//        }
//        if ($item->getItemCategories()->count() > 0) {
//            $categories = $item->getItemCategories();
//            $categoriesArray = [];
//            $specGroupKeysStack = [];
//            foreach ($categories as $category) {
//                $categoriesArray[] = $category->getId();
//                if ($category->getSpecKeys()) {
//                    $specKeys = $category->getSpecKeys();
//                    foreach ($specKeys as $specKey) {
//                        $specKeyModel = new SpecKeyModel();
//                        $specKeyModel->setSpecKeyName($specKey->getName());
//                        $specKeyModel->setSpecKeyID($specKey->getId());
//                        $specKeyModel->setSpecKeyIsSpecial($specKey->getIsSpecial());
//                        $specKeyModel->setSpecKeyDefaultValue($specKey->getDefaultValue());
//                        $specKeyModel->setSpecKeySpecGroupID($specKey->getSpecGroup()->getId());
//                        $specKeyModel->setSpecKeySpecGroupName($specKey->getSpecGroup()->getName());
//                        $submittedValuesArray = [];
//                        /**
//                         * @var $itemSpecKeyValues SpecKeyValue[]
//                         */
//                        $itemSpecKeyValues = $this->getDoctrine()
//                            ->getRepository(SpecKeyValue::class)
//                            ->findBy([
//                                'item' => $item,
//                                'spec_key' => $specKey
//                            ]);
//                        if ($itemSpecKeyValues) {
//                            foreach ($itemSpecKeyValues as $itemSpecKeyValue) {
//                                $value = $itemSpecKeyValue->getSpecValue()->getValue();
//                                $submittedValuesArray[] = $value;
//                            }
//                        }
//
//                        $specKeyModel->setSpecKeySubmittedValues($submittedValuesArray);
//                        $suggestionsArray = [];
//                        if ($specKey->getSpecKeyValues()->count()) {
//                            foreach ($specKey->getSpecKeyValues() as $keyValue) {
//                                $suggestion = $keyValue->getSpecValue()->getValue();
//                                $suggestionsArray[] = $suggestion;
//                            }
//                        }
//                        $specKeyModel->setSpecKeySuggestion(array_unique($suggestionsArray));
//                        $specGroupKeysStack[$specKey->getSpecGroup()->getName()][$specKey->getId()] = $specKeyModel;
//                    }
//                    $itemModel->setItemSpecGroupsKeys($specGroupKeysStack);
//                }
//            }
//            $itemModel->setItemCategoriesIds($categoriesArray);
//        }
//
//        $itemModel->setItemUpdatedDate(
//            $item->getLastModified()
//        );
//        $itemModel->setItemCreatedDate(
//            $item->getCreateDate()
//        );
//
//        $imageModelsArray = [];
//
//        if ($item->getImages()->count()) {
//            foreach ($item->getImages() as $image) {
//                $imageModel = new ImageModel();
//                $imageModel->setImageSize('default_size');
//                $imageModel->setImageSerial($image->getSerial());
//                $imageModel->setImageAlt($image->getAlt());
//                $imageModel->setUrl(Helpers::get_image_link($image->getSerial(), 'default_size'));
//                $imageModelsArray[] = $imageModel;
//            }
//        }
//
//        $itemModel->setItemImages($imageModelsArray);
//
//        return $this->json($itemModel);
//
//    }
//
//    public function purge_single_cache($itemID): bool
//    {
//        $fileSystem = new Filesystem();
//        if (!$fileSystem->exists(General::ITEMS_CACHE_DIRECTORY)) {
//            $fileSystem->mkdir(General::ITEMS_CACHE_DIRECTORY);
//        }
//        if (!$fileSystem->exists(General::ITEMS_CACHE_DIRECTORY . $itemID . '.txt')) {
//            $fileSystem->touch(General::ITEMS_CACHE_DIRECTORY . $itemID . '.txt');
//        } else {
//            $fileSystem->remove(General::ITEMS_CACHE_DIRECTORY . $itemID . '.txt');
//            $fileSystem->touch(General::ITEMS_CACHE_DIRECTORY . $itemID . '.txt');
//        }
//        /**
//         * @var $raw_response Response
//         */
//        $raw_response = $this->forward(ItemController::class . "::fetch_raw", [
//            'itemID' => $itemID
//        ]);
//
//        $itemEncoded = $raw_response->getContent();
//        $fileSystem->appendToFile(General::ITEMS_CACHE_DIRECTORY . $itemID . '.txt', $itemEncoded);
//        return true;
//    }


    public function update($request): JsonResponse
    {
        return Helpers::validateRequest($request, function ($reqParam) {
            /**
             * @var $reqParam Request
             */
            /**
             * @var $itemModel ItemModel
             */
            $itemModel = ModelSerializer::parse($reqParam->query->all(), ItemModel::class);
            if ($itemModel->getItemID()) {
                /**
                 * @var $item Item
                 */
                $item = $this->getDoctrine()->getRepository(Item::class)->find($itemModel->getItemID());
                if ($item) {
                    if ($itemModel->getItemBrandId()) {
                        /**
                         * @var $brand Brand
                         */
                        $brand = $this->getDoctrine()->getRepository(Brand::class)->find($itemModel->getItemBrandId());
                        if ($brand) {
                            $item->setBrand($brand);
                        } else {
                            return $this->json(Json::response($itemModel, "Could not find brand with provided ID", Json::input_parameter_is_missing));
                        }
                    }
                    $item->setLastModified(new \DateTime('now'));
                    $item->setName($itemModel->getItemName());
                    $item->setSearchableContent();
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($item);
                    $em->flush();

                    return $this->json(Json::response($itemModel, "Suc updated", Json::successful));
                } else {
                    return $this->json(Json::response($itemModel, "Could not fid Item", Json::input_parameter_is_missing));
                }
            } else {
                return $this->json(Json::response($itemModel, "ID is required", Json::input_parameter_is_missing));
            }
        }, function ($reqParam) {
            return $this->json(Json::response(null, "Inputs are missing", Json::input_parameter_is_missing));
        });
    }

    public function new($request): JsonResponse
    {

        return Helpers::validateRequest($request, function ($reqParam) {
            /**
             * @var $reqParam Request
             */
            /**
             * @var $itemModel ItemModel
             */

            $itemModel = ModelSerializer::parse($reqParam->query->all(), ItemModel::class);


            if ($itemModel->getItemName()) {

                $item = new Item();
                $item->setName($itemModel->getItemName());
                $item->setSearchableContent();
                $item->setCreateDate(new \DateTime('now'));
                $item->setLastModified(new \DateTime('now'));
                if ($itemModel->getItemTypeId()) {

                    $itemTypeModelID = $itemModel->getItemTypeId();
                    /**
                     * @var $itemType ItemType
                     */
                    $itemType = $this->getDoctrine()
                        ->getRepository(ItemType::class)
                        ->find($itemTypeModelID);
                    if ($itemType) {
                        $item->setItemType($itemType);
                    } else {
                        return $this->json(Json::response($itemModel, "Could not find item type", Json::input_parameter_is_missing));
                    }
                    if ($itemModel->getItemBrandId()) {

                        $itemBrandModelID = $itemModel->getItemBrandId();
                        /**
                         * @var $brand Brand
                         */
                        $brand = $this->getDoctrine()
                            ->getRepository(Brand::class)
                            ->find($itemBrandModelID);
                        /**
                         * @todo Validation can be more tight here
                         */
                        if ($brand) {
                            $item->setBrand($brand);
                        } else {
                            return $this->json(Json::response($itemModel, "Could not find brand", Json::input_parameter_is_missing));
                        }
                    }
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($item);
                    $em->flush();
                    $itemModel->setItemID($item->getId());
                    return $this->json(Json::response($itemModel, "Item is added", Json::successful));
                } else {
                    return $this->json(Json::response($itemModel, "Item Type is required", Json::input_parameter_is_missing));
                }

            } else {
                return $this->json(Json::response($itemModel, "Name is required.", Json::input_parameter_is_missing));

            }
        }, function ($reqParam) {
            return $this->json(Json::response(null, "Inputs are missing", Json::input_parameter_is_missing));
        });
    }

    public function get_types(): JsonResponse
    {
        /**
         * @var $itemTypes ItemType[]
         */
        $itemTypes = $this->getDoctrine()->getRepository(ItemType::class)->findAll();
        $itemTypeModels = [];
        if ($itemTypes) {
            foreach ($itemTypes as $itemType) {
                $itemTypeModel = new ItemTypeModel();
                $itemTypeModel->setItemTypeId($itemType->getId());
                $itemTypeModel->setItemTypeMachineName($itemType->getMachineName());
                $itemTypeModel->setItemTypeName($itemType->getName());
                $itemTypeModels[] = $itemTypeModel;
            }
            return $this->json(Json::response($itemTypeModels, "suc", Json::successful));
        } else {
            $em = $this->getDoctrine()->getManager();
            $uniqueItemType = new ItemType();
            $uniqueItemTypeName = "Unique";
            $uniqueItemType->setName($uniqueItemTypeName);
            $uniqueItemType->setMachineName(Convert2::machine_name($uniqueItemTypeName));
            $em->persist($uniqueItemType);
            $quantityType = new ItemType();
            $quantityTypeName = "Quantity";
            $quantityType->setName($quantityTypeName);
            $quantityType->setMachineName(Convert2::machine_name($quantityTypeName));
            $em->persist($quantityType);
            $em->flush();
            return $this->get_types();
        }
    }

    public function add_barcode($request): JsonResponse
    {
        return Helpers::validateRequest($request, function ($reqParam) {
            /**
             * @var $reqParam Request
             */
            /**
             * @var $barcodeModel BarcodeModel
             */
            $barcodeModel = ModelSerializer::parse($reqParam->query->all(), BarcodeModel::class);
            /**
             * @todo IMPORTANT: barcode duplication should be checked
             */
            if ($barcodeModel->getItemId()) {
                /**
                 * @var $item Item
                 */
                $item = $this->getDoctrine()->getRepository(Item::class)->find($barcodeModel->getItemId());
                if ($item) {
                    $barcode = new Barcode();
                    $barcode->setSerial($barcodeModel->getBarcodeName());
                    $barcode->setItem($item);
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($barcode);
                    $item->addBarcode($barcode);
                    $em->persist($item);
                    $em->flush();

                    return $this->json(Json::response($barcodeModel, "Suc", Json::successful));
                } else {
                    return $this->json(Json::response($barcodeModel, "Could not find Item", Json::input_parameter_is_missing));
                }
            } else {
                return $this->json(Json::response($barcodeModel, "Item ID is missing", Json::input_parameter_is_missing));
            }
        }, function ($reqParam) {
            return $this->json(Json::response(null, "Inputs are missing", Json::input_parameter_is_missing));
        });
    }

    public function remove_barcode($request): JsonResponse
    {

        return Helpers::validateRequest($request, function ($reqParam) {
            /**
             * @var $reqParam Request
             */
            /**
             * @var $barcodeModel BarcodeModel
             */
            $barcodeModel = ModelSerializer::parse($reqParam->query->all(), BarcodeModel::class);
            /**
             * @todo IMPORTANT: barcode duplication should be checked
             */
            if ($barcodeModel->getItemId()) {
                /**
                 * @var $item Item
                 */
                $item = $this->getDoctrine()->getRepository(Item::class)->find($barcodeModel->getItemId());
                if ($item) {
                    if ($barcodeModel->getBarcodeId()) {
                        /**
                         * @var $barcode Barcode
                         */
                        $barcode = $this->getDoctrine()->getRepository(Barcode::class)->find($barcodeModel->getBarcodeId());
                        $em = $this->getDoctrine()->getManager();
                        $item->removeBarcode($barcode);
                        $em->persist($item);
                        $em->remove($barcode);
                        $em->persist($barcode);
                        $em->flush();

                        return $this->json(Json::response($barcodeModel, "Suc remove", Json::successful));
                    } else {
                        return $this->json(Json::response($barcodeModel, "Barcode ID is missing", Json::input_parameter_is_missing));
                    }
                } else {
                    return $this->json(Json::response($barcodeModel, "Could not find Item", Json::input_parameter_is_missing));
                }
            } else {
                return $this->json(Json::response($barcodeModel, "Item ID is missing", Json::input_parameter_is_missing));
            }

        }, function ($reqParam) {
            return $this->json(Json::response(null, "Inputs are missing", Json::input_parameter_is_missing));
        });
    }

    public function add_available_guarantee($request): JsonResponse
    {
        return Helpers::validateRequest($request, function ($reqParam) {
            /**
             * @var $reqParam Request
             */
            /**
             * @var $guaranteeModel GuaranteeModel
             */
            $guaranteeModel = ModelSerializer::parse($reqParam->query->all(), GuaranteeModel::class);

            if ($guaranteeModel->getItemId()) {
                /**
                 * @var $item Item
                 */
                $item = $this->getDoctrine()->getRepository(Item::class)->find($guaranteeModel->getItemId());
                if ($item) {
                    if ($guaranteeModel->getGuaranteeID()) {
                        /**
                         * @var $guarantee Guarantee
                         */
                        $guarantee = $this->getDoctrine()->getRepository(Guarantee::class)->find($guaranteeModel->getGuaranteeID());
                        if ($guarantee) {
                            $item->addAvailableGuarantee($guarantee);
                            $em = $this->getDoctrine()->getManager();
                            $em->persist($item);
                            $em->flush();
                            return $this->json(Json::response($guaranteeModel, "Suc", Json::successful));

                        } else {
                            return $this->json(Json::response($guaranteeModel, "Could not find guarantee", Json::input_parameter_is_missing));
                        }
                    } else {
                        return $this->json(Json::response($guaranteeModel, "Guarantee ID is missing", Json::input_parameter_is_missing));
                    }
                } else {
                    return $this->json(Json::response($guaranteeModel, "Could not find Item", Json::input_parameter_is_missing));
                }
            } else {
                return $this->json(Json::response($guaranteeModel, "Item ID is missing", Json::input_parameter_is_missing));
            }
        }, function ($reqParam) {
            return $this->json(Json::response(null, "Inputs are missing", Json::input_parameter_is_missing));
        });
    }

    public function remove_available_guarantee($request): JsonResponse
    {
        return Helpers::validateRequest($request, function ($reqParam) {
            /**
             * @var $reqParam Request
             */
            /**
             * @var $guaranteeModel GuaranteeModel
             */
            $guaranteeModel = ModelSerializer::parse($reqParam->query->all(), GuaranteeModel::class);

            if ($guaranteeModel->getItemId()) {
                /**
                 * @var $item Item
                 */
                $item = $this->getDoctrine()->getRepository(Item::class)->find($guaranteeModel->getItemId());
                if ($item) {
                    if ($guaranteeModel->getGuaranteeID()) {
                        /**
                         * @var $guarantee Guarantee
                         */
                        $guarantee = $this->getDoctrine()->getRepository(Guarantee::class)->find($guaranteeModel->getGuaranteeID());
                        if ($guarantee) {
                            $item->removeAvailableGuarantee($guarantee);
                            $em = $this->getDoctrine()->getManager();
                            $em->persist($item);
                            $em->flush();
                            return $this->json(Json::response($guaranteeModel, "Suc", Json::successful));

                        } else {
                            return $this->json(Json::response($guaranteeModel, "Could not find guarantee", Json::input_parameter_is_missing));
                        }
                    } else {
                        return $this->json(Json::response($guaranteeModel, "Guarantee ID is missing", Json::input_parameter_is_missing));
                    }
                } else {
                    return $this->json(Json::response($guaranteeModel, "Could not find Item", Json::input_parameter_is_missing));
                }
            } else {
                return $this->json(Json::response($guaranteeModel, "Item ID is missing", Json::input_parameter_is_missing));
            }
        }, function ($reqParam) {
            return $this->json(Json::response(null, "Inputs are missing", Json::input_parameter_is_missing));
        });
    }

    public function add_available_supplier($request): JsonResponse
    {
        return Helpers::validateRequest($request, function ($reqParam) {
            /**
             * @var $reqParam Request
             */
            /**
             * @var $supplierModel CompanyModel
             */
            $supplierModel = ModelSerializer::parse($reqParam->query->all(), CompanyModel::class);

            if ($supplierModel->getItemId()) {
                /**
                 * @var $item Item
                 */
                $item = $this->getDoctrine()->getRepository(Item::class)->find($supplierModel->getItemId());
                if ($item) {
                    if ($supplierModel->getCompanyID()) {
                        /**
                         * @var $supplier Company
                         */
                        $supplier = $this->getDoctrine()->getRepository(Company::class)->find($supplierModel->getCompanyID());
                        if ($supplier) {
                            $item->addAvailableSupplier($supplier);
                            $em = $this->getDoctrine()->getManager();
                            $em->persist($item);
                            $em->flush();
                            return $this->json(Json::response($supplierModel, "Suc", Json::successful));

                        } else {
                            return $this->json(Json::response($supplierModel, "Could not find company", Json::input_parameter_is_missing));
                        }
                    } else {
                        return $this->json(Json::response($supplierModel, "company ID is missing", Json::input_parameter_is_missing));
                    }
                } else {
                    return $this->json(Json::response($supplierModel, "Could not find Item", Json::input_parameter_is_missing));
                }
            } else {
                return $this->json(Json::response($supplierModel, "Item ID is missing", Json::input_parameter_is_missing));
            }
        }, function ($reqParam) {
            return $this->json(Json::response(null, "Inputs are missing", Json::input_parameter_is_missing));
        });

    }

    public function remove_available_supplier($request): JsonResponse
    {
        return Helpers::validateRequest($request, function ($reqParam) {
            /**
             * @var $reqParam Request
             */
            /**
             * @var $supplierModel CompanyModel
             */
            $supplierModel = ModelSerializer::parse($reqParam->query->all(), CompanyModel::class);

            if ($supplierModel->getItemId()) {
                /**
                 * @var $item Item
                 */
                $item = $this->getDoctrine()->getRepository(Item::class)->find($supplierModel->getItemId());
                if ($item) {
                    if ($supplierModel->getCompanyID()) {
                        /**
                         * @var $supplier Company
                         */
                        $supplier = $this->getDoctrine()->getRepository(Company::class)->find($supplierModel->getCompanyID());
                        if ($supplier) {
                            $item->removeAvailableSupplier($supplier);
                            $em = $this->getDoctrine()->getManager();
                            $em->persist($item);
                            $em->flush();
                            return $this->json(Json::response($supplierModel, "Suc", Json::successful));

                        } else {
                            return $this->json(Json::response($supplierModel, "Could not find company", Json::input_parameter_is_missing));
                        }
                    } else {
                        return $this->json(Json::response($supplierModel, "company ID is missing", Json::input_parameter_is_missing));
                    }
                } else {
                    return $this->json(Json::response($supplierModel, "Could not find Item", Json::input_parameter_is_missing));
                }
            } else {
                return $this->json(Json::response($supplierModel, "Item ID is missing", Json::input_parameter_is_missing));
            }
        }, function ($reqParam) {
            return $this->json(Json::response(null, "Inputs are missing", Json::input_parameter_is_missing));
        });
    }

    public function add_available_color($request): JsonResponse
    {

        return Helpers::validateRequest($request, function ($reqParam) {
            /**
             * @var $reqParam Request
             */
            /**
             * @var $colorModel ItemColorModel
             */
            $colorModel = ModelSerializer::parse($reqParam->query->all(), ItemColorModel::class);


            if ($colorModel->getItemId()) {
                /**
                 * @var $item Item
                 */
                $item = $this->getDoctrine()->getRepository(Item::class)->find($colorModel->getItemId());
                if ($item) {
                    if ($colorModel->getItemColorID()) {
                        /**
                         * @var $color ItemColor
                         */
                        $color = $this->getDoctrine()->getRepository(ItemColor::class)->find($colorModel->getItemColorID());
                        if ($color) {
                            $item->addAvailableColor($color);
                            $em = $this->getDoctrine()->getManager();
                            $em->persist($item);
                            $em->flush();
                            return $this->json(Json::response($colorModel, "Suc", Json::successful));

                        } else {
                            return $this->json(Json::response($colorModel, "Could not find", Json::input_parameter_is_missing));
                        }
                    } else {
                        return $this->json(Json::response($colorModel, "ID is missing", Json::input_parameter_is_missing));
                    }
                } else {
                    return $this->json(Json::response($colorModel, "Could not find Item", Json::input_parameter_is_missing));
                }
            } else {
                return $this->json(Json::response($colorModel, "Item ID is missing", Json::input_parameter_is_missing));
            }
        }, function ($reqParam) {
            return $this->json(Json::response(null, "Inputs are missing", Json::input_parameter_is_missing));
        });
    }

    public function remove_available_color($request): JsonResponse
    {
        return Helpers::validateRequest($request, function ($reqParam) {
            /**
             * @var $reqParam Request
             */
            /**
             * @var $colorModel ItemColorModel
             */
            $colorModel = ModelSerializer::parse($reqParam->query->all(), ItemColorModel::class);

            if ($colorModel->getItemId()) {
                /**
                 * @var $item Item
                 */
                $item = $this->getDoctrine()->getRepository(Item::class)->find($colorModel->getItemId());
                if ($item) {
                    if ($colorModel->getItemColorID()) {
                        /**
                         * @var $color ItemColor
                         */
                        $color = $this->getDoctrine()->getRepository(ItemColor::class)->find($colorModel->getItemColorID());
                        if ($color) {
                            $item->removeAvailableColor($color);
                            $em = $this->getDoctrine()->getManager();
                            $em->persist($item);
                            $em->flush();
                            return $this->json(Json::response($colorModel, "Suc", Json::successful));

                        } else {
                            return $this->json(Json::response($colorModel, "Could not find", Json::input_parameter_is_missing));
                        }
                    } else {
                        return $this->json(Json::response($colorModel, "ID is missing", Json::input_parameter_is_missing));
                    }
                } else {
                    return $this->json(Json::response($colorModel, "Could not find Item", Json::input_parameter_is_missing));
                }
            } else {
                return $this->json(Json::response($colorModel, "Item ID is missing", Json::input_parameter_is_missing));
            }
        }, function ($reqParam) {
            return $this->json(Json::response(null, "Inputs are missing", Json::input_parameter_is_missing));
        });
    }

    public function update_category($request): JsonResponse
    {
        return Helpers::validateRequest($request, function ($reqParam) {
            /**
             * @var $reqParam Request
             */
            /**
             * @var $itemCategoriesModel ItemCategoriesModel
             */
            $itemCategoriesModel = ModelSerializer::parse($reqParam->query->all(), ItemCategoriesModel::class);

            if ($itemCategoriesModel->getItemId()) {
                /**
                 * @var $item Item | null
                 */
                $item = $this->getDoctrine()->getRepository(Item::class)->find($itemCategoriesModel->getItemId());
                if ($item) {
                    $em = $this->getDoctrine()->getManager();
                    if ($item->getItemCategories()->count() > 0) {
                        foreach ($item->getItemCategories() as $category) {
                            $item->removeItemCategory($category);
                        }
                        $em->persist($item);
                        $em->flush();
                    }

                    $itemCategoriesIdsArray = json_decode($itemCategoriesModel->getItemCategoriesIds());
                    if ($itemCategoriesIdsArray) {
                        $categoryManager = $this->getDoctrine()->getRepository(ItemCategory::class);
                        foreach ($itemCategoriesIdsArray as $categoryId) {
                            /**
                             * @var $category ItemCategory
                             */
                            $category = $categoryManager->find($categoryId);
                            if ($category) {
                                if ($item->getItemSize()) {
                                    if ($item->getItemSize()->getSizeOrder() < $category->getSize()->getSizeOrder()) {
                                        $item->setItemSize($category->getSize());
                                    }
                                } else {
                                    $item->setItemSize($category->getSize());
                                }
                                $item->addItemCategory($category);
                            }
                        }
                        $em->persist($item);
                        $em->flush();
                    }
                    return $this->json(Json::response($itemCategoriesModel, "Updated successfully", Json::successful));

                } else {
                    return $this->json(Json::response(null, "Could not find Item", Json::input_parameter_is_missing));

                }
            } else {
                return $this->json(Json::response(null, "Item ID is missing", Json::input_parameter_is_missing));
            }
        }, function ($reqParam) {
            return $this->json(Json::response(null, "Inputs are missing", Json::input_parameter_is_missing));
        });
    }

    public function add_image($request): JsonResponse
    {
        return Helpers::validateRequest($request, function ($reqParam) {
            /**
             * @var $reqParam Request
             */
            /**
             * @var $itemModel ItemModel
             */
            $itemModel = ModelSerializer::parse(Json::getArray($reqParam->request->get('instance')), ItemModel::class);
            if (Validation::check($itemModel->getItemID())) {
                return $this->json(Validation::check($itemModel->getItemID(), "Item id"));
            }
            $response = $this->forward('App\Controller\Media\ImageController::upload', [
                'request' => $reqParam,
                'instance' => $itemModel
            ]);
            $response = json_decode($response->getContent(), true);
            if ($response['status'] == Json::successful) {
                return $this->json(Json::response($itemModel, "Successful", Json::successful));
            } else {
                return $this->json(Json::response($itemModel, "Failed to upload image", Json::input_parameter_is_missing));

            }


        }, function ($reqParam) {
            return $this->json(Json::response(null, "Inputs are missing", Json::input_parameter_is_missing));
        });
    }

    /**
     * @param $request
     * @return JsonResponse
     */
    public function remove_image($request): JsonResponse
    {
        return Helpers::validateRequest($request, function ($reqParam) {
            /**
             * @var $reqParam Request
             */
            /**
             * @var $imageModel ImageModel
             */
            $imageModel = Serialize::Model($reqParam, ImageModel::class);
            if (Validation::check($imageModel->getImageSerial())) {
                return $this->json(Validation::check($imageModel->getImageSerial(), 'Image serial'));
            }
            if (Validation::check($imageModel->getItemID())) {
                return $this->json(Validation::check($imageModel->getItemID(), 'Item ID'));
            }
            $manager = $this->getDoctrine()->getManager();
            /**
             * @var $imageMedia ImageMedia
             */
            $imageMedia = $manager->getRepository(ImageMedia::class)->findOneBy([
                'serial' => $imageModel->getImageSerial()
            ]);
            if (Validation::check($imageMedia)) {
                return $this->json(Validation::check($imageMedia, 'Image media'));
            }
            /**
             * @var $item Item
             */
            $item = $manager->getRepository(Item::class)->find($imageModel->getItemID());
            if (Validation::check($item)) {
                return $this->json(Validation::check($item, 'Item'));
            }
            $item->removeImage($imageMedia);
            $manager->persist($item);
            $manager->flush();
            return $this->json(Json::response(null, "Suc", Json::successful));
        }, function ($reqParam) {
            return $this->json(Json::response(null, "Inputs are missing", Json::input_parameter_is_missing));
        });
    }


}

