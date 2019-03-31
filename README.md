# MyApi
An API to shorten URL using known providers

POST /shorten

The request checks if a url is present and has proper formatting. Then the microservice shortens it based on the provider requested or the default provider if no provider is requested.
Body Params
url:required, string (the URL to be shortened).
provider: string (the URL shortening provider to use. If none is selected, the microservice automatically selects bitly).

Results Format
201 Created
{
    "error": false,
    "message": "Url shortened successfully",
    "requested_url": "https://www.youtube.com/watch?v=z8KKL8qHVuY",
    "provider_requested": "rebrandly",
    "provider_used": "rebrandly",
    "shortened_url": "rebrand.ly/2nqpo",
    "cache": "File cache used for this request"
}

{
    "error": false,
    "message": "Url shortened successfully",
    "requested_url": "https://www.youtube.com/watch?v=248S6jQ2wAI",
    "provider_requested": "No known provider was requested",
    "provider_used": "bitly",
    "shortened_url": "http://bit.ly/2FzaVxh",
    "cache": "File cache NOT used for this request"
}

422 Unprocessable Entity

{
    "message": "Parameter url is not valid",
    "error": true
}

{
    "message": "Required parameter url is missing or empty",
    "error": true
}

The response provides details, including the requested and created shortened url, the provider requested (or not) as well as the one used. It also contains a parameter informing if the caching system is present and was used for the specific request. An error message is also provided in case there is something wrong with the request parameters or the whole process (curl error).

TESTING

The /shorten route was tested by creating parameter value combinations for calls to verify its functionality as well as to expose its failures. PHPUnit testing component was installed and also the Slim\Http\Environment class was used for mock environment objects with custom information to build up the Request object. 
The tests can be found in the tests/MyApiTest.php file and include the following scenarios:

Empty url returns the proper error message.
Malformed url returns the proper message.
Empty provider automatically selects one.
Shortened url comprises of less characters than the original url.

CACHING

FilesystemCache
The caching component used was the symfony/cache, specifically simple caching FilesystemCache. The microservice checks if the specific id (related to both the provider and also the url requested) has stored value in the cache. If the value is found it gets retrieved without an extra request call. If it returns empty, the request is made and the value is stored in cache with a unique id.
If the caching system is removed or is unavailable, the process continues normally without caching the urls.

Run composer update to get full vendor folder
