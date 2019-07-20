<?php

namespace App\Controller\General;

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
                            ],
                            [
                                'menu_child_name' => 'Create',
                                'menu_child_link' => 'http://127.0.0.1:8000/accounting/coupon-group/create',
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
                            ],
                            [
                                'menu_child_name' => 'Create',
                                'menu_child_link' => 'http://127.0.0.1:8000/accounting/gift-card-group/create',
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
                            ],
                            [
                                'menu_child_name' => 'Create',
                                'menu_child_link' => 'http://127.0.0.1:8000/accounting/invoice/create',
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
                            ],
                            [
                                'menu_child_name' => 'Create',
                                'menu_child_link' => 'http://127.0.0.1:8000/delivery/delivery-method/create',
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
                            ],
                            [
                                'menu_child_name' => 'Create',
                                'menu_child_link' => 'http://127.0.0.1:8000/delivery/dispatch/create',
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
                            ],
                            [
                                'menu_child_name' => 'Create',
                                'menu_child_link' => 'http://127.0.0.1:8000/inventory/create',
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
                            ],
                            [
                                'menu_child_name' => 'Create',
                                'menu_child_link' => 'http://127.0.0.1:8000/inventory/shelve/create',
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
                            ],
                            [
                                'menu_child_name' => 'Create',
                                'menu_child_link' => 'http://127.0.0.1:8000/inventory/deed/create',
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
                            ],
                            [
                                'menu_child_name' => 'Create',
                                'menu_child_link' => 'http://127.0.0.1:8000/repository/company/create',
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
                            ],
                            [
                                'menu_child_name' => 'Create',
                                'menu_child_link' => 'http://127.0.0.1:8000/repository/person/create',
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
                            ],
                            [
                                'menu_child_name' => 'Create',
                                'menu_child_link' => 'http://127.0.0.1:8000/repository/item/create',
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
                            ],
                            [
                                'menu_child_name' => 'Create',
                                'menu_child_link' => 'http://127.0.0.1:8000/sale/pricing-deed/create',
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
                            ],
                            [
                                'menu_child_name' => 'Create',
                                'menu_child_link' => 'http://127.0.0.1:8000/sale/order/create',
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
                            ],
                        ]

                    ],
                ]
            ],
            //Ticketing

        ];

//        dd($sideMenu);
        return $this->render('general/menu/side-menu.html.twig', [
            'controller_name' => 'MenuController',
            'sideMenu' => $sideMenu,
        ]);
    }
}
