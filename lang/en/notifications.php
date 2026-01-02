<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Influencer Invitation Notification
    |--------------------------------------------------------------------------
    */
    'influencer_invitation' => [
        'subject' => 'Welcome to Flower Violet as a Partner!',
        'greeting' => 'Hello :name! 🎉',
        'intro' => 'Congratulations! You have been accepted as an influencer and partner at Flower Violet.',
        'login_details' => 'Login Details',
        'email_label' => 'Email',
        'password_label' => 'Password',
        'coupon_section' => 'Your Discount Code',
        'your_code' => 'Code',
        'login_button' => 'Login to Partners Portal',
        'change_password_note' => 'We recommend changing your password after first login.',
        'salutation' => 'Best regards, Flower Violet Team',
        'db_message' => 'Your discount code has been created: :code',
    ],

    /*
    |--------------------------------------------------------------------------
    | Application Status Notifications
    |--------------------------------------------------------------------------
    */
    'application_approved' => [
        'subject' => 'Your Influencer Application Approved!',
        'greeting' => 'Hello :name!',
        'message' => 'We are happy to inform you that your application to join as an influencer at Flower Violet has been approved.',
        'login_button' => 'Login',
        'salutation' => 'Best regards, Flower Violet Team',
    ],

    'application_rejected' => [
        'subject' => 'Regarding Your Influencer Application',
        'greeting' => 'Hello :name!',
        'message' => 'Thank you for your interest in joining as an influencer at Flower Violet. Unfortunately, we were unable to accept your application at this time.',
        'salutation' => 'Best regards, Flower Violet Team',
    ],
];
