# Kirby Accordion Field

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
    pretty: true
    preview: "{{first_name}} {{last_name}}"
    fieldsets:
      step01:
        label:
          tr: Adım 01
          en: Step 01
        open: true
        headline: "{{first_name}} {{last_name}}"
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

## Field methods

### `->toAccordion()`

Assigns all fields in accordion to the object.

```php
if ($profile = $page->accordion()->toAccordion())
{
  echo $profile->first_name();
  echo $profile->last_name();
}
```

Returns all fields in accordion field as array with passing `true` param.

```php
$fields = $page->accordion()->toAccordion(true);

foreach ($fields as $field) {
  echo $field->value();
}
```

## Field properties

The default values of the package are:

| Property | Type | Default| Description |
|:--- |:---|:---|:---|
| default | array | [] | Allows assigning default values to fields |
| fieldesets | array | [] | Allows to define different form fields for each accordion |
| fieldesets.open | boolean | false | Allows it to be loaded collapsed/expanded by default |
| fieldesets.headline | string | null | Helps to add extra dynamic headline to each accordion and supports query template. |
| pretty | boolean | false | Saves pretty printed JSON in text files |
| preview | string | null | Provides preview output for structure field and supports query template |
| toggle | boolean | true | Allows only one accordion to remain open when enabled |
