<?php

namespace App\Controller\Crm;

use App\FormModels\Accounting\CouponGroupModel;
use App\FormModels\Accounting\GiftCardModel;
use App\FormModels\Accounting\InvoiceModel;
use App\FormModels\Accounting\PaymentModel;
use App\FormModels\CRM\CustomerCouponModel;
use App\FormModels\CRM\CustomerGroupModel;
use App\FormModels\CRM\InvitationModel;
use App\FormModels\ModelSerializer;
use App\FormModels\Notification\SMSModel;
use App\FormModels\Repository\CompanyModel;
use App\FormModels\Repository\ItemModel;
use App\FormModels\Repository\LocationModel;
use App\FormModels\Repository\PersonModel;
use App\FormModels\Repository\ProvinceModel;
use App\FormModels\Sale\OrderModel;
use App\FormModels\Ticketing\CommentModel;
use App\General\AuthUser;
use App\Permissions\ServerPermissions;
use Matican\Core\Entities\Accounting;
use Matican\Core\Entities\Authentication;
use Matican\Core\Entities\CRM;
use Matican\Core\Entities\Notifications;
use Matican\Core\Entities\Repository;
use Matican\Core\Entities\Sale;
use Matican\Core\Servers;
use Matican\Core\Transaction\ResponseStatus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Matican\Core\Transaction\Request as Req;

/**
 * @Route("/crm/customer", name="crm_customer")
 */
class CustomerController extends AbstractController
{
    /**
     * @Route("/list", name="_list")
     */
    public function fetchAll()
    {

        $request = new Req(Servers::Repository, Repository::Person, 'all');
        $response = $request->send();

        /**
         * @var $customers PersonModel[]
         */
        $customers = [];
        if ($response->getContent()) {
            foreach ($response->getContent() as $customer) {
                $customers[] = ModelSerializer::parse($customer, PersonModel::class);
            }
        }
        if (!AuthUser::if_is_allowed(ServerPermissions::repository_person_all)) {
            $customers = [];
        }
        return $this->render('crm/customer/list.html.twig', [
            'controller_name' => 'CustomerController',
            'customers' => $customers,
        ]);
    }

    /**
     * @Route("/customer-info/{id}", name="_info")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \ReflectionException
     */
    public function customerInfo($id, Request $request)
    {
        if (!AuthUser::if_is_allowed(ServerPermissions::repository_person_fetch)) {
            return $this->redirect($this->generateUrl('crm_customer_list'));
        }
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

        $inputs = $request->request->all();
        /**
         * @var $personModel PersonModel
         */
        $personModel = ModelSerializer::parse($inputs, PersonModel::class);
        $personModel->setId($id);
        $request = new Req(Servers::Repository, Repository::Person, 'fetch');
        $request->add_instance($personModel);
        $response = $request->send();
        /**
         * @var $personModel PersonModel
         */
        $personModel = ModelSerializer::parse($response->getContent(), PersonModel::class);

        $locationModel = new LocationModel();

        if (!empty($inputs)) {
            if (isset($inputs['provinceName'])) {
                /**
                 * @var $locationModel LocationModel
                 */
                $locationModel = ModelSerializer::parse($inputs, LocationModel::class);
                $locationModel->setPersonId($id);
                $latLang = str_replace(' ', '', $locationModel->getLocationGeoPoints());
                $latLang = explode(',', $latLang);
                if (count($latLang) != 2) {
                    $this->addFlash('add_address_success', 'geo points are not formatted correctly');
                    return $this->redirect($this->generateUrl('crm_customer_info', ['id' => $locationModel->getPersonId()]));
                }
                $locationModel->setLocationLat($latLang[0]);
                $locationModel->setLocationLng($latLang[1]);
//                dd($locationModel);
                $request = new Req(Servers::Repository, Repository::Location, 'new');
                $request->add_instance($locationModel);
                $response = $request->send();
//                dd($response);
                if ($response->getStatus() == ResponseStatus::successful) {
                    $this->addFlash('add_address_success', $response->getMessage());
                } else {
                    $this->addFlash('add_address_failed', $response->getMessage());
                }


                return $this->redirect($this->generateUrl('crm_customer_info', ['id' => $locationModel->getPersonId()]));
            } else {
                $personModel = ModelSerializer::parse($inputs, PersonModel::class);
                $personModel->setId($id);
                $request = new Req(Servers::Repository, Repository::Person, 'update');
                $request->add_instance($personModel);
                $response = $request->send();
                if ($response->getContent() == ResponseStatus::successful) {
                    $this->addFlash('s', $response->getMessage());
                    /**
                     * @var $personModel PersonModel
                     */
                    $personModel = ModelSerializer::parse($response->getContent(), PersonModel::class);
                    return $this->redirect($this->generateUrl('crm_customer_info', ['id' => $personModel->getId()]));
                } else {
                    $this->addFlash('f', $response->getMessage());
                }
            }
        }

        /**
         * @var $locations LocationModel[]
         */
        $locations = [];
        if ($personModel->getLocations()) {
            foreach ($personModel->getLocations() as $location) {
                $locations[] = ModelSerializer::parse($location, LocationModel::class);
            }
        }


        $provincesRequest = new Req(Servers::Repository, Repository::Location, 'get_provinces');
        $provincesResponse = $provincesRequest->send();

        /**
         * @var $provinces ProvinceModel[]
         */
        $provinces = [];
        if ($provincesResponse->getContent()) {
            foreach ($provincesResponse->getContent() as $province) {
                $provinces[] = ModelSerializer::parse($province, ProvinceModel::class);
            }
        }

        $url = $_SERVER['PHP_SELF'];
        $url = explode('index.php', $url)[1];

        return $this->render('crm/customer/read-info.html.twig', [
            'controller_name' => 'CustomerController',
            'personModel' => $personModel,
            'locationModel' => $locationModel,
            'locations' => $locations,
            'provinces' => $provinces,
            'years' => $years,
            'months' => $months,
            'days' => $days,
            'url' => $url,
        ]);
    }


