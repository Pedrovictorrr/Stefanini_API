<?php

return [
    'navigation' => [
        'group' => 'Sistema',
        'label' => 'Logs',
    ],

    'page' => [
        'title' => 'Logs',

        'form' => [
            'placeholder' => 'Selecione ou pesquise um arquivo de log...',
        ],
    ],

    'actions' => [
        'clear' => [
            'label' => 'Limpar',

            'modal' => [
                'heading' => 'Limpar logs do site?',
                'description' => 'Tem certeza de que deseja limpar todos os logs do site?',

                'actions' => [
                    'confirm' => 'Limpar',
                ],
            ],
        ],

        'jumpToStart' => [
            'label' => 'Ir para o início',
        ],

        'jumpToEnd' => [
            'label' => 'Ir para o fim',
        ],

        'refresh' => [
            'label' => 'Atualizar',
        ],
    ],
];
