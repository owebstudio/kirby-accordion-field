<?php

use Kirby\Cms\App as Kirby;
use Kirby\Content\Content;
use Kirby\Data\Data;
use Kirby\Form\Field\AccordionField;
use Oweb\AccordionField\License\License;

require_once __DIR__ . '/lib/license/autoload.php';

load([
    'kirby\\form\\field\\accordionfield' => __DIR__ . '/lib/AccordionField.php'
]);

$license = new License(
    plugin: 'owebstudio/accordion-field',
    products: [15538],
    name: 'Accordion Field',
    url: 'https://owebstudio.com/kirby/plugins/accordion-field'
);

Kirby::plugin('owebstudio/accordion-field', $license->extend([
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
]), ...$license->args());
