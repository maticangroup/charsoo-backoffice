<?php

namespace App\Controller\Repository;

use App\FormModels\ModelSerializer;
use App\FormModels\Repository\BrandModel;
use App\FormModels\Repository\BrandSupplierModel;
use App\FormModels\Repository\CompanyModel;
use Matican\Core\Entities\Repository;
use Matican\Core\Servers;
use Matican\Core\Transaction\ResponseStatus;
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
        $inputs = $request->request->all();
        /**
         * @var $brandModel BrandModel
         */
        $brandModel = ModelSerializer::parse($inputs, BrandModel::class);

        if (!empty($inputs)) {
            $request = new Req(Servers::Repository, Repository::Brand, 'new');
            $request->add_instance($brandModel);
            $response = $request->send();

            if ($response->getStatus() == ResponseStatus::successful) {
                /**
                 * @var $newBrand BrandModel
                 */
                $newBrand = ModelSerializer::parse($response->getContent(), BrandModel::class);
                $this->addFlash('success', $response->getMessage());
                return $this->redirect($this->generateUrl('repository_brand_repository_brand_edit', ['id' => $newBrand->getBrandID()]));
            }
            $this->addFlash('failed', $response->getMessage());
        }

        $allBrandsRequest = new Req(Servers::Repository, Repository::Brand, 'all');
        $allBrandsResponse = $allBrandsRequest->send();
        $brands = $allBrandsResponse->getContent();

        /**
         * @var $results BrandModel[]
         */
        $results = [];
        foreach ($brands as $brand) {
            $results[] = ModelSerializer::parse($brand, BrandModel::class);
        }


        return $this->render('repository/brand/create.html.twig', [
            'controller_name' => 'BrandController',
            'brandModel' => $brandModel,
            'brands' => $results,
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
        /**
         * @var $suppliers CompanyModel[]
         */
        $suppliers = [];
        if ($brandModel->getBrandSuppliers()) {
            foreach ($brandModel->getBrandSuppliers() as $supplier) {
                $suppliers[] = ModelSerializer::parse($supplier, CompanyModel::class);
            }
        }

        if (!empty($inputs)) {
            $brandModel = ModelSerializer::parse($inputs, BrandModel::class);
            $brandModel->setBrandID($id);
            $request = new Req(Servers::Repository, Repository::Brand, 'update');
            $request->add_instance($brandModel);
            $response = $request->send();
            if ($response->getStatus() == ResponseStatus::successful) {
                $this->addFlash('s', '');
            } else {
                $this->addFlash('f', '');
            }
        }


        $request = new Req(Servers::Repository, Repository::Company, 'all');
        $response = $request->send();
        /**
         * @var $companies CompanyModel[]
         */
        $companies = [];
        foreach ($response->getContent() as $company) {
            $companies[] = ModelSerializer::parse($company, CompanyModel::class);
        }


        return $this->render('repository/brand/edit.html.twig', [
            'controller_name' => 'BrandController',
            'brand' => $brandModel,
            'allCompanies' => $companies,
            'suppliers' => $suppliers,
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
