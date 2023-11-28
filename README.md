
# livewire-forms

[![Latest Version on Packagist](https://img.shields.io/packagist/v/timolake/livewire-forms.svg?style=flat-square)](https://packagist.org/packages/timolake/livewire-forms)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/timolake/livewire-forms/run-tests?label=tests)](https://github.com/timolake/livewire-forms/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/timolake/livewire-forms/Check%20&%20fix%20styling?label=code%20style)](https://github.com/timolake/livewire-forms/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/timolake/livewire-forms.svg?style=flat-square)](https://packagist.org/packages/timolake/livewire-forms)

one form for creating & updating data, based on laravel livewire

## Installation

You can install the package via composer:

```bash
composer require timolake/livewire-forms

```

## How to use

1. create class in \App\Http\Livewire\Forms and extend form timolake\LivewireForm or timolake\LivewireItemForm
1. implement abstact classes \
populate rules() with all attributes wich need to be edited \
use model.attribute notation
1. create view
## validation

for model validation always use **model.attribute**
```php
    public function rules(): array
    {
        return [
            'model.name' => 'required|min:2|max:255',
        ];
    }
```
for item validation, use **model.items** and **selectedItem.attribute**

 ```php
    public function rules(): array
    {
        return [
            'items' => 'required|min:1',
        ];
    }

    public function itemRules(): array
    {
        return [
            'selectedItem.foreign_key' => 'required|numeric',
            'selectedItem.name' => 'required|min:2|max:255',
        ];    
    }
 ```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
