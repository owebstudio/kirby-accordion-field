![Accordion Field, a Kirby plugin by oweb studio. Group fields into collapsible sections in the Panel.](.github/cover.png)

# Kirby Accordion Field

Kirby accordion field plugin collapses the content below headlines in Panel for Kirby.

![kirby-accordion-field](https://user-images.githubusercontent.com/3393422/131719194-395ec239-d88a-4866-b754-9a023dce02de.png)

## Installation

1. Download the latest release
2. Unzip downloaded file
3. Copy/paste unzipped folder in your `/site/plugins` folder

## Kirby compatibility table

| Kirby version | Compatible plugin version |
|:--------------|:--------------------------|
| ^3.0          | ^1.0                      |
| ^4.0          | ^2.0                      |
| ^5.0          | ^3.0                      |
| ^6.0          | ^4.0                      |

## Usage

```yaml
fields:
  accordion:
    label: Profile
    type: accordion
    toggle: true
    reset: true
    pretty: true
    preview: "{{first_name}} {{last_name}} - {{country}}"
    default:
      first_name: John
      last_name: Doe
      phone: "671-969-4772"
      email: john@doe.com
    fieldsets:
      step01:
        label:
          tr: Adım 01
          en: Step 01
        icon: user
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
        icon: map
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

## In the Panel

- Click a headline to open or close its fieldset. With `toggle: true`, opening a fieldset closes the others.
- The button next to the field label opens or closes all fieldsets at once.
- The Panel remembers which fieldsets are open, per field and per page, in your browser.
- Fieldsets with an invalid or missing value show a warning icon in their headline. When saving fails, they open automatically.

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

| Property            | Type    | Default | Description                                                                        |
|:--------------------|:--------|:--------|:-----------------------------------------------------------------------------------|
| default             | array   | []      | Allows assigning default values to fields                                          |
| fieldsets           | array   | []      | Allows to define different form fields for each accordion                          |
| fieldsets.label     | string  | null    | Title of the fieldset, can be translated                                           |
| fieldsets.icon      | string  | null    | Name of a Panel icon shown before the label                                        |
| fieldsets.open      | boolean | false   | Allows it to be loaded collapsed/expanded by default                               |
| fieldsets.headline  | string  | null    | Helps to add extra dynamic headline to each accordion and supports query template. |
| pretty              | boolean | false   | Saves pretty printed JSON in text files                                            |
| preview             | string  | null    | Provides preview output for structure field and supports query template            |
| reset               | boolean | false   | Restores the default values to the form if `default` prop is filled                |
| toggle              | boolean | true    | Allows only one accordion to remain open when enabled                              |

## Upgrading from 3.x

Version 4 requires Kirby 6 and PHP 8.3 or newer. Your blueprints and content stay the same. If your own code extends the field class, it moved from `Kirby\Form\Field\AccordionField` to `Oweb\AccordionField\AccordionField`.