    /**
     * @Route("/remove-address/{location_id}/{person_id}", name="_remove_address")
     * @param $location_id
     * @param $person_id
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \ReflectionException
     */
    public function removeAddress($location_id, $person_id)
    {
        if (!AuthUser::if_is_allowed(ServerPermissions::repository_location_remove)) {
            return $this->redirect($this->generateUrl('crm_customer_list'));
        }
        $locationModel = new LocationModel();
        $locationModel->setLocationId($location_id);
        $locationModel->setPersonId($person_id);
        $request = new Req(Servers::Repository, Repository::Location, 'remove');
        $request->add_instance($locationModel);
        $response = $request->send();


        if ($response->getStatus() == ResponseStatus::successful) {
            $this->addFlash('add_address_success', $response->getMessage());
        } else {
            $this->addFlash('add_address_failed', $response->getMessage());
        }
        return $this->redirect($this->generateUrl('crm_customer_info', ['id' => $person_id]));
    }


    /**
     * @Route("/sms-log/{id}", name="_sms_log")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \ReflectionException
     */
    public function customerSMSLog($id, Request $request)
    {
//        if (!AuthUser::if_is_allowed(ServerPermissions::)) {
//
//        }

        /**
         * @todo Authorization here is incomplete
         */
        $inputs = $request->request->all();
        /**
         * @var $personModel PersonModel
         */
        $personModel = ModelSerializer::parse($inputs, PersonModel::class);
        $personModel->setId($id);
        $request = new Req(Servers::Notifications, Notifications::SMS, 'get_person_sms_logs');
        $request->add_instance($personModel);
        $response = $request->send();
        /**
         * @var $personModel PersonModel
         */
        $personModel = ModelSerializer::parse($response->getContent(), PersonModel::class);

        /**
         * @var $smsLogs SMSModel[]
         */
        $smsLogs = [];
        if ($personModel->getSmsLogs()) {
            foreach ($personModel->getSmsLogs() as $smsLog) {
                $smsLogs[] = ModelSerializer::parse($smsLog, SMSModel::class);
            }
        }

        $url = $_SERVER['PHP_SELF'];
        $url = explode('index.php', $url)[1];


        return $this->render('crm/customer/read-sms.html.twig', [
            'controller_name' => 'CustomerController',
            'personModel' => $personModel,
            'smsLogs' => $smsLogs,
            'url' => $url
        ]);
    }

