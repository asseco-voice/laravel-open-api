<p align="center"><a href="https://see.asseco.com" target="_blank"><img src="https://github.com/asseco-voice/art/blob/main/evil_logo.png" width="500"></a></p>

# Laravel OpenApi generator

This package provides painless OpenApi YML generation from existing routes. 

The idea is to have as little work to do as possible in order to generate the 
API documentation, so the package will try to assume a lot of things such as
models from controller names, request and response parameter based on actual
tables etc. 

For custom inputs/outputs, options will be provided.

## Installation

Install the package through composer. It is automatically registered
as a Laravel service provider.

``composer require asseco-voice/laravel-open-api``

## Usage

Running the command ``php artisan asseco:open-api`` will generate a new `.yml`
file at ``project_root/open-api.yml``.

What is covered out-of-the-box:

- read all routes
- inferring model name from controller name
- group (tag) them automatically
- get title and description from 
- generate request and response parameters

For cases not covered by this convention, refer to [overriding defaults section](#overriding-defaults).

For additional tweaking, refer to [config](#config).

Depending on number of routes, first run may take a few seconds as it is requesting a DB schema for
each model it can find. This is cached, so every subsequent run will run much faster. 

### Simple out-of-the-box example

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

## Overriding defaults

Custom cases are handled through controller and method annotations in doc blocks:

```
/**
 * My controller doc block
 *
 * @annotation random annotation
 */
class MyController extends Controller
{
    /**
     * My method doc block
     *
     * @annotation another random annotation
     */
    public function index()
    {
        ...
    }

   ...
}
```

### Groups (tags)

When talking about 'groups', we are actually talking about OpenApi 'tags'. 

By default, command will take the controller name, remove ``Controller`` from it
and split `PascalCase` with spaces (i.e. `SysUserController` results in `Sys User` group name).

- ``@group`` within a controller doc block will override default group. 
- ``@group`` within a method doc block will override default group and controller group
making it an operator with the highest precedence.

It is possible to stack multiple group annotations.

### Models

Model is used to try to automatically generate inputs and outputs for standard Laravel
CRUD functions.

**Input (request):** model DB schema with either only fillable properties or except guarded.
Fillable properties have precedence, guarded will be ignored if fillable exists.

**Output (response):** complete model DB schema without hidden fields. 

It is completely valid to have no model associated. In this case, no automatic actions
will be performed which require an existing model class.

By default, model name is extracted from controller name. To change this behavior,
you have few options:

- Map a specific controller to a specific model. See [config](#config) for details.
- Include ``@model`` tag within a controller:
   - Specifying namespaced model will use that model ``@model My\Namespaced\Model`` 
   - Not specifying the namespace will use controller's namespace ``@model Model``
   
Controller tag has higher precedence over configuration mapping. If both exist, and
controller tag fails, configuration will try to fetch the model as well. Failing on
both fronts will result in model being ``null``.

It is possible to exclude part of the model for the request:

- ``@exclude attribute1 attribute2`` is a space separated list of specified model attributes
which will not be included in request data.  

### Path parameters

By default, path parameter(s) will be set as integer (assuming most of the path parameters are 
model IDs). 

Override them by including the following in the method doc block:

- ``@path`` will override what is fetched by default. You must provide it in the following
convention ``@path name type description`` where:
   - ``name`` - parameter name.
   - ``type`` - [OpenApi data type](https://swagger.io/docs/specification/data-models/data-types/).
   - ``description`` - text which will be set as parameter description (not required, 
   empty by default, so it can be omitted). 
   
Examples:

```
@path name type
@path name type Some description
```

It is not possible to set path parameter ``required`` property. It is automatically set to true because
OpenApi doesn't support optional path parameters (even though Laravel does).

### Request/response parameters

By default, request/response parameter(s) will be [extracted from model](#models).

Override them by including ``@request`` or/and `@response` in the method doc block.

Example for ``@request``, working the same for `@response`:

- ``@request`` will override what is fetched by default. You must provide it in the following
convention ``@request name type required description`` where:
   - ``name`` - parameter name.
   - ``type`` - [OpenApi data type](https://swagger.io/docs/specification/data-models/data-types/).
   - ``required`` - boolean `true/false` value indicating whether the parameter is required 
   (if omitted, will be set to `true`)
   - ``description`` - text which will be set as parameter description (if omitted, will be set to
    empty string)
   
Examples:

```
@request name type
@request name type true
@request name type false Some description
```

For multiple parameters it is also possible to adopt a different convention:

```
@request
name type required description
name type required description
name type required description
```

In case you want an arbitrary string as a request/response, it can be achieved 
by using double quotes when setting request/response parameters. This way, all 
other parameters will be ignored and only the string inside double quotes will 
be returned.

Example:

```
@response "example"
```

#### Response specific

Responses will by default be marked as multiple (indicating collection output, not a single model)
when looking at ``GET`` request without path parameters.

- including ``@multiple true/false`` in the method doc block will override those defaults 

If the variable type is ``array``, you can provide additional property within the parenthesis (be
sure not to leave blank space between type and parenthesis) to indicate
of what type are the array values: 

```
@response attribute array[string] true Some description
```

It is also possible to directly append a pivot table to the response, even if it 
has no model associated with it.

```
@pivot table_name
```

For example, if the table was appended to the ``User`` model, the following 
response will be returned:

```
{
    "name": "string"
    "email": "string"
    "pivot": {
      "user_id": 0
      "example_id": 0
    }
}
```

#### Request specific

You can include additional input models alongside original model with `@append key Class` in your method 
doc block. This will append given ``Class`` properties on already existing model using the `key` as a key. 

I.e. having original model ``User`` (with properties name, email) you want to append `Post` model (with properties
title, description) to it as a list of inputs. 

```
@model User // <-- not needed if it is UserController or you already specified model on the controller

@appends posts Post
``` 

This will result in following request:

```
{
    "name": "string"
    "email": "string"
    "posts": {
        "title": "string"
        "description": "string"
    }
}
```

## Cache

Models database schema is being cached for performance (1d TTL), 
if you modify a migration be sure to run ``php artisan asseco:open-api --bust-cache``
which will force re-caching. 

## Config

Publish the configuration with 
``php artisan vendor:publish --provider="Asseco\OpenApi\OpenApiServiceProvider"``.

Configuration requires your minimal engagement, however there are some things which
package can't assume. 

- For models outside of ``App`` namespace, be sure to include full namespace
to ``namespaces`` config key as well so that package can automatically get the 
model attributes. 
- For controllers not named after their models (in ``ModelController`` format)
remap in ``controller_model_mapping`` config key.
