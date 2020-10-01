# Laravel OpenApi generator

This package provides painless OpenApi YAML generation from existing routes. 

The idea is to have as little work to do as possible in order to generate the 
API documentation, so the package will try to assume a lot of things such as
models from controller names, parameters based on actual tables, outputs based
on fake requests done etc. 

For custom inputs/outputs, options will be provided.

**Still in early development, expect issues :)**

**Stay tuned**

## Installation

Install the package through composer. It is automatically registered
as a Laravel service provider.

``composer require asseco-voice/laravel-open-api``

## Usage

Running the command ``php artisan voice:open-api`` will generate a new YAML
file at ``storage/app/open-api.yaml`` location.

Models database schema is being cached for performance (1d TTL), 
if you modify a migration be sure to run ``php artisan voice:open-api --bust-cache``
which will force re-caching. 

## Config

Publish the configuration with 
``php artisan vendor:publish --provider="Voice\OpenApi\OpenApiServiceProvider"``.

Configuration requires your minimal engagement, however there are some things which
package can't assume. 

If you have models outside of ``App`` namespace, be sure to include full namespace
to these as well so that package can automatically get the model attributes. 
