<?php

namespace App\Controller\Repository;

use App\FormModels\ModelSerializer;
use App\FormModels\Repository\CompanyAddEmployeeModel;
use App\FormModels\Repository\CompanyModel;
use App\FormModels\Repository\PersonModel;
use App\FormModels\Repository\PhoneModel;
use Matican\Actions\Repository\PersonActions;
use Matican\Core\Entities\Repository;
use Matican\Core\Servers;
use Matican\Core\Transaction\ResponseStatus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Matican\Core\Transaction\Request as Req;

/**
 * @Route("/repository/company", name="repository_company")
 */
class CompanyController extends AbstractController
{
    /**
     * @Route("/list", name="_repository_company_list")
     */
    public function fetchAll()
    {
        $request = new Req(Servers::Repository, Repository::Company, 'all');
        $response = $request->send();
        $companies = $response->getContent();

        /**
         * @var $results CompanyModel[]
         */
        $results = [];
        foreach ($companies as $company) {
            $results[] = ModelSerializer::parse($company, CompanyModel::class);
        }
        return $this->render('repository/company/list.html.twig', [
            'controller_name' => 'CompanyController',
            'companies' => $results
        ]);
    }

    /**
     * @Route("/create", name="_repository_company_create")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \ReflectionException
     */
    public function create(Request $request)
    {
        $inputs = $request->request->all();
        /**
         * @var $company CompanyModel
         */
        $company = ModelSerializer::parse($inputs, CompanyModel::class);
        if (!empty($inputs)) {
            $request = new Req(Servers::Repository, Repository::Company, 'new');
            $request->add_instance($company);
            $response = $request->send();
            if ($response->getStatus() == ResponseStatus::successful) {
                /**
                 * @var $newCompany CompanyModel
                 */
                $newCompany = ModelSerializer::parse($response->getContent(), CompanyModel::class);
                $this->addFlash('success', $response->getMessage());
                return $this->redirect($this->generateUrl('repository_company_repository_company_edit', ['id' => $newCompany->getCompanyID()]));
            }
            $this->addFlash('failed', $response->getMessage());
        }
        return $this->render('repository/company/create.html.twig', [
            'controller_name' => 'CompanyController',
            'company' => $company
        ]);
    }

    /**
     * @Route("/edit/{id}", name="_repository_company_edit")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \ReflectionException
     */
    public function edit($id, Request $request)
    {
        $inputs = $request->request->all();
        /**
         * @var $companyModel CompanyModel
         */
        $companyModel = ModelSerializer::parse($inputs, CompanyModel::class);
        $companyModel->setCompanyID($id);
        $request = new Req(Servers::Repository, Repository::Company, 'fetch');
        $request->add_instance($companyModel);
        $response = $request->send();
        $companyModel = ModelSerializer::parse($response->getContent(), CompanyModel::class);

        if (!empty($inputs)) {
            $companyModel = ModelSerializer::parse($inputs, CompanyModel::class);
            $companyModel->setCompanyID($id);
            $request = new Req(Servers::Repository, Repository::Company, 'update');
            $request->add_instance($companyModel);
            $response = $request->send();
            if ($response->getStatus() == ResponseStatus::successful) {
                $this->addFlash('s', '');
            } else {
                $this->addFlash('f', '');
            }
        }
        /**
         * @var $employees PersonModel[]
         */
        $employees = [];
        if ($companyModel->getCompanyEmployees()) {
            foreach ($companyModel->getCompanyEmployees() as $employee) {
                $employees[] = ModelSerializer::parse($employee, PersonModel::class);
            }
        }

        /**
         * @var $phones PhoneModel[]
         */
        $phones = [];
        if ($companyModel->getCompanyPhones()) {
            foreach ($companyModel->getCompanyPhones() as $phone) {
                $phones[] = ModelSerializer::parse($phone, PhoneModel::class);
            }
        }

        $personsRequest = new Req(Servers::Repository, Repository::Person, PersonActions::all);
        $personsResponse = $personsRequest->send();
        $persons = $personsResponse->getContent();

        /**
         * @var $personResults PersonModel[]
         */
        $personResults = [];
        foreach ($persons as $person) {
            $personResults[] = ModelSerializer::parse($person, PersonModel::class);
        }

        return $this->render('repository/company/edit.html.twig', [
            'controller_name' => 'CompanyController',
            'company' => $companyModel,
            'employees' => $employees,
            'persons' => $personResults,
            'phones' => $phones
        ]);
    }

