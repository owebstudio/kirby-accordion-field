# Kirby Accordion Field

[![Source](https://img.shields.io/badge/source-clicktonext/kirby-accordion-field-blue?style=flat-square)](https://github.com/clicktonext/kirby-accordion-field)
[![Download](https://img.shields.io/packagist/dt/clicktonext/kirby-accordion-field?style=flat-square)](https://github.com/clicktonext/kirby-accordion-field)
[![Open Issues](https://img.shields.io/github/issues-raw/clicktonext/kirby-accordion-field?style=flat-square)](https://github.com/clicktonext/kirby-accordion-field)
[![Last Commit](https://img.shields.io/github/last-commit/clicktonext/kirby-accordion-field?style=flat-square)](https://github.com/clicktonext/kirby-accordion-field)
[![Release](https://img.shields.io/github/v/release/clicktonext/kirby-accordion-field?style=flat-square)](https://github.com/clicktonext/kirby-accordion-field)
[![License](https://img.shields.io/github/license/clicktonext/kirby-accordion-field?style=flat-square)](https://github.com/clicktonext/kirby-accordion-field)

Kirby accordion field plugin collapses the content below headlines in Panel for Kirby 3.

![kirby-accordion-field](https://user-images.githubusercontent.com/3393422/131719194-395ec239-d88a-4866-b754-9a023dce02de.png)

## Installation

### Installation with composer

```ssh
composer require clicktonext/kirby-accordion-field
```

### Add as git submodule

```ssh
git submodule add https://github.com/clicktonext/kirby-accordion-field.git site/plugins/kirby-accordion-field
```

## Usage

```yaml
fields:
  accordion:
    label: Profile
    type: accordion
    toggle: true
    pretty: false
    fieldsets:
      step01:
        label:
          tr: Adım 01
          en: Step 01
        open: true
        fields:
          first_name:
            label: First name
            type: text
            required: true
            autofocus: true
            width: 1/2

          last_name:
            label: Last name
            type: text
            required: true
            width: 1/2

          phone:
            label: Phone
            type: text
            width: 1/2

          email:
            label: E-Mail
            type: email
            width: 1/2
            required: true

      step02:
        label: Step 02
        fields:
          address:
            label: Address
            type: textarea
          country:
            label: Country
            type: text
            width: 1/2
          zip:
            label: ZIP
            type: text
            width: 1/2

      step03:
        label: Step 03
        fields:
          available:
            label: Available for hire
            type: toggle
            width: 1/3
            text:
              - "no"
              - "yes"

          expertise:
            label: Expertise
            type: multiselect
            width: 2/3
            options:
              php: PHP
              kirby: Kirby
              sql: SQL
              vue: Vue
              html: HTML
```

## Methods

### `->toAccordion()`

Assigns all fields in accordion to the object.

```php

$profile = $page->accordion()->toAccordion();
echo $profile->first_name();

```

### `->toFields()`

Returns all fields in accordion field as array.

```php
$fields = $page->accordion()->toFields();

foreach ($fields as $field) {
  echo $field->value();
}
```

## Field properties

The default values of the package are:

| Property | Type | Default| Description |
|:--- |:---|:---|:---|
| fieldesets | array | [] | Allows to define different form fields for each accordion |
| fieldesets.field.open | boolean | false | Allows it to be loaded collapsed/expanded by default |
| pretty | boolean | false | Saves pretty printed JSON in text files |
| toggle | boolean | true | Allows only one accordion to remain open when enabled |

All the values can be updated in the `config.php` file.
