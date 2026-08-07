<?php

return [

    'whatsapp_number' => env('NATURECARE_WHATSAPP_NUMBER', '919999999999'),

    'meta_pixel_id' => env('NATURECARE_META_PIXEL_ID', ''),

    'admin_notification_email' => env('MAIL_ADMIN_ADDRESS', 'online@viviz.in'),

    'investment_ranges' => [
        'below_1_lakh' => 'Below ₹1 Lakh',
        '1_to_5_lakh' => '₹1 Lakh - ₹5 Lakh',
        '5_to_10_lakh' => '₹5 Lakh - ₹10 Lakh',
        '10_to_25_lakh' => '₹10 Lakh - ₹25 Lakh',
        'above_25_lakh' => 'Above ₹25 Lakh',
    ],

    'years_in_business_ranges' => [
        'less_than_1' => 'Less than 1 year',
        '1_to_3' => '1 - 3 years',
        '3_to_5' => '3 - 5 years',
        '5_to_10' => '5 - 10 years',
        'more_than_10' => 'More than 10 years',
    ],

    'business_types' => [
        'kirana_general_store' => 'Kirana / General Store',
        'supermarket' => 'Supermarket',
        'wholesale_trading' => 'Wholesale Trading',
        'cosmetics_store' => 'Cosmetics / Personal Care Store',
        'fmcg_distribution' => 'FMCG Distribution',
        'other' => 'Other',
    ],

    'expected_response_hours' => 24,

    'indian_states' => [
        'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chhattisgarh', 'Goa', 'Gujarat',
        'Haryana', 'Himachal Pradesh', 'Jharkhand', 'Karnataka', 'Kerala', 'Madhya Pradesh',
        'Maharashtra', 'Manipur', 'Meghalaya', 'Mizoram', 'Nagaland', 'Odisha', 'Punjab',
        'Rajasthan', 'Sikkim', 'Tamil Nadu', 'Telangana', 'Tripura', 'Uttar Pradesh',
        'Uttarakhand', 'West Bengal', 'Delhi', 'Jammu and Kashmir', 'Ladakh', 'Puducherry',
        'Chandigarh', 'Andaman and Nicobar Islands', 'Dadra and Nagar Haveli and Daman and Diu',
        'Lakshadweep',
    ],

];
