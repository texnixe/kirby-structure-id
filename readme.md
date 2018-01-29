# Kirby Structure ID Plugin

This plugin automatically hooks into Kirby to add unique hashes to structure field items. It supports multiple structure fields.


## Installation

### Download

[Download the files](https://github.com/texnixe/kirby-structure-id/archive/master.zip) and place them inside site/plugins/kirby-structure-id.


### Kirby CLI
Installing via Kirby's [command line interface](https://github.com/getkirby/cli):

    $ kirby plugin:install texnixe/kirby-structure-id

To update the plugin, run:

    $ kirby plugin:update texnixe/kirby-structure-id

### Git Submodule
You can add the Logger plugin as a Git submodule.

    $ cd your/project/root
    $ git submodule add https://github.com/texnixe/kirby-structure-id.git site/plugins/kirby-structure-id
    $ git submodule update --init --recursive
    $ git commit -am "Add Kirby Structure ID plugin"

Run these commands to update the plugin:

    $ cd your/project/root
    $ git submodule foreach git checkout master
    $ git submodule foreach git pull
    $ git commit -am "Update submodules"
    $ git submodule update --init --recursive

## Usage

Add a HashID field to your structure field and make it readonly:

```php
structurefield:
  label: My awesome structure field
  type: structure
  fields:
    hash_id:
      label: ID
      type: text
      placeholder: Will be filled automatically when the page is saved.  
      readonly: true
    somefield:
      label: Something
      type: textarea
```

Instead of a field of type text, you could also use a [`hidden` field](https://getkirby.com/docs/cheatsheet/panel-fields/hidden) if you want to hide this field completely from editors. However, using a text field gives you more control to see if the field works as expected.

You can set an array of structure field data and the name of the `hashID` field in your `config.php` file. As soon as you save the page, the structure field entries will be updated with the unique hash.

## Settings

Add the following settings in your `/site/config/config.php`:


### structure.id.data (required)

An array of page URIs with an  array of structure field names. You can use the placeholders `(:any)` and `(:all)` after a parent page (not by itself).


```php

c::set('structure.id.data', [
  'home' => ['addresses'],
  'projects/(:any)' => ['field1', 'field2']
]);

```

### structure.id.hashfield (optional)

```php
c::set('structure.id.hashfield', 'hash_id');
```

The name of the hashID field within the structure field. The default field name is `hash_id`. All blueprints must use the same hash ID field. The field is created if it doesn't exist.

### structure.id.hashGenerator (optional)

```php
c::set('structure.id.hashGenerator', function() {
  return myHashGenerator();
});
```

Use your own hash generator. The option expects a callback function that returns your own unique hash.


## Changelog

## v1.2.2

- Adds option for user defined hash generator

## v1.2.1

- Improve code

## v1.2.0

- Change naming of config Options
- Add support for multiple structure fields

## Credits

This plugin is inspired by the AutoID plugin:

- [AutoID](https://github.com/hellicht/kirby-autoid) plugin by @hellicht

## License

The Structure ID plugin is open-sourced software licensed under the MIT license.

Copyright © 2018 Sonja Broda info@texniq.de https://www.texniq.de
