<?php

return [
    'invalid_credentials' => 'Invalid credentials.',
    'inactive_user' => 'User is inactive.',
    'suspended_user' => 'User is suspended.',
    'invalid_current_password' => 'Current password is incorrect.',
    'first_login_password_already_reset' => 'First-login password reset has already been completed.',
    'forbidden_admin_assume_user' => 'You are not allowed to assume a platform user.',
    'target_user_unavailable' => 'Target user is inactive or suspended.',
    'invalid_impersonation_hash' => 'Impersonation hash is invalid, expired, or already used.',

    'custom' => [
        'email' => [
            'required' => 'Email is required.',
            'email' => 'Please enter a valid email address.',
            'exists' => 'Email not found.',
        ],
        'password' => [
            'required' => 'Password is required.',
            'min' => 'Password must be at least :min characters.',
            'confirmed' => 'Password confirmation does not match.',
        ],
        'current_password' => [
            'required' => 'Current password is required.',
        ],

        // input validation
        'preset_id' => [
            'required' => 'Preset is required.',
            'integer' => 'Preset must be a valid integer.',
            'exists' => 'Preset not found or inactive.',
        ],
        'image' => [
            'required' => 'Image is required.',
            'file' => 'Image must be a file.',
            'image' => 'Please upload a valid image.',
            'aspect_ratio_9_16' => 'Image must be in 9:16 format (vertical).',
            'aspect_ratio_expected' => 'Image must be in :ratio format.',
            'mimes' => 'Image must be a JPG, JPEG, PNG, or WEBP file.',
            'max' => 'Image must not be larger than :max KB.',
            'dimensions' => 'Image dimensions are not allowed.',
        ],

        'input_id' => [
            'required' => 'Input ID is required.',
            'integer' => 'Input ID must be a valid integer.',
            'exists' => 'Input ID not found or inactive.',
        ],
    ],
];
