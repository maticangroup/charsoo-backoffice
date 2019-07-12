<?php

namespace App\Controller\Repository;

use App\FormModels\ModelSerializer;
use App\FormModels\Repository\LocationModel;
use App\FormModels\Repository\PersonModel;
use Matican\Actions\Repository\PersonActions;
use Matican\Core\Entities\Repository;
use Matican\Core\Servers;
use Matican\Core\Transaction\Method;
use Matican\Core\Transaction\Request as Req;
use Matican\Core\Transaction\ResponseStatus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/repository/person", name="repository_person")
 */
class PersonController extends AbstractController
{
    /**
     * @Route("/list", name="_repository_person_list")
     */
    public function fetchAll()
    {
        $request = new Req(Servers::Repository, Repository::Person, PersonActions::all);
        $response = $request->send();
        $persons = $response->getContent();
        /**
         * @var $results PersonModel[]
         */
        $results = [];
        foreach ($persons as $person) {
            $results[] = ModelSerializer::parse($person, PersonModel::class);
        }
        /**
         * @var $person PersonModel
         */
        return $this->render('repository/person/list.html.twig', [
            'controller_name' => 'PersonController',
            'persons' => $results
        ]);
    }

    /**
     * @Route("/create", name="_repository_person_create")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \ReflectionException
     */
    public function create(Request $request)
    {
        $inputs = $request->request->all();

        $years = [];
        for ($i = 1950; $i <= 2019; $i++) {
            $years[] = $i;
        }

        $months = [];
        for ($j = 1; $j <= 12; $j++) {
            $months[] = $j;
        }

        $days = [];
        for ($k = 1; $k <= 31; $k++) {
            $days[] = $k;
        }

        if (empty($inputs)) {
            $person = new PersonModel();

            return $this->render('repository/person/create.html.twig', [
                'controller_name' => 'PersonController',
                'years' => $years,
                'months' => $months,
                'days' => $days,
                'person' => $person
            ]);
        } else {
            $person = new PersonModel();
            $person->setHumanName($inputs['person_name']);
            $person->setHumanFamily($inputs['person_family']);
            $person->setEmail($inputs['person_email']);
            $person->setBirthDateYear($inputs['person_birth_date_year']);
            $person->setBirthDateMonth($inputs['person_birth_date_month']);
            $person->setBirthDateDay($inputs['person_birth_date_day']);
            $person->setNationalCode($inputs['person_national_code']);
            $person->setMobile($inputs['person_mobile']);
            $person->setSendSMS((isset($inputs['send_sms'])) ? true : false);
            if ($person->getSendSMS()) {
                /**
                 * @todo send sms should be handle by Notification server
                 */
            }

            $request = new Req(Servers::Repository, Repository::Person, PersonActions::new);
            $request->setMethod(Method::POST);
            $request->add_instance($person);
            $response = $request->send();

            if ($response->getStatus() == ResponseStatus::successful) {
                $this->addFlash('add_person_success', $response->getMessage());
                /**
                 * @var $responsePerson PersonModel
                 */
                $responsePerson = ModelSerializer::parse($response->getContent(), PersonModel::class);
                return $this->redirect($this->generateUrl('repository_person_repository_person_edit', ['id' => $responsePerson->getId()]));

            } else {
                $this->addFlash('add_person_failed', $response->getMessage());
                return $this->render('repository/person/create.html.twig', [
                    'controller_name' => 'PersonController',
                    'years' => $years,
                    'months' => $months,
                    'days' => $days,
                    'person' => $person
                ]);

            }
            /**
             * @todo redirect to READ if user is not allowed to edit
             */
        }
    }

    /**
     * @Route("/quick_save", name="_repository_person_quick_save")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ReflectionException
     */
    public function quickSave(Request $request)
    {
        $inputs = $request->request->all();

        $person = new PersonModel();
        $person->setHumanName($inputs['person_name']);
        $person->setHumanFamily($inputs['person_family']);
        $person->setMobile($inputs['person_mobile']);

        $request = new Req(Servers::Repository, Repository::Person, PersonActions::quick_register);
        $request->setMethod(Method::POST);
        $request->add_instance($person);
        $response = $request->send();

        if ($response->getStatus() == ResponseStatus::record_added_successfully) {
            $this->addFlash('add_person_quick_success', $response->getMessage());
        } else {
            $this->addFlash('add_person_quick_failed', $response->getMessage());
        }

        $referer = $_SERVER['HTTP_REFERER'];
        return $this->redirect($referer);
    }