    /**
     * @Route("/order/{id}", name="_order")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \ReflectionException
     */
    public function customerOrder($id, Request $request)
    {
        if (!AuthUser::if_is_allowed(ServerPermissions::sale_order_get_person_orders)) {
            return $this->redirect($this->generateUrl('crm_customer_list'));
        }
        $inputs = $request->request->all();
        /**
         * @var $personModel PersonModel
         */
        $personModel = ModelSerializer::parse($inputs, PersonModel::class);
        $personModel->setId($id);
        $request = new Req(Servers::Sale, Sale::Order, 'get_person_orders');
        $request->add_instance($personModel);
        $response = $request->send();
        /**
         * @var $personModel PersonModel
         */
        $personModel = ModelSerializer::parse($response->getContent(), PersonModel::class);

        /**
         * @var $orders OrderModel[]
         */
        $orders = [];
        if ($personModel->getOrders()) {
            foreach ($personModel->getOrders() as $order) {
                $orders[] = ModelSerializer::parse($order, OrderModel::class);
            }
        }


        $url = $_SERVER['PHP_SELF'];
        $url = explode('index.php', $url)[1];

        return $this->render('crm/customer/read-order.html.twig', [
            'controller_name' => 'CustomerController',
            'personModel' => $personModel,
            'url' => $url,
            'orders' => $orders,
        ]);
    }

    /**
     * @Route("/favorite/{id}", name="_favorite")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \ReflectionException
     */
    public function customerFavorite($id, Request $request)
    {
        if (!AuthUser::if_is_allowed(ServerPermissions::repository_person_fetch)) {
            return $this->redirect($this->generateUrl('crm_customer_list'));
        }
        $inputs = $request->request->all();
        /**
         * @var $personModel PersonModel
         */
        $personModel = ModelSerializer::parse($inputs, PersonModel::class);
        $personModel->setId($id);
        $request = new Req(Servers::Repository, Repository::Person, 'fetch');
        $request->add_instance($personModel);
        $response = $request->send();
        /**
         * @var $personModel PersonModel
         */
        $personModel = ModelSerializer::parse($response->getContent(), PersonModel::class);

        /**
         * @var $favoriteItems ItemModel[]
         */
        $favoriteItems = [];
        if ($personModel->getFavoriteItems()) {
            foreach ($personModel->getFavoriteItems() as $favoriteItem) {
                $favoriteItems[] = ModelSerializer::parse($favoriteItem, ItemModel::class);
            }
        }


        $url = $_SERVER['PHP_SELF'];
        $url = explode('index.php', $url)[1];


        return $this->render('crm/customer/read-favorite.html.twig', [
            'controller_name' => 'CustomerController',
            'personModel' => $personModel,
            'url' => $url,
            'favoriteItems' => $favoriteItems,
        ]);
    }

    /**
     * @Route("/cart", name="_cart")
     */
    public function customerCart()
    {
        return $this->render('crm/customer/read-cart.html.twig', [
            'controller_name' => 'CustomerController',
        ]);
    }

    /**
     * @Route("/gift-coupon/{id}", name="_gift_coupon")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \ReflectionException
     */
    public function customerGiftCoupon($id, Request $request)
    {
        /**
         * @todo Authorization here (maybe the controller and requested actions are wrong, "person fetch")
         */
        $inputs = $request->request->all();
        /**
         * @var $personModel PersonModel
         */
        $personModel = ModelSerializer::parse($inputs, PersonModel::class);
        $personModel->setId($id);
        $request = new Req(Servers::Repository, Repository::Person, 'fetch');
        $request->add_instance($personModel);
        $response = $request->send();
        /**
         * @var $personModel PersonModel
         */
        $personModel = ModelSerializer::parse($response->getContent(), PersonModel::class);

        /**
         * @var $giftCards GiftCardModel[]
         */
        $giftCards = [];
        if ($personModel->getGiftCards()) {
            foreach ($personModel->getGiftCards() as $giftCard) {
                $giftCards[] = ModelSerializer::parse($giftCard, GiftCardModel::class);
            }
        }

        /**
         * @var $coupons CustomerCouponModel[]
         */
        $coupons = [];
        if ($personModel->getCoupons()) {
            foreach ($personModel->getCoupons() as $coupon) {
                $coupons[] = ModelSerializer::parse($coupon, CustomerCouponModel::class);
            }
        }


        $url = $_SERVER['PHP_SELF'];
        $url = explode('index.php', $url)[1];

        return $this->render('crm/customer/read-gift-coupon.html.twig', [
            'controller_name' => 'CustomerController',
            'personModel' => $personModel,
            'url' => $url,
            'giftCards' => $giftCards,
            'coupons' => $coupons,
        ]);
    }