    /**
     * @Route("/read", name="_repository_company_read")
     */
    public function read()
    {
        return $this->render('repository/company/read.html.twig', [
            'controller_name' => 'CompanyController',
        ]);
    }

    /**
     * @Route("/add-employee/{company_id}", name="_repository_company_add_employee")
     * @param $company_id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ReflectionException
     */
    public function addEmployee($company_id, Request $request)
    {
        $inputs = $request->request->all();
        /**
         * @var $employeeModel CompanyAddEmployeeModel
         */
        $employeeModel = ModelSerializer::parse($inputs, CompanyAddEmployeeModel::class);
        $employeeModel->setCompanyID($company_id);
        $request = new Req(Servers::Repository, Repository::Company, 'add_employee');
        $request->add_instance($employeeModel);
        $response = $request->send();
//        dd($response);

        if ($response->getStatus() == ResponseStatus::successful) {
            $this->addFlash('s', $response->getMessage());
        } else {
            $this->addFlash('f', $response->getMessage());
        }
        return $this->redirect($this->generateUrl('repository_company_repository_company_edit', ['id' => $company_id]));
    }

    /**
     * @Route("/remove-employee/{company_id}/{employee_id}", name="_repository_company_remove_employee")
     * @param $company_id
     * @param $employee_id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ReflectionException
     */
    public function removeEmployee($company_id , $employee_id)
    {
        $employeeModel = new CompanyAddEmployeeModel();
        $employeeModel->setCompanyID($company_id);
        $employeeModel->setEmployeeID($employee_id);
        $request = new Req(Servers::Repository, Repository::Company, 'remove_employee');
        $request->add_instance($employeeModel);
        $response = $request->send();
        if ($response->getStatus() == ResponseStatus::successful) {
            $this->addFlash('s' , $response->getMessage());
        } else {
            $this->addFlash('f' , $response->getMessage());
        }
        return $this->redirect($this->generateUrl('repository_company_repository_company_edit', ['id' => $company_id]));
    }

    /**
     * @Route("/add-phone/{company_id}", name="_repository_company_add_phone")
     * @param $company_id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ReflectionException
     */
    public function addPhone($company_id, Request $request)
    {
        $inputs = $request->request->all();
        /**
         * @var $employeeModel CompanyAddEmployeeModel
         */
        $phoneModel = ModelSerializer::parse($inputs, PhoneModel::class);
        $phoneModel->setCompanyID($company_id);
        $request = new Req(Servers::Repository, Repository::Company, 'add_phone');
        $request->add_instance($phoneModel);
        $response = $request->send();
        if ($response->getStatus() == ResponseStatus::successful) {
            $this->addFlash('s', $response->getMessage());
        } else {
            $this->addFlash('f', $response->getMessage());
        }
        return $this->redirect($this->generateUrl('repository_company_repository_company_edit', ['id' => $company_id]));
    }

    /**
     * @Route("/remove-phone/{company_id}/{phone_id}", name="_repository_company_remove_phone")
     * @param $company_id
     * @param $phone_id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ReflectionException
     */
    public function removePhone($company_id, $phone_id)
    {
        $phoneModel = new PhoneModel();
        $phoneModel->setCompanyID($company_id);
        $phoneModel->setId($phone_id);
        $request = new Req(Servers::Repository, Repository::Company, 'remove_phone');
        $request->add_instance($phoneModel);
        $response = $request->send();
        if ($response->getStatus() == ResponseStatus::successful) {
            $this->addFlash('s' , $response->getMessage());
        } else {
            $this->addFlash('f' , $response->getMessage());
        }
        return $this->redirect($this->generateUrl('repository_company_repository_company_edit', ['id' => $company_id]));
    }
}