    /**
     * @Route("/edit/{id}", name="_repository_person_edit")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \ReflectionException
     */
    public function edit($id, Request $request)
    {
        $inputs = $request->request->all();

        $years = [];
        for ($i = 1950; $i <= 2019; $i++) {
            $years[] = $i;
        }

        $months = [];
        for ($j = 1; $j <= 12; $j++) {
            $months[] = $j;
        }

        $days = [];
        for ($k = 1; $k <= 31; $k++) {
            $days[] = $k;
        }

        /**
         * @todo check action all addresses request
         */
        $request = new Req(Servers::Repository, Repository::Person, 'get_all_addresses');
        $personModel = new PersonModel();
        $personModel->setId($id);
        $request->add_instance($personModel);
        $response = $request->send();
        $responseLocations = $response->getContent();
        /**
         * @var $locationModels LocationModel[]
         */
        $locationModels = [];
        foreach ($responseLocations as $location) {
            $locationModels[] = ModelSerializer::parse($location, LocationModel::class);
        }

        if (empty($inputs)) {
            $request = new Req(Servers::Repository, Repository::Person, PersonActions::fetch);
            $request->add_query('id', $id);
            $response = $request->send();
            $responsePerson = $response->getContent();

            $person = new PersonModel();
            $person->setHumanName($responsePerson['humanName']);
            $person->setHumanFamily($responsePerson['humanFamily']);
            $person->setBirthDateYear($responsePerson['birthDateYear']);
            $person->setBirthDateMonth($responsePerson['birthDateMonth']);
            $person->setBirthDateDay($responsePerson['birthDateDay']);
            $person->setMobile($responsePerson['mobile']);
            $person->setNationalCode($responsePerson['nationalCode']);
            $person->setEmail($responsePerson['email']);


            /**
             * @todo create LocationActions
             */
//        $request = new Req(Servers::Repository, Repository::Location, LocationActions::get_person_addresses);
//        $request->add_query('id', $id);
//        $response = $request->send();
//        $personAddresses = $response->getContent();

//        dd($person);
//        dd($personAddresses);
            /**
             * @todo create "year","month","day" classes
             */

            return $this->render('repository/person/edit.html.twig', [
                'controller_name' => 'PersonController',
                'person' => $person,
                'years' => $years,
                'months' => $months,
                'days' => $days,
                'personID' => $id,
                'locations' =>  $locationModels
//            'personAddresses' => $personAddresses
            ]);

        } else {
            $person = new PersonModel();
            $person->setId($inputs['person_id']);
            $person->setHumanName($inputs['person_name']);
            $person->setHumanFamily($inputs['person_family']);
            $person->setEmail($inputs['person_email']);
            $person->setBirthDateYear($inputs['person_birth_date_year']);
            $person->setBirthDateMonth($inputs['person_birth_date_month']);
            $person->setBirthDateDay($inputs['person_birth_date_day']);
            $person->setNationalCode($inputs['person_national_code']);
            $person->setMobile($inputs['person_mobile']);

            $request = new Req(Servers::Repository, Repository::Person, PersonActions::update);
            $request->setMethod(Method::POST);
            $request->add_instance($person);
            $response = $request->send();

            if ($response->getStatus() == ResponseStatus::successful) {
                /**
                 * @todo ResponseStatus should be change to record_update_successfully
                 */
                $this->addFlash('update_person_success', $response->getMessage());

            } else {
                $this->addFlash('update_person_failed', $response->getMessage());
            }

            return $this->render('repository/person/edit.html.twig', [
                'controller_name' => 'PersonController',
                'person' => $person,
                'years' => $years,
                'months' => $months,
                'days' => $days,
                'personID' => $id,
                'locations' => $locationModels
//            'personAddresses' => $personAddresses
            ]);
        }

    }

    /**
     * @Route("/save-address", name="_repository_person_save_address")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \ReflectionException
     */
    public function addAddress(Request $request)
    {
        $inputs = $request->request->all();
        /**
         * @var $locationModel LocationModel
         */
        $locationModel = ModelSerializer::parse($inputs, LocationModel::class);
        $request = new Req(Servers::Repository, Repository::Person, PersonActions::add_address);
        $request->setMethod(Method::POST);
        $request->add_instance($locationModel);
        $response = $request->send();


        if ($response->getStatus() == ResponseStatus::successful) {
            $this->addFlash('add_address_success', $response->getMessage());
        } else {
            $this->addFlash('add_address_failed', $response->getMessage());
        }


        return $this->redirect($this->generateUrl('repository_person_repository_person_edit', ['id' => $locationModel->getPersonId()]));
    }

    /**
     * @Route("/update-address", name="_repository_person_update_address")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \ReflectionException
     */
    public function updateAddress(Request $request)
    {
        $inputs = $request->request->all();
        /**
         * @var $locationModel LocationModel
         */
        $locationModel = ModelSerializer::parse($inputs, LocationModel::class);
        /**
         * @todo check action
         */
        $request = new Req(Servers::Repository, Repository::Person, 'update_address');
        $request->setMethod(Method::POST);
        $request->add_instance($locationModel);
        $response = $request->send();


        if ($response->getStatus() == ResponseStatus::successful) {
            $this->addFlash('add_address_success', $response->getMessage());
        } else {
            $this->addFlash('add_address_failed', $response->getMessage());
        }


        return $this->redirect($this->generateUrl('repository_person_repository_person_edit', ['id' => $locationModel->getPersonId()]));
    }
}
