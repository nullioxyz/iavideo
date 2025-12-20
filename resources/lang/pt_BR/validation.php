<?php

return [
    'invalid_credentials' => 'Credenciais inválidas.',
    'inactive_user' => 'Usuário inativo.',

    'custom' => [
        'email' => [
            'required' => 'Informe o e-mail.',
            'email' => 'Informe um e-mail válido.',
            'exists' => 'E-mail não encontrado.',
        ],
        'password' => [
            'required' => 'Informe a senha.',
            'min' => 'A senha deve ter no mínimo :min caracteres.',
        ],

        // inputs validation
        'preset_id' => [
            'required' => 'O preset é obrigatório.',
            'integer'  => 'O preset deve ser um número inteiro válido.',
            'exists'   => 'Preset não encontrado ou inativo.',
        ],
        'image' => [
            'required'   => 'A imagem é obrigatória.',
            'file'       => 'A imagem deve ser um arquivo.',
            'image'      => 'Envie uma imagem válida.',
            'aspect_ratio_9_16' => 'A imagem deve estar no formato 9:16 (vertical).',
            'mimes'      => 'A imagem deve ser JPG, JPEG, PNG ou WEBP.',
            'max'        => 'A imagem não pode ter mais que :max KB.',
            'dimensions' => 'As dimensões da imagem não são permitidas.',
        ],
    ],
];
