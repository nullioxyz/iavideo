<?php

return [
    'invalid_credentials' => 'Credenciais inválidas.',
    'inactive_user' => 'Usuário inativo.',
    'suspended_user' => 'Usuário suspenso.',
    'invalid_current_password' => 'A senha atual está incorreta.',
    'first_login_password_already_reset' => 'A redefinição de senha de primeiro login já foi concluída.',
    'forbidden_admin_assume_user' => 'Você não tem permissão para assumir um usuário da plataforma.',
    'target_user_unavailable' => 'O usuário alvo está inativo ou suspenso.',
    'invalid_impersonation_hash' => 'Hash de impersonação inválida, expirada ou já utilizada.',

    'custom' => [
        'email' => [
            'required' => 'Informe o e-mail.',
            'email' => 'Informe um e-mail válido.',
            'exists' => 'E-mail não encontrado.',
        ],
        'password' => [
            'required' => 'Informe a senha.',
            'min' => 'A senha deve ter no mínimo :min caracteres.',
            'confirmed' => 'A confirmação da senha não confere.',
        ],
        'current_password' => [
            'required' => 'Informe a senha atual.',
        ],

        // inputs validation
        'preset_id' => [
            'required' => 'O preset é obrigatório.',
            'integer' => 'O preset deve ser um número inteiro válido.',
            'exists' => 'Preset não encontrado ou inativo.',
        ],
        'image' => [
            'required' => 'A imagem é obrigatória.',
            'file' => 'A imagem deve ser um arquivo.',
            'image' => 'Envie uma imagem válida.',
            'aspect_ratio_9_16' => 'A imagem deve estar no formato 9:16 (vertical).',
            'aspect_ratio_expected' => 'A imagem deve estar no formato :ratio.',
            'mimes' => 'A imagem deve ser JPG, JPEG, PNG ou WEBP.',
            'max' => 'A imagem não pode ter mais que :max KB.',
            'dimensions' => 'As dimensões da imagem não são permitidas.',
        ],

        'input_id' => [
            'required' => 'O ID de entrada é obrigatório.',
            'integer' => 'O ID de entrada deve ser um número inteiro válido.',
            'exists' => 'O ID de entrada não foi encontrado ou está inativo.',
        ],
    ],
];
