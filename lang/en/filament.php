<?php

return [
    'navigation' => [
        'groups' => [
            'users' => 'Users',
            'content' => 'Content',
            'finance' => 'Finance',
        ],
        'roles' => 'Roles',
    ],

    'common' => [
        'yes' => 'Yes',
        'no' => 'No',
        'anonymous' => 'Anonymous',
        'close' => 'Close',
    ],

    'options' => [
        'role' => [
            'admin' => 'Admin',
            'member' => 'Member',
        ],
        'donation_category' => [
            'zakat' => 'Zakat',
            'infak' => 'Infaq',
            'sedekah' => 'Sadaqah',
        ],
        'donation_payment_type' => [
            'maal' => 'Zakat Maal',
            'fitrah' => 'Zakat Fitrah',
            'profesi' => 'Zakat Profession',
            'kemanusiaan' => 'Humanitarian Infaq',
            'infak_umum' => 'General Infaq',
            'jariyah' => 'Sadaqah Jariyah',
            'sedekah_umum' => 'General Sadaqah',
            'umum' => 'General',
        ],
        'donation_status' => [
            'pending' => 'Pending',
            'verified' => 'Verified',
            'rejected' => 'Rejected',
            'confirmed' => 'Confirmed',
        ],
        'member_prayer_status' => [
            'published' => 'Published',
            'hidden' => 'Hidden',
        ],
        'payment_method_type' => [
            'bank' => 'Bank Transfer',
            'qris' => 'QRIS',
            'ewallet' => 'E-Wallet',
        ],
    ],

    'resources' => [
        'articles' => [
            'model_label' => 'Article',
            'plural_model_label' => 'Articles',
            'navigation_label' => 'Articles',
            'sections' => [
                'information' => 'Article Information',
                'content' => 'Article Content',
            ],
            'descriptions' => [
                'information' => 'Article metadata for URL and content list display.',
            ],
            'fields' => [
                'title' => 'Title',
                'slug' => 'Slug',
                'thumbnail' => 'Thumbnail',
                'is_published' => 'Publish Article',
                'content' => 'Content',
                'created_at' => 'Created At',
            ],
            'placeholders' => [
                'title' => 'Enter article title',
                'slug' => 'article-slug',
            ],
            'filters' => [
                'publication' => 'Publication',
                'published' => 'Published',
                'draft' => 'Draft',
            ],
        ],

        'donations' => [
            'model_label' => 'Donation',
            'plural_model_label' => 'Donations',
            'navigation_label' => 'Donations',
            'sections' => [
                'donor_data' => 'Donor Data',
                'transaction_details' => 'Transaction Details',
                'program_context' => 'Program & Calculator Context',
                'admin_notes' => 'Admin Notes',
            ],
            'descriptions' => [
                'donor_data' => 'Fill in registered or guest donor data.',
                'transaction_details' => 'Core donation transaction and verification information.',
                'program_context' => 'Used for contextual infaq/sadaqah donations and zakat calculator details.',
            ],
            'fields' => [
                'member' => 'Member',
                'registered_user' => 'Registered User',
                'guest_token' => 'Guest Token',
                'donor_name' => 'Donor Name',
                'donor_phone' => 'Donor Phone Number',
                'donor_email' => 'Donor Email',
                'payment_method' => 'Payment Method',
                'category' => 'Category',
                'payment_type' => 'Donation Type',
                'amount' => 'Amount',
                'proof_image' => 'Transfer Proof',
                'status' => 'Status',
                'context_label' => 'Donation Context',
                'context_slug' => 'Context Slug',
                'calculator_type' => 'Calculator Type',
                'intention_note' => 'Intention Note',
                'calculator_breakdown' => 'Calculator Breakdown (JSON)',
                'admin_note' => 'Internal Note',
                'created_at' => 'Created At',
                'updated_at' => 'Updated At',
            ],
            'placeholders' => [
                'guest_token' => 'Guest donor token',
                'payment_type' => 'Select donation type',
                'member_guest' => 'Guest',
            ],
            'helper_text' => [
                'calculator_breakdown' => 'Use a valid JSON format. Example: {"nisab": 123, "wajib": true}',
            ],
            'filters' => [
                'category' => 'Category',
                'payment_type' => 'Donation Type',
                'status' => 'Status',
                'payment_method' => 'Payment Method',
            ],
            'columns' => [
                'view_proof' => 'View Proof',
                'no_proof' => 'No Proof Uploaded',
                'proof_unavailable' => 'Transfer proof was not found or is not accessible.',
            ],
            'actions' => [
                'approve' => 'Approve',
                'reject' => 'Reject',
                'approve_success' => 'Donation has been approved.',
                'reject_success' => 'Donation has been rejected.',
            ],
        ],

        'donation_categories' => [
            'model_label' => 'Donation Category',
            'plural_model_label' => 'Donation Categories',
            'navigation_label' => 'Donation Categories',
            'sections' => [
                'information' => 'Donation Category Information',
                'advanced' => 'Technical Settings (Optional)',
            ],
            'descriptions' => [
                'information' => 'Set the category name first, then enable or disable it for the app display.',
                'advanced' => 'Use this section for system settings such as code and display order.',
            ],
            'fields' => [
                'key' => 'System Code',
                'label' => 'Category Name',
                'description' => 'Description',
                'requires_context' => 'Context Required',
                'payment_types_count' => 'Type Count',
                'sort_order' => 'Sort Order',
                'is_active' => 'Active',
                'is_locked' => 'System Default',
                'updated_at' => 'Updated At',
            ],
            'placeholders' => [
                'key' => 'Example: zakat, infaq, sadaqah',
                'label' => 'Example: Zakat',
            ],
            'helper_text' => [
                'label' => 'This name is displayed in the donation app.',
                'key' => 'Optional. If left empty, the system code is auto-generated from the category name.',
                'requires_context' => 'Enable this if the category must choose a context/program during donation.',
                'sort_order' => 'Lower numbers are shown first.',
            ],
            'filters' => [
                'active_status' => 'Active Status',
                'active' => 'Active',
                'inactive' => 'Inactive',
            ],
            'actions' => [
                'add_payment_type' => 'Add Donation Type',
            ],
            'relations' => [
                'payment_types' => 'Donation Types',
            ],
        ],

        'donation_payment_types' => [
            'model_label' => 'Donation Type',
            'plural_model_label' => 'Donation Types',
            'navigation_label' => 'Donation Types',
            'sections' => [
                'information' => 'Donation Type Information',
                'rules' => 'Additional Rules (Optional)',
                'advanced' => 'Technical Settings (Optional)',
            ],
            'descriptions' => [
                'information' => 'Select a category, then enter the donation type name shown to donors.',
                'rules' => 'Configure zakat calculator usage and special conditions when needed.',
                'advanced' => 'Use this section for system code and display order.',
            ],
            'fields' => [
                'category' => 'Category',
                'key' => 'System Code',
                'label' => 'Type Name',
                'description' => 'Description',
                'is_zakat_calculator' => 'Included in Zakat Calculator',
                'min_amount' => 'Minimum Amount',
                'max_amount' => 'Maximum Amount',
                'require_context' => 'Require Program Context',
                'require_intention_note' => 'Require Intention Note',
                'conditions' => 'Additional Conditions',
                'condition_name' => 'Condition Name',
                'condition_value' => 'Condition Value',
                'sort_order' => 'Sort Order',
                'is_active' => 'Active',
                'is_locked' => 'System Default',
                'updated_at' => 'Updated At',
            ],
            'placeholders' => [
                'key' => 'Example: maal, fitrah, general',
                'label' => 'Example: Zakat Maal',
                'condition_name' => 'Example: minimum_amount',
                'condition_value' => 'Example: 50000',
            ],
            'helper_text' => [
                'category' => 'Select which category this donation type belongs to.',
                'label' => 'This name is shown as donation type option in the app.',
                'key' => 'Optional. If left empty, the system code is auto-generated from the type name.',
                'is_zakat_calculator' => 'Enable only for zakat-related types that use the calculator.',
                'min_amount' => 'Optional. Set minimum allowed amount for this zakat type.',
                'max_amount' => 'Optional. Set maximum allowed amount for this zakat type.',
                'conditions' => 'Optional. Add key/value pairs for special rules (for example minimum_amount: 50000).',
                'sort_order' => 'Lower numbers are shown first.',
            ],
            'filters' => [
                'category' => 'Category',
                'active_status' => 'Active Status',
                'active' => 'Active',
                'inactive' => 'Inactive',
                'zakat_calculator' => 'Zakat Calculator',
            ],
            'actions' => [
                'add_condition' => 'Add Condition',
            ],
        ],

        'member_prayers' => [
            'model_label' => 'Member Prayer',
            'plural_model_label' => 'Member Prayers',
            'navigation_label' => 'Member Prayers',
            'sections' => [
                'main' => 'Member Prayer',
            ],
            'descriptions' => [
                'main' => 'Manage prayer content and visibility in the app.',
            ],
            'fields' => [
                'user' => 'User',
                'visibility_status' => 'Visibility Status',
                'status' => 'Status',
                'is_anonymous' => 'Show as anonymous',
                'content' => 'Prayer Content',
                'created_at' => 'Created At',
                'updated_at' => 'Updated At',
            ],
            'filters' => [
                'status' => 'Status',
                'anonymous' => 'Anonymous',
                'not_anonymous' => 'Not Anonymous',
            ],
        ],

        'payment_methods' => [
            'model_label' => 'Payment Method',
            'plural_model_label' => 'Payment Methods',
            'navigation_label' => 'Payment Methods',
            'sections' => [
                'information' => 'Payment Method Information',
                'qris_configuration' => 'QRIS Configuration',
            ],
            'descriptions' => [
                'information' => 'Main payment method data displayed in the app.',
                'qris_configuration' => 'Required when method type is QRIS.',
            ],
            'fields' => [
                'name' => 'Method Name',
                'type' => 'Method Type',
                'account_number' => 'Account Number / Bank Account / NMID',
                'account_holder' => 'Account Holder Name',
                'is_active' => 'Active',
                'logo' => 'Method Logo',
                'qris_image' => 'Upload QRIS Image',
                'qris_static_payload' => 'QRIS Static Payload (EMV)',
                'qris_template' => 'QRIS Template',
                'qris_image_indicator' => 'QRIS Image',
                'created_at' => 'Created At',
                'updated_at' => 'Updated At',
            ],
            'placeholders' => [
                'name' => 'Example: BSI Bank / QRIS',
                'account_number' => 'Example: 1234567890',
                'account_holder' => 'Example: Baitul Maal LAZ',
            ],
            'helper_text' => [
                'qris_image' => 'Upload a QRIS image. The EMV payload will be filled automatically.',
                'qris_static_payload' => 'Can be auto-filled from QRIS image upload, or entered manually.',
            ],
            'filters' => [
                'type' => 'Type',
                'active_status' => 'Active Status',
                'active' => 'Active',
                'inactive' => 'Inactive',
            ],
        ],

        'users' => [
            'model_label' => 'Member',
            'plural_model_label' => 'Members',
            'navigation_label' => 'Members',
            'sections' => [
                'account_information' => 'Account Information',
                'profile' => 'Profile',
            ],
            'descriptions' => [
                'account_information' => 'Main user account data for authentication and authorization.',
                'profile' => 'Additional information shown in the app.',
            ],
            'fields' => [
                'name' => 'Full Name',
                'email' => 'Email Address',
                'password' => 'Password',
                'role' => 'Role',
                'phone' => 'Phone Number',
                'avatar' => 'Profile Photo',
            ],
            'placeholders' => [
                'name' => 'Example: Ahmad Fauzi',
                'email' => 'example@email.com',
                'password' => 'Leave blank if unchanged',
                'phone' => '08xxxxxxxxxx',
            ],
            'filters' => [
                'role' => 'Role',
            ],
        ],

        'videos' => [
            'model_label' => 'Study Video',
            'plural_model_label' => 'Study Videos',
            'navigation_label' => 'Study Videos',
            'sections' => [
                'youtube_source' => 'YouTube Source',
                'content_and_publication' => 'Content & Publication',
            ],
            'descriptions' => [
                'youtube_source' => 'Paste a video link to auto-fill metadata.',
            ],
            'fields' => [
                'youtube_link' => 'YouTube Link',
                'title' => 'Video Title',
                'thumbnail_url' => 'Thumbnail URL',
                'description' => 'Description',
                'is_published' => 'Show in App',
            ],
            'placeholders' => [
                'youtube_link' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'title' => 'Video content title',
                'thumbnail_url' => 'https://...',
            ],
            'helper_text' => [
                'youtube_link' => 'Title, description, and thumbnail will be filled automatically after this field is completed.',
                'thumbnail_url' => 'Can be auto-filled from YouTube, or entered manually.',
            ],
            'filters' => [
                'publication' => 'Publication',
                'published' => 'Published',
                'draft' => 'Draft',
            ],
        ],
    ],

    'widgets' => [
        'recent_donations' => [
            'heading' => 'Recent Donations',
            'columns' => [
                'date' => 'Date',
                'donor' => 'Donor',
                'category' => 'Category',
                'amount' => 'Amount',
                'payment_method' => 'Payment Method',
                'status' => 'Status',
            ],
        ],
        'donation_stats' => [
            'total_donations' => 'Total Donations',
            'total_donations_description' => 'Total collected donations',
            'today_donations' => 'Today\'s Donations',
            'today_donations_description' => 'Donations received today',
            'pending_confirmation' => 'Pending Confirmation',
            'pending_confirmation_description' => 'Pending donations',
            'confirmed' => 'Confirmed',
            'confirmed_description' => 'Verified donations',
        ],
        'content_stats' => [
            'total_members' => 'Total Members',
            'registered_members' => 'Registered members',
            'articles' => 'Articles',
            'videos' => 'Videos',
            'member_prayers' => 'Member Prayers',
            'published_count' => ':count published',
        ],
        'zakat_stats' => [
            'fitrah' => 'Zakat Fitrah',
            'fitrah_description' => 'Total zakat fitrah',
            'maal' => 'Zakat Maal',
            'maal_description' => 'Total zakat maal',
            'profession' => 'Zakat Profession',
            'profession_description' => 'Total zakat profession',
        ],
        'donation_category_chart' => [
            'heading' => 'Donation Distribution by Category',
            'total_label' => 'Total (IDR)',
        ],
        'donation_chart' => [
            'heading' => 'Donation Trend (Last 7 Days)',
            'total_label' => 'Total Donations (IDR)',
        ],
    ],

    'notifications' => [
        'invalid_qris' => 'Invalid QRIS',
        'invalid_youtube_link' => 'Invalid YouTube link.',
    ],

    'exports' => [
        'articles' => [
            'columns' => [
                'id' => 'ID',
                'title' => 'Title',
                'slug' => 'Slug',
                'is_published' => 'Published',
                'published_at' => 'Published At',
                'created_at' => 'Created At',
                'content' => 'Content',
            ],
        ],
        'donations' => [
            'columns' => [
                'id' => 'Donation ID',
                'member' => 'Member',
                'donor_name' => 'Donor Name',
                'donor_phone' => 'Donor Phone',
                'donor_email' => 'Donor Email',
                'category' => 'Category',
                'payment_type' => 'Donation Type',
                'program' => 'Program',
                'payment_method' => 'Payment Method',
                'amount' => 'Amount',
                'status' => 'Status',
                'admin_note' => 'Admin Note',
                'created_at' => 'Donation Date',
            ],
        ],
        'member_prayers' => [
            'columns' => [
                'id' => 'ID',
                'member' => 'Member',
                'content' => 'Prayer Content',
                'is_anonymous' => 'Anonymous',
                'likes_count' => 'Amen Count',
                'status' => 'Status',
                'created_at' => 'Created At',
            ],
        ],
        'payment_methods' => [
            'columns' => [
                'id' => 'ID',
                'name' => 'Name',
                'type' => 'Type',
                'account_number' => 'Account Number',
                'account_holder' => 'Account Holder',
                'is_active' => 'Active',
                'qris_static_payload' => 'QRIS Payload',
                'qris_image' => 'QRIS Image',
                'created_at' => 'Created At',
            ],
        ],
        'users' => [
            'columns' => [
                'id' => 'ID',
                'name' => 'Name',
                'email' => 'Email',
                'role' => 'Role',
                'phone' => 'Phone',
                'email_verified_at' => 'Email Verified At',
                'created_at' => 'Registration Date',
            ],
        ],
        'videos' => [
            'columns' => [
                'id' => 'ID',
                'title' => 'Title',
                'youtube_id' => 'YouTube ID',
                'description' => 'Description',
                'is_published' => 'Published',
                'created_at' => 'Created At',
            ],
        ],
    ],
];
