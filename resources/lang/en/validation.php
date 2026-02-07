<?php

return [
    'invalid_credentials' => 'Invalid credentials.',
    'inactive_user' => 'User is inactive.',

    'custom' => [
        'email' => [
            'required' => 'Email is required.',
            'email' => 'Please enter a valid email address.',
            'exists' => 'Email not found.',
        ],
        'password' => [
            'required' => 'Password is required.',
            'min' => 'Password must be at least :min characters.',
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
            'aspect_ratio_9_16' => 'A imagem deve estar no formato 9:16 (vertical).',
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
