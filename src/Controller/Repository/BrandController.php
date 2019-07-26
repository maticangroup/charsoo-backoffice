<?php

namespace App\Controller\Repository;

use App\FormModels\ModelSerializer;
use App\FormModels\Repository\BrandModel;
use App\FormModels\Repository\BrandSupplierModel;
use App\FormModels\Repository\CompanyModel;
use App\General\AuthUser;
use Matican\Core\Entities\Repository;
use Matican\Core\Servers;
use Matican\Core\Transaction\ResponseStatus;
use Matican\Models\Media\Image;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Matican\Core\Transaction\Request as Req;

/**
 * @Route("/repository/brand", name="repository_brand")
 */
class BrandController extends AbstractController
{
    /**
     * @Route("/create", name="_repository_brand_create")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \ReflectionException
     */
    public function create(Request $request)
    {
        $canCreate = AuthUser::if_is_allowed('repository_brand_new');
        $canSeeAll = AuthUser::if_is_allowed('repository_brand_all');
        $canEdit = AuthUser::if_is_allowed('repository_brand_fetch');


        $inputs = $request->request->all();
        /**
         * @var $brandModel BrandModel
         */
        $brandModel = ModelSerializer::parse($inputs, BrandModel::class);

        if ($canCreate) {
            if (!empty($inputs)) {

                $createRequest = new Req(Servers::Repository, Repository::Brand, 'new');
                $createRequest->add_instance($brandModel);
                $response = $createRequest->send();

                if ($response->getStatus() == ResponseStatus::successful) {

                    /**
                     * @var $newBrand BrandModel
                     */
                    $newBrand = ModelSerializer::parse($response->getContent(), BrandModel::class);

                    $file = $request->files->get('brand_image');
                    $image = new Image();
                    $image->setName($file->getClientOriginalName());
                    $image->setContent($file->getPathname());
                    $image->setFileName($file->getPathname());
                    $image->setMimeType($file->getMimeType());

                    $coreRequest = new Req(Servers::Media, "Image", "upload");
                    $imageUploadResponse = $coreRequest->uploadImage($image, ['brand_id' => $newBrand->getBrandID()]);
                    dd($imageUploadResponse);
                    if (!$imageUploadResponse) {
                        $this->addFlash('s', $imageUploadResponse->getMessage());
                        return $this->redirect($this->generateUrl('repository_brand_repository_brand_create'));
                    }
                    $this->addFlash('s', $response->getMessage());
                    if ($canEdit) {
                        return $this->redirect($this->generateUrl('repository_brand_repository_brand_edit', ['id' => $newBrand->getBrandID()]));
                    } else {
                        return $this->redirect($this->generateUrl('repository_brand_repository_brand_create'));
                    }
                } else {
                    $this->addFlash('f', $response->getMessage());
                }
            }
        }


        /**
         * @var $results BrandModel[]
         */
        $results = [];
        if ($canSeeAll) {
            $allBrandsRequest = new Req(Servers::Repository, Repository::Brand, 'all');
            $allBrandsResponse = $allBrandsRequest->send();
            if ($allBrandsResponse->getContent()) {
                foreach ($allBrandsResponse->getContent() as $brand) {
                    $results[] = ModelSerializer::parse($brand, BrandModel::class);
                }
            }
        }


        return $this->render('repository/brand/create.html.twig', [
            'controller_name' => 'BrandController',
            'brandModel' => $brandModel,
            'brands' => $results,
            'canCreate' => $canCreate,
            'canSeeAll' => $canSeeAll,
            'canEdit' => $canEdit,
        ]);
    }