    /**
     * @Route("/company-group/{id}", name="_company_group")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \ReflectionException
     */
    public function customerCompanyGroup($id, Request $request)
    {
        if (!AuthUser::if_is_allowed(ServerPermissions::repository_person_fetch)) {
            return $this->redirect($this->generateUrl('crm_customer_list'));
        }
        $inputs = $request->request->all();
        /**
         * @var $personModel PersonModel
         */
        $personModel = ModelSerializer::parse($inputs, PersonModel::class);
        $personModel->setId($id);
        $request = new Req(Servers::Repository, Repository::Person, 'fetch');
        $request->add_instance($personModel);
        $response = $request->send();
        /**
         * @var $personModel PersonModel
         */
        $personModel = ModelSerializer::parse($response->getContent(), PersonModel::class);

        /**
         * @var $companies CompanyModel[]
         */
        $companies = [];
        if ($personModel->getCompanies()) {
            foreach ($personModel->getCompanies() as $company) {
                $companies[] = ModelSerializer::parse($company, CompanyModel::class);
            }
        }

        /**
         * @var $groups CustomerGroupModel[]
         */
        $groups = [];
        if ($personModel->getGroups()) {
            foreach ($personModel->getGroups() as $group) {
                $groups[] = ModelSerializer::parse($group, CustomerGroupModel::class);
            }
        }


        $url = $_SERVER['PHP_SELF'];
        $url = explode('index.php', $url)[1];

        return $this->render('crm/customer/read-company-group.html.twig', [
            'controller_name' => 'CustomerController',
            'personModel' => $personModel,
            'url' => $url,
            'companies' => $companies,
            'groups' => $groups,
        ]);
    }

    /**
     * @Route("/payment-deed/{id}", name="_payment_deed")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \ReflectionException
     */
    public function customerPaymentDeed($id, Request $request)
    {
        if (!AuthUser::if_is_allowed(ServerPermissions::repository_person_fetch)) {
            return $this->redirect($this->generateUrl('crm_customer_list'));
        }
        $inputs = $request->request->all();
        /**
         * @var $personModel PersonModel
         */
        $personModel = ModelSerializer::parse($inputs, PersonModel::class);
        $personModel->setId($id);
        $request = new Req(Servers::Repository, Repository::Person, 'fetch');
        $request->add_instance($personModel);
        $response = $request->send();
        /**
         * @var $personModel PersonModel
         */
        $personModel = ModelSerializer::parse($response->getContent(), PersonModel::class);

        /**
         * @var $payments PaymentModel[]
         */
        $payments = [];
        if ($personModel->getPayments()) {
            foreach ($personModel->getPayments() as $payment) {
                $payments[] = ModelSerializer::parse($payment, PaymentModel::class);
            }
        }


        $url = $_SERVER['PHP_SELF'];
        $url = explode('index.php', $url)[1];


        return $this->render('crm/customer/read-payment-deed.html.twig', [
            'controller_name' => 'CustomerController',
            'personModel' => $personModel,
            'url' => $url,
            'payments' => $payments,
        ]);
    }

    /**
     * @Route("/invoice/{id}", name="_invoice")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \ReflectionException
     */
    public function customerInvoice($id, Request $request)
    {
        if (!AuthUser::if_is_allowed(ServerPermissions::repository_person_fetch)) {
            return $this->redirect($this->generateUrl('crm_customer_list'));
        }
        $inputs = $request->request->all();
        /**
         * @var $personModel PersonModel
         */
        $personModel = ModelSerializer::parse($inputs, PersonModel::class);
        $personModel->setId($id);
        $request = new Req(Servers::Repository, Repository::Person, 'fetch');
        $request->add_instance($personModel);
        $response = $request->send();
        /**
         * @var $personModel PersonModel
         */
        $personModel = ModelSerializer::parse($response->getContent(), PersonModel::class);

        /**
         * @var $invoices InvoiceModel[]
         */
        $invoices = [];
        if ($personModel->getInvoices()) {
            foreach ($personModel->getInvoices() as $invoice) {
                $invoices[] = ModelSerializer::parse($invoice, InvoiceModel::class);
            }
        }


        $url = $_SERVER['PHP_SELF'];
        $url = explode('index.php', $url)[1];


        return $this->render('crm/customer/read-invoice.html.twig', [
            'controller_name' => 'CustomerController',
            'personModel' => $personModel,
            'url' => $url,
            'invoices' => $invoices,
        ]);
    }

