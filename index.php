<?php

use Kirby\Cms\App as Kirby;
use Kirby\Cms\Content;
use Kirby\Data\Data;
use Kirby\Form\Field\AccordionField;

load([
    'kirby\\form\\field\\accordionfield' => __DIR__ . '/lib/AccordionField.php'
]);

Kirby::plugin('owebstudio/kirby-accordion-field', [
    'fields' => [
        'accordion' => AccordionField::class
    ],
    'fieldMethods' => [
        'toAccordion' => function ($field, bool $arrayFields = false) {
            $data = Data::decode($field->value, 'json');
            $content = new Content($data, $field->parent());

            if ($arrayFields === true) {
                return $content->fields();
            }

            return $content;
        }
    ]
]);
