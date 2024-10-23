# Otlib Rest API - TYPO3 REST API Extension

This extension allows you to create and configure RESTful API endpoints in TYPO3 using the `Otlib\RestApi\Api`. It provides a
flexible way to define routes, controllers, authentication types, and request methods, enabling developers to build robust APIs
for TYPO3.

### Installation
- Run the following command to install the package via Composer:
    ```bash
     composer require "otlib/rest-api"
    ```
- Or download the repository and add it to your extensions.
  https://github.com/othmanmajd/otlib-rest-api.git

### Defining an API Endpoint
Use the Otlib\RestApi\Api class to define your API endpoints. You can set up the path, controller, method, request method, and
authentication type.

### Basic Usage
The following example demonstrates creating a basic API endpoint that responds to GET requests.
This example creates a GET endpoint at  ```/api/products ``` that calls the list *method* of *ProductController*.

   ```php
    ext_localconf.php
    __________________

    \Otlib\RestApi\Api::newApi('check')
    ->setController(ProductController::class)
    ->setMethod('getAll');
   ```

### Advanced Usage
The following example demonstrates creating a more advanced API endpoint with custom settings:.
This example creates a POST endpoint at ```/_api/v2/``` check that requires Bearer token authentication and calls the check method
of the ProductController.

   ```php
    ext_localconf.php
    __________________

    use Otlib\RestApi\Api;
    use Otlib\RestApi\Enumeration\AuthType;
    use YourExtension\Controller\ProductController;

    Api::newApi('check')
    ->setPathPrefix('_api/v2')
    ->setController(ProductController::class)
    ->setMethod('check')
    ->setAuthType(AuthType::BEARER)
    ->setRequestMethod('POST')
    ->setHeaderWithNoCache(true);
 ```

### Authentication Types
The extension supports the following authentication types:

- **None:** No authentication is required.
- **Basic:** Basic authentication using a username and password.
- **Bearer:** Bearer token authentication.
- **Frontend TYPO3 User:** Authentication using TYPO3 frontend user session (fe_typo_user) **or** Username and
  Password. ```$request->getParsedBody()['user'] && $request->getParsedBody()['pass']```

#### Setting Authentication

```php
use Otlib\RestApi\Api;
use Otlib\RestApi\Enumeration\AuthType;

Api::newApi('secure-endpoint')->setAuthType(AuthType::BEARER);
```

### Error Handling
The extension uses custom exceptions to handle errors:

- InvalidRequestMethodException: Thrown when the request method doesn't match the API's configuration.
- MethodNotFoundException: Thrown when the specified controller method doesn't exist.
- ApiPathNotSetException: Thrown when the API path is not set.

### JSON Response for Errors
The middleware will return a JsonResponse with appropriate HTTP status codes and messages for errors, such as unauthorized
access (401 Unauthorized) or method not found.

### Running the Extension
- Define your API endpoints in your TYPO3 extension's setup.
- Make requests to the API using tools like Postman, curl, or custom clients.
- Secure endpoints using the supported authentication types.

## Example Code
```php
ext_localconf.php
__________________

use Otlib\RestApi\Api;
use Otlib\RestApi\Enumeration\AuthType;
use YourExtension\Controller\ProductController;

// Create a public API endpoint
Api::newApi('public')
->setController(ProductController::class)
->setMethod('publicInfo')
->setRequestMethod('GET')
->setAuthType(AuthType::NONE);

// Create a protected API endpoint with Bearer token authentication
Api::newApi('protected')
->setController(ProductController::class)
->setMethod('secureData')
->setRequestMethod('POST')
->setAuthType(AuthType::BEARER)
->setHeaderWithNoCache(true);

```
