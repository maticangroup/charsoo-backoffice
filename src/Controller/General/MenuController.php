<?php

namespace App\Controller\General;

use App\General\AuthUser;
use App\Permissions\ServerPermissions;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/general/menu", name="general_menu")
 */
class MenuController extends AbstractController
{
    /**
     * @Route("/side_menu", name="_side_menu")
     */
    public function sideMenu()
    {
        $sideMenu = [
            //Accounting
            [
                'main_menu_name' => 'Accounting',
                'main_menu_items' => [
                    [
                        'menu_item_name' => 'Coupon Group',
                        'menu_item_icon' => 'fa fa-th',
                        'menu_item_children' => [
                            [
                                'menu_child_name' => 'List',
                                'menu_child_link' => 'http://127.0.0.1:8000/accounting/coupon-group/list',
                                'action' => ServerPermissions::accounting_coupongroup_all
                            ],
                            [
                                'menu_child_name' => 'Create',
                                'menu_child_link' => 'http://127.0.0.1:8000/accounting/coupon-group/create',
                                'action' => ServerPermissions::accounting_coupongroup_new
                            ],
                        ]

                    ],
                    [
                        'menu_item_name' => 'Gift Card Group',
                        'menu_item_icon' => 'fa fa-th',
                        'menu_item_children' => [
                            [
                                'menu_child_name' => 'List',
                                'menu_child_link' => 'http://127.0.0.1:8000/accounting/gift-card-group/list',
                                'action' => ServerPermissions::accounting_giftcardgroup_all
                            ],
                            [
                                'menu_child_name' => 'Create',
                                'menu_child_link' => 'http://127.0.0.1:8000/accounting/gift-card-group/create',
                                'action' => ServerPermissions::accounting_giftcardgroup_new
                            ],
                        ]

                    ],
                    [
                        'menu_item_name' => 'Invoice',
                        'menu_item_icon' => 'fa fa-th',
                        'menu_item_children' => [
                            [
                                'menu_child_name' => 'List',
                                'menu_child_link' => 'http://127.0.0.1:8000/accounting/invoice/list',
                                'action' => ServerPermissions::accounting_invoice_all
                            ],
                            [
                                'menu_child_name' => 'Create',
                                'menu_child_link' => 'http://127.0.0.1:8000/accounting/invoice/create',
                                'action' => ServerPermissions::accounting_invoice_new
                            ],
                        ]

                    ],
                ]


            ],
            //Accounting

            //CRM
            [
                'main_menu_name' => 'CRM',
                'main_menu_items' => [
                    [
                        'menu_item_name' => 'Customer Group',
                        'menu_item_icon' => 'fa fa-th',
                        'menu_item_children' => [
                            [
                                'menu_child_name' => 'Create',
                                'menu_child_link' => 'http://127.0.0.1:8000/crm/customer-group/create',
                                'action' => ServerPermissions::crm_customergroup_new
                            ],
                        ]

                    ],
                    [
                        'menu_item_name' => 'Customer',
                        'menu_item_icon' => 'fa fa-th',
                        'menu_item_children' => [
                            [
                                'menu_child_name' => 'List',
                                'menu_child_link' => 'http://127.0.0.1:8000/crm/customer/list',
                                'action' => ServerPermissions::crm_customergroup_all
                            ],
                        ]

                    ],
                ]
            ],
            //CRM

            //Delivery
            [
                'main_menu_name' => 'Delivery',
                'main_menu_items' => [
                    [
                        'menu_item_name' => 'Delivery Method',
                        'menu_item_icon' => 'fa fa-th',
                        'menu_item_children' => [
                            [
                                'menu_child_name' => 'List',
                                'menu_child_link' => 'http://127.0.0.1:8000/delivery/delivery-method/list',
                                'action' => ServerPermissions::delivery_deliverymethod_all
                            ],
                            [
                                'menu_child_name' => 'Create',
                                'menu_child_link' => 'http://127.0.0.1:8000/delivery/delivery-method/create',
                                'action' => ServerPermissions::delivery_deliverymethod_new
                            ],
                        ]

                    ],
                    [
                        'menu_item_name' => 'Delivery Person',
                        'menu_item_icon' => 'fa fa-th',
                        'menu_item_children' => [
                            [
                                'menu_child_name' => 'List',
                                'menu_child_link' => 'http://127.0.0.1:8000/delivery/delivery-person/list',
                                'action' => ServerPermissions::delivery_deliveryperson_all
                            ],
                        ]

                    ],
                    [
                        'menu_item_name' => 'Dispatch',
                        'menu_item_icon' => 'fa fa-th',
                        'menu_item_children' => [
                            [
                                'menu_child_name' => 'List',
                                'menu_child_link' => 'http://127.0.0.1:8000/delivery/dispatch/list',
                                'action' => ServerPermissions::delivery_dispatch_all
                            ],
                            [
                                'menu_child_name' => 'Create',
                                'menu_child_link' => 'http://127.0.0.1:8000/delivery/dispatch/create',
                                'action' => ServerPermissions::delivery_dispatch_new
                            ],
                        ]

                    ],
                ]
            ],
            //Delivery

            //Inventory
            [
                'main_menu_name' => 'Inventory',
                'main_menu_items' => [
                    [
                        'menu_item_name' => 'Inventory',
                        'menu_item_icon' => 'fa fa-th',
                        'menu_item_children' => [
                            [
                                'menu_child_name' => 'List',
                                'menu_child_link' => 'http://127.0.0.1:8000/inventory/list',
                                'action' => ServerPermissions::inventory_inventory_all
                            ],
                            [
                                'menu_child_name' => 'Create',
                                'menu_child_link' => 'http://127.0.0.1:8000/inventory/create',
                                'action' => ServerPermissions::inventory_inventory_new
                            ],
                        ]

                    ],
                    [
                        'menu_item_name' => 'Shelve',
                        'menu_item_icon' => 'fa fa-th',
                        'menu_item_children' => [
                            [
                                'menu_child_name' => 'List',
                                'menu_child_link' => 'http://127.0.0.1:8000/inventory/shelve/list',
                                'action' => ServerPermissions::inventory_shelve_all
                            ],
                            [
                                'menu_child_name' => 'Create',
                                'menu_child_link' => 'http://127.0.0.1:8000/inventory/shelve/create',
                                'action' => ServerPermissions::inventory_shelve_new
                            ],
                        ]

                    ],
                    [
                        'menu_item_name' => 'Deed',
                        'menu_item_icon' => 'fa fa-th',
                        'menu_item_children' => [
                            [
                                'menu_child_name' => 'List',
                                'menu_child_link' => 'http://127.0.0.1:8000/inventory/deed/list',
                                'action' => ServerPermissions::inventory_transferdeed_all
                            ],
                            [
                                'menu_child_name' => 'Create',
                                'menu_child_link' => 'http://127.0.0.1:8000/inventory/deed/create',
                                'action' => ServerPermissions::inventory_transferdeed_new
                            ],
                        ]

                    ],
                    [
                        'menu_item_name' => 'Stock',
                        'menu_item_icon' => 'fa fa-th',
                        'menu_item_children' => [
                            [
                                'menu_child_name' => 'List',
                                'menu_child_link' => 'http://127.0.0.1:8000/inventory/stock/list',
                                'action' => ServerPermissions::inventory_inventory_all_item_products
                            ],
                        ]

                    ],
                ]
            ],
            //Inventory

            //Notification
            [
                'main_menu_name' => 'Notification',
                'main_menu_items' => [
                    [
                        'menu_item_name' => 'Mail Template',
                        'menu_item_icon' => 'fa fa-th',
                        'menu_item_children' => [
                            [
                                'menu_child_name' => 'List',
                                'menu_child_link' => 'http://127.0.0.1:8000/delivery/delivery-method/list',
                                'action' => ServerPermissions::delivery_deliverymethod_all
                            ],
                        ]

                    ],
                ]
            ],
            //Notification

            //Repository
            [
                'main_menu_name' => 'Repository',
                'main_menu_items' => [
                    [
                        'menu_item_name' => 'Brand',
                        'menu_item_icon' => 'fa fa-th',
                        'menu_item_children' => [
                            [
                                'menu_child_name' => 'Create',
                                'menu_child_link' => 'http://127.0.0.1:8000/repository/brand/create',
                                'action' => ServerPermissions::repository_brand_new
                            ],
                        ]

                    ],
                    [
                        'menu_item_name' => 'Company',
                        'menu_item_icon' => 'fa fa-th',
                        'menu_item_children' => [
                            [
                                'menu_child_name' => 'List',
                                'menu_child_link' => 'http://127.0.0.1:8000/repository/company/list',
                                'action' => ServerPermissions::repository_company_all
                            ],
                            [
                                'menu_child_name' => 'Create',
                                'menu_child_link' => 'http://127.0.0.1:8000/repository/company/create',
                                'action' => ServerPermissions::repository_company_new
                            ],
                        ]

                    ],
                    [
                        'menu_item_name' => 'Forbidden Words',
                        'menu_item_icon' => 'fa fa-th',
                        'menu_item_children' => [
                            [
                                'menu_child_name' => 'Create',
                                'menu_child_link' => 'http://127.0.0.1:8000/repository/forbidden-words/create',
                                'action' => ServerPermissions::repository_forbiddenwords_new
                            ],
                        ]

                    ],
                    [
                        'menu_item_name' => 'Guarantee',
                        'menu_item_icon' => 'fa fa-th',
                        'menu_item_children' => [
                            [
                                'menu_child_name' => 'Create',
                                'menu_child_link' => 'http://127.0.0.1:8000/repository/guarantee/create',
                                'action' => ServerPermissions::repository_guarantee_new
                            ],
                        ]

                    ],
                    [
                        'menu_item_name' => 'Guarantee Duration',
                        'menu_item_icon' => 'fa fa-th',
                        'menu_item_children' => [
                            [
                                'menu_child_name' => 'Create',
                                'menu_child_link' => 'http://127.0.0.1:8000/repository/guarantee-duration/create',
                                'action' => ServerPermissions::repository_guarantee_add_duration
                            ],
                        ]

                    ],
                    [
                        'menu_item_name' => 'Guarantee Provider',
                        'menu_item_icon' => 'fa fa-th',
                        'menu_item_children' => [
                            [
                                'menu_child_name' => 'Create',
                                'menu_child_link' => 'http://127.0.0.1:8000/repository/guarantee-provider/create',
                                'action' => ServerPermissions::repository_guarantee_add_provider
                            ],
                        ]

                    ],
                    [
                        'menu_item_name' => 'Color',
                        'menu_item_icon' => 'fa fa-th',
                        'menu_item_children' => [
                            [
                                'menu_child_name' => 'Create',
                                'menu_child_link' => 'http://127.0.0.1:8000/repository/color/create',
                                'action' => ServerPermissions::repository_color_new
                            ],
                        ]

                    ],
                    [
                        'menu_item_name' => 'Off Day',
                        'menu_item_icon' => 'fa fa-th',
                        'menu_item_children' => [
                            [
                                'menu_child_name' => 'Create',
                                'menu_child_link' => 'http://127.0.0.1:8000/repository/off-day/create',
                                'action' => ServerPermissions::repository_offdays_new
                            ],
                        ]

                    ],
                    [
                        'menu_item_name' => 'Person',
                        'menu_item_icon' => 'fa fa-th',
                        'menu_item_children' => [
                            [
                                'menu_child_name' => 'List',
                                'menu_child_link' => 'http://127.0.0.1:8000/repository/person/list',
                                'action' => ServerPermissions::repository_person_all
                            ],
                            [
                                'menu_child_name' => 'Create',
                                'menu_child_link' => 'http://127.0.0.1:8000/repository/person/create',
                                'action' => ServerPermissions::repository_person_new
                            ],
                        ]

                    ],
                    [
                        'menu_item_name' => 'Size',
                        'menu_item_icon' => 'fa fa-th',
                        'menu_item_children' => [
                            [
                                'menu_child_name' => 'Create',
                                'menu_child_link' => 'http://127.0.0.1:8000/repository/size/create',
                                'action' => ServerPermissions::repository_size_new
                            ],
                        ]

                    ],
                    [
                        'menu_item_name' => 'Spec Group',
                        'menu_item_icon' => 'fa fa-th',
                        'menu_item_children' => [
                            [
                                'menu_child_name' => 'Create',
                                'menu_child_link' => 'http://127.0.0.1:8000/repository/spec-group/create',
                                'action' => ServerPermissions::repository_specgroup_new
                            ],
                        ]

                    ],
                    [
                        'menu_item_name' => 'Item Category',
                        'menu_item_icon' => 'fa fa-th',
                        'menu_item_children' => [
                            [
                                'menu_child_name' => 'Create',
                                'menu_child_link' => 'http://127.0.0.1:8000/repository/item-category/create',
                                'action' => ServerPermissions::repository_itemcategory_new
                            ],
                        ]

                    ],
                    [
                        'menu_item_name' => 'Item',
                        'menu_item_icon' => 'fa fa-th',
                        'menu_item_children' => [
                            [
                                'menu_child_name' => 'List',
                                'menu_child_link' => 'http://127.0.0.1:8000/repository/item/list',
                                'action' => ServerPermissions::repository_item_all
                            ],
                            [
                                'menu_child_name' => 'Create',
                                'menu_child_link' => 'http://127.0.0.1:8000/repository/item/create',
                                'action' => ServerPermissions::repository_item_new
                            ],
                        ]

                    ],
                ]
            ],
            //Repository

            //Sale
            [
                'main_menu_name' => 'Sale',
                'main_menu_items' => [
                    [
                        'menu_item_name' => 'Offer Group',
                        'menu_item_icon' => 'fa fa-th',
                        'menu_item_children' => [
                            [
                                'menu_child_name' => 'Create',
                                'menu_child_link' => 'http://127.0.0.1:8000/sale/offer-group/create',
                                'action' => ServerPermissions::sale_offergroup_new
                            ],
                        ]

                    ],
                    [
                        'menu_item_name' => 'Pricing Deed',
                        'menu_item_icon' => 'fa fa-th',
                        'menu_item_children' => [
                            [
                                'menu_child_name' => 'List',
                                'menu_child_link' => 'http://127.0.0.1:8000/sale/pricing-deed/list',
                                'action' => ServerPermissions::sale_pricingdeed_all
                            ],
                            [
                                'menu_child_name' => 'Create',
                                'menu_child_link' => 'http://127.0.0.1:8000/sale/pricing-deed/create',
                                'action' => ServerPermissions::sale_pricingdeed_new
                            ],
                        ]

                    ],
                    [
                        'menu_item_name' => 'Order',
                        'menu_item_icon' => 'fa fa-th',
                        'menu_item_children' => [
                            [
                                'menu_child_name' => 'List',
                                'menu_child_link' => 'http://127.0.0.1:8000/sale/order/list',
                                'action' => ServerPermissions::sale_order_all
                            ],
                            [
                                'menu_child_name' => 'Create',
                                'menu_child_link' => 'http://127.0.0.1:8000/sale/order/create',
                                'action' => ServerPermissions::sale_order_new
                            ],
                        ]

                    ],
                ]
            ],
            //Sale

            //Ticketing
            [
                'main_menu_name' => 'Ticketing',
                'main_menu_items' => [
                    [
                        'menu_item_name' => 'Comment',
                        'menu_item_icon' => 'fa fa-th',
                        'menu_item_children' => [
                            [
                                'menu_child_name' => 'Create',
                                'menu_child_link' => 'http://127.0.0.1:8000/sale/offer-group/create',
                                'action' => ServerPermissions::sale_offergroup_new
                            ],
                        ]

                    ],
                ]
            ],
            //Ticketing

            //Authentication
            [
                'main_menu_name' => 'Authentication',
                'main_menu_items' => [
                    [
                        'menu_item_name' => 'Client',
                        'menu_item_icon' => 'fa fa-th',
                        'menu_item_children' => [
                            [
                                'menu_child_name' => 'Create',
                                'menu_child_link' => 'http://127.0.0.1:8000/authentication/client/create',
                                'action' => ServerPermissions::authentication_client_new
                            ],
                        ]

                    ],
                    [
                        'menu_item_name' => 'Role',
                        'menu_item_icon' => 'fa fa-th',
                        'menu_item_children' => [
                            [
                                'menu_child_name' => 'List',
                                'menu_child_link' => 'http://127.0.0.1:8000/authentication/role/create',
                                'action' => ServerPermissions::authentication_role_all
                            ],
                        ]

                    ],
                    [
                        'menu_item_name' => 'User',
                        'menu_item_icon' => 'fa fa-th',
                        'menu_item_children' => [
                            [
                                'menu_child_name' => 'List',
                                'menu_child_link' => 'http://127.0.0.1:8000/authentication/user/list',
                                'action' => ServerPermissions::authentication_user_all
                            ],
                        ]

                    ],
                ]
            ],
            //Authentication

        ];
        $authorizedMenu = [];
        foreach ($sideMenu as $menuNumber => $menu) {
            foreach ($menu['main_menu_items'] as $sectionNumber => $sections) {
                foreach ($sections['menu_item_children'] as $childNumber => $section) {
                    if (!AuthUser::if_is_allowed($section['action'])) {
                        unset($sideMenu[$menuNumber]['main_menu_items'][$sectionNumber]['menu_item_children'][$childNumber]);
                    }
                    if (count($sideMenu[$menuNumber]['main_menu_items'][$sectionNumber]['menu_item_children']) == 0) {
                        unset($sideMenu[$menuNumber]['main_menu_items'][$sectionNumber]);
                    }
                    if (count($sideMenu[$menuNumber]['main_menu_items']) == 0) {
                        unset($sideMenu[$menuNumber]);
                    }
                }
            }
        }
        return $this->render('general/menu/side-menu.html.twig', [
            'controller_name' => 'MenuController',
            'sideMenu' => $sideMenu,
        ]);
    }
}