    /**
     * @Route("/edit/{id}", name="_repository_brand_edit")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \ReflectionException
     */
    public function edit($id, Request $request)
    {
        $canUpdate = AuthUser::if_is_allowed('repository_brand_update');
        $canAddSupplier = AuthUser::if_is_allowed('repository_brand_add_supplier');
        $canRemoveSupplier = AuthUser::if_is_allowed('repository_brand_remove_supplier');

        $inputs = $request->request->all();
        /**
         * @var $brandModel BrandModel
         */
        $brandModel = ModelSerializer::parse($inputs, BrandModel::class);
        $brandModel->setBrandID($id);
        $request = new Req(Servers::Repository, Repository::Brand, 'fetch');
        $request->add_instance($brandModel);
        $response = $request->send();
        $brandModel = ModelSerializer::parse($response->getContent(), BrandModel::class);

        if ($canUpdate) {
            if (!empty($inputs)) {
                $brandModel = ModelSerializer::parse($inputs, BrandModel::class);
                $brandModel->setBrandID($id);
                $request = new Req(Servers::Repository, Repository::Brand, 'update');
                $request->add_instance($brandModel);
                $response = $request->send();
                if ($response->getStatus() == ResponseStatus::successful) {
                    $this->addFlash('s', $response->getMessage());
                } else {
                    $this->addFlash('f', $response->getMessage());
                }
            }
        }


        /**
         * @var $suppliers CompanyModel[]
         */
        $suppliers = [];

        /**
         * @var $companies CompanyModel[]
         */
        $companies = [];
        if ($canAddSupplier) {
            $request = new Req(Servers::Repository, Repository::Company, 'all');
            $response = $request->send();
            if ($response->getContent()) {
                foreach ($response->getContent() as $company) {
                    $companies[] = ModelSerializer::parse($company, CompanyModel::class);
                }
            }

            if ($brandModel->getBrandSuppliers()) {
                foreach ($brandModel->getBrandSuppliers() as $supplier) {
                    $suppliers[] = ModelSerializer::parse($supplier, CompanyModel::class);
                }
            }
        }


        return $this->render('repository/brand/edit.html.twig', [
            'controller_name' => 'BrandController',
            'brand' => $brandModel,
            'allCompanies' => $companies,
            'suppliers' => $suppliers,
            'canUpdate' => $canUpdate,
            'canAddSupplier' => $canAddSupplier,
            'canRemoveSupplier' => $canRemoveSupplier,
        ]);
    }

    /**
     * @Route("/add-supplier/{brand_id}", name="_repository_brand_add_supplier")
     * @param $brand_id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ReflectionException
     */
    public function addSupplier($brand_id, Request $request)
    {
        $inputs = $request->request->all();
        /**
         * @var $supplierModel BrandSupplierModel
         */
        $supplierModel = ModelSerializer::parse($inputs, BrandSupplierModel::class);
        $supplierModel->setBrandId($brand_id);
        $request = new Req(Servers::Repository, Repository::Brand, 'add_supplier');
        $request->add_instance($supplierModel);
        $response = $request->send();

        if ($response->getStatus() == ResponseStatus::successful) {
            $this->addFlash('s', $response->getMessage());
        } else {
            $this->addFlash('f', $response->getMessage());
        }
        return $this->redirect($this->generateUrl('repository_brand_repository_brand_edit', ['id' => $brand_id]));
    }

    /**
     * @Route("/remove-supplier/{brand_id}/{supplier_id}", name="_repository_brand_remove_supplier")
     * @param $brand_id
     * @param $supplier_id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ReflectionException
     */
    public function removeSupplier($brand_id, $supplier_id)
    {
        $supplierModel = new BrandSupplierModel();
        $supplierModel->setBrandId($brand_id);
        $supplierModel->setCompanyId($supplier_id);
        $request = new Req(Servers::Repository, Repository::Brand, 'remove_supplier');
        $request->add_instance($supplierModel);
        $response = $request->send();
        if ($response->getStatus() == ResponseStatus::successful) {
            $this->addFlash('s', $response->getMessage());
        } else {
            $this->addFlash('f', $response->getMessage());
        }
        return $this->redirect($this->generateUrl('repository_brand_repository_brand_edit', ['id' => $brand_id]));
    }

}
