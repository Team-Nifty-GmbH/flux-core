<?php

return [
    /*
     * You can define the default layout that will be used for printing.
     * The layout must extend the App\View\Layouts\Printing class.
     * This setting can be overridden
     *
     * you can use a name as key and a class as value
     * if you dont use a name, the class name will be used as key
     */
    'views' => [
        \FluxErp\Models\Order::class => [
            'invoice' => \FluxErp\View\Printing\Order\Invoice::class,
            'offer' => \FluxErp\View\Printing\Order\Offer::class,
            'retoure' => \FluxErp\View\Printing\Order\Retoure::class,
        ],
    ],

    /*
     * You can define the default layout that will be used to wrap the printing view.
     */
    'layout' => \FluexErp\View\Layouts\Printing::class,
];