    /**
     * @Route("/bank-account", name="_crm_customer_bank_account")
     */
    public function customerBankAccount()
    {
        return $this->render('crm/customer/read-bank-account.html.twig', [
            'controller_name' => 'CustomerController',
        ]);
    }

    /**
     * @Route("/invitation/{id}", name="_invitation")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \ReflectionException
     */
    public function customerInvitation($id, Request $request)
    {
        /**
         * @todo Authorization here
         */
        if (!AuthUser::if_is_allowed(ServerPermissions::repository_person_fetch)) {
            return $this->redirect($this->generateUrl('crm_customer_list'));
        }
        $inputs = $request->request->all();
        /**
         * @var $personModel PersonModel
         */
        $personModel = ModelSerializer::parse($inputs, PersonModel::class);
        $personModel->setId($id);
        $request = new Req(Servers::Repository, Repository::Person, 'fetch');
        $request->add_instance($personModel);
        $response = $request->send();
        /**
         * @var $personModel PersonModel
         */
        $personModel = ModelSerializer::parse($response->getContent(), PersonModel::class);

        /**
         * @var $invitations InvitationModel[]
         */
        $invitations = [];
        if ($personModel->getInvitations()) {
            foreach ($personModel->getInvitations() as $invitation) {
                $invitations[] = ModelSerializer::parse($invitation, InvitationModel::class);
            }
        }

        $url = $_SERVER['PHP_SELF'];
        $url = explode('index.php', $url)[1];

        /**
         * @var $invitationModel InvitationModel
         */
        $invitationModel = ModelSerializer::parse($inputs, InvitationModel::class);

        if (!empty($inputs) && $inputs['invitedPersonFirstName']) {
            $request = new Req(Servers::CRM, CRM::Invitation, 'new_invite');
            $request->add_instance($invitationModel);
            $response = $request->send();
            if ($response->getStatus() == ResponseStatus::successful) {
                $this->addFlash('s', $response->getMessage());
            } else {
                $this->addFlash('s', $response->getMessage());
            }
            return $this->redirect($this->generateUrl('crm_customer_invitation', ['id' => $id]));
        }

        return $this->render('crm/customer/read-invitation.html.twig', [
            'controller_name' => 'CustomerController',
            'personModel' => $personModel,
            'url' => $url,
            'invitations' => $invitations,
            'invitationModel' => $invitationModel,
        ]);
    }

    /**
     * @Route("/comment/{id}", name="_comment")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \ReflectionException
     */
    public function customerComment($id, Request $request)
    {
        /**
         * @todo Authorization here
         */
        $inputs = $request->request->all();
        /**
         * @var $personModel PersonModel
         */
        $personModel = ModelSerializer::parse($inputs, PersonModel::class);
        $personModel->setId($id);
        $request = new Req(Servers::Repository, Repository::Person, 'fetch');
        $request->add_instance($personModel);
        $response = $request->send();
        /**
         * @var $personModel PersonModel
         */
        $personModel = ModelSerializer::parse($response->getContent(), PersonModel::class);

        /**
         * @var $comments CommentModel[]
         */
        $comments = [];
        if ($personModel->getComments()) {
            foreach ($personModel->getComments() as $comment) {
                $comments[] = ModelSerializer::parse($comment, CommentModel::class);
            }
        }


        $url = $_SERVER['PHP_SELF'];
        $url = explode('index.php', $url)[1];

        return $this->render('crm/customer/read-comment.html.twig', [
            'controller_name' => 'CustomerController',
            'personModel' => $personModel,
            'url' => $url,
            'comments' => $comments,
        ]);
    }
}
