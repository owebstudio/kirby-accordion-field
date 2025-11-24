<?php

namespace Kirby\Form\Field;

use Kirby\Cms\App as Kirby;
use Kirby\Cms\Blueprint;
use Kirby\Data\Json;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\FieldClass;
use Kirby\Form\Form;
use Kirby\Toolkit\A;
use Kirby\Toolkit\I18n;
use Throwable;

class AccordionField extends FieldClass
{
    protected array $fields = [];
    protected array $fieldsets;
    protected static array $forms = [];
    protected bool $pretty;
    protected string|null $preview;
    protected bool $reset;
    protected bool $toggle;
    protected mixed $value = [];

    public function __construct(array $params = [])
    {
        $this->setModel($params['model'] ?? Kirby::instance()->site());
        $this->setFieldsets($params['fieldsets'] ?? []);
        $this->setPretty($params['pretty'] ?? false);
        $this->setPreview($params['preview'] ?? null);
        $this->setReset($params['reset'] ?? false);
        $this->setToggle($params['toggle'] ?? true);

        parent::__construct($params);
    }

    public function default(): mixed
    {
        $default = [];
        $fields = $this->fields();

        // get fields default values
        foreach ($fields as $key => $field) {
            if (empty($field['default']) === false) {
                $default[strtolower($key)] = $field['default'];
            }
        }

        // extend default prop
        if (is_string($this->default) === false) {
            $defaultProp = $this->default;
        } else {
            if ($extend = Blueprint::extend($this->default)) {
                $defaultProp = $extend;
            } else {
                $defaultProp = $this->stringTemplate($this->default);
            }
        }

        if (empty($defaultProp) === true || is_array($defaultProp) === false) {
            $defaultProp = [];
        }

        // merge fields own defaults with accordion default
        $default = array_replace($default, $defaultProp);

        return array_change_key_case($default, CASE_LOWER);
    }

    public function fields(string $fieldset = null): array
    {
        if (empty($fieldset) === false) {
            return $this->fieldsets[$fieldset]['fields'] ?? [];
        }

        return $this->fields;
    }

    public function fieldsets(): array
    {
        return $this->fieldsets;
    }

    public function form(array $fields = null): Form
    {
        $fields ??= $this->fields();

        // hash fields and input to cache
        // todo: improve this cache and form instance after core form refactoring
        $hash = md5(serialize($fields));

        // cache the form instance
        return static::$forms[$hash] ??= new Form(
            fields: $fields,
            model: $this->model()
        );
    }

    public function fill(mixed $value): static
    {
        try {
            $this->value = $value === null ? $this->default() : Json::decode($value);
        } catch (Throwable) {
            $this->value = [];
        }

        return $this;
    }

    public function pretty(): bool
    {
        return $this->pretty;
    }

    public function preview(): ?string
    {
        return $this->preview;
    }

    public function reset(): bool
    {
        return $this->reset;
    }

    public function toggle(): bool
    {
        return $this->toggle;
    }

    public function props(): array
    {
        return array_merge([
            'fieldsets' => $this->fieldsets(),
            'pretty' => $this->pretty(),
            'preview' => $this->preview(),
            'reset' => $this->reset(),
            'toggle' => $this->toggle(),
        ], parent::props());
    }

    protected function setFieldsets(array $fieldsets): void
    {
        foreach ($fieldsets as $key => &$fieldset) {
            $fieldset = Blueprint::extend($fieldset);

            // extends fieldsets and fields
            $fields = Blueprint::fieldsProps($fieldset['fields'] ?? []);
            $this->fields = array_replace_recursive($this->fields, $fields);
            $fields = $this->form($fields)->fields();

            $fieldset['name'] = $key;
            $fieldset['label'] = I18n::translate($fieldset['label'], $fieldset['label']);
            $fieldset['fields'] = $fields->toArray();
            $fieldset['headline'] = $fieldset['headline'] ?? null;
            $fieldset['open'] = $fieldset['open'] ?? false;
        }

        $this->fieldsets = $fieldsets;
    }

    protected function setPretty(bool $pretty = false): void
    {
        $this->pretty = $pretty;
    }

    protected function setPreview(string $preview = null): void
    {
        $this->preview = $preview;
    }

    protected function setReset(bool $reset = false): void
    {
        $this->reset = $reset;
    }

    protected function setToggle(bool $toggle = true): void
    {
        $this->toggle = $toggle;
    }

    public function toFormValue(): mixed
    {
        return $this
            ->form()
            ->reset()
            ->fill(input: parent::toFormValue() ?? [])
            ->fields()
            ->toFormValues();
    }

    public function toStoredValue(): mixed
    {
        $value = $this
            ->form()
            ->reset()
            ->submit(input: parent::toStoredValue())
            ->toStoredValues();

        return Json::encode($value, pretty: $this->pretty());
    }

    public function validations(): array
    {
        return [
            'accordion' => function ($value) {
                $errors = [];

                foreach ($this->fieldsets() as $fieldset) {
                    $fields = $this->form($fieldset['fields'] ?? [])->fields()->reset()->fill(input: $value);

                    foreach ($fields as $field) {
                        $fieldErrors = $field->errors();

                        if (empty($fieldErrors) === false) {
                            foreach ($fieldErrors as $error) {
                                $errors[] = $error . ' (' . $fieldset['label'] . ' > ' . $field->label() . ')';
                            }
                        }
                    }
                }

                if (empty($errors) === false) {
                    throw new InvalidArgumentException(implode("\n", array_values($errors)));
                }

                return true;
            }
        ];
    }
}
