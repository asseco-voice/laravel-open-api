# Laravel OpenApi generator

This package provides painless OpenApi YML generation from existing routes. 

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

Running the command ``php artisan voice:open-api`` will generate a new `.yml`
file at ``storage/app/open-api.yml`` location.

What is covered out-of-the-box:

- read all routes
- inferring model name from controller name
- group (tag) them automatically
- get title and description from 
- generate request and response parameters

For cases not covered by this convention, refer to [config](#config).

### Example

Given the controller:

```
class UserController extends Controller
{
    /**
     * Store a newly created resource in storage.    
     *
     * Create new User object and store it in DB.
     */
    public function store(Request $request): JsonResponse
    {
        $user = User::query()->create($request->all());

        return response()->json($user);
    }

    ...
```

- command will infer ``User`` as being the main model for the controller
- title: ``Store a newly created resource in storage.`` 
- description: ``Create new User object and store it in DB.``
- request data: ``User`` model table attributes without `id`, `created_at`, `updated_at` attributes
- response data: complete ``User`` model table attributes

## Cache

Models database schema is being cached for performance (1d TTL), 
if you modify a migration be sure to run ``php artisan voice:open-api --bust-cache``
which will force re-caching. 

## Config

Publish the configuration with 
``php artisan vendor:publish --provider="Voice\OpenApi\OpenApiServiceProvider"``.

Configuration requires your minimal engagement, however there are some things which
package can't assume. 

- For models outside of ``App`` namespace, be sure to include full namespace
to ``namespaces`` config key as well so that package can automatically get the 
model attributes. 
- For controllers not named after their models (in ``ModelController`` format)
remap in ``controllerModelMapping`` config key.

