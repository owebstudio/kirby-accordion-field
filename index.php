<?php

use Kirby\Cms\App as Kirby;
use Kirby\Cms\Content;
use Kirby\Data\Data;
use Kirby\Form\Field\AccordionField;

load([
    'kirby\\form\\field\\accordionfield' => __DIR__ . '/src/AccordionField.php'
]);

Kirby::plugin('clicktonext/kirby-accordion-field', [
    'fields' => [
        'accordion' => AccordionField::class
    ],
    'fieldMethods' => [
        'toAccordion' => function ($field) {
            $data = Data::decode($field->value, 'json');
            return new Content($data, $field->parent());
        },
        'toFields' => function ($field) {
            return $field->toAccordion($field)->fields();
        }
    ]
]);
