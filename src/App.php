<?php
namespace Eafion\MyApi;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Symfony\Component\Cache\Simple\FilesystemCache;

class App
{
    /**
     * Stores an instance of the Slim application.
     *
     * @var \Slim\App
     */
    private $app;

    public function __construct()
    {
        $app = new \Slim\App([
            'settings'=>[
                'displayErrorDetails'=>true
            ]
        ]);

        $app->post('/shorten', function (Request $request, Response $response) {
            if (!Helpers::emptyOrInvalidParams('url', $request, $response)) {
                $request_data = $request->getParsedBody();

                //initialize parameters
                $url = $request_data['url'];
                $provider = $request_data['provider'];
                $provider_req =
                    ($provider=="rebrandly" || $provider=="bitly") ? $provider : "No known provider was requested";

                //default provider used is bitly
                $provider_used = ($provider == "rebrandly") ? "rebrandly" : "bitly";
                $curl_error_msg = '';

                //check if caching system is absent/unavailable
                if (class_exists('Symfony\Component\Cache\Simple\FilesystemCache')) {
                    //check if requested url exists in cache
                    $cache = new FilesystemCache();
                    $key = 'url_' . urlencode($url) . '_provider_' . $provider_used;
                    $newUrl = $cache->get($key);
                    $cacheUsed = "File cache used for this request";
                    if (!$cache->has($key)) {
                        $resultArray = Helpers::callCurl($provider_used, $url);
                        $newUrl = $resultArray['newUrl'];
                        $curl_error_msg = $resultArray['error'];
                        //save created url to cache for one hour
                        $cacheUsed = "File cache NOT used for this request";
                        $cache->set($key, $newUrl, 3600);
                    }
                } else {
                    $cacheUsed = "There is no cache present";
                    $resultArray = Helpers::callCurl($provider_used, $url);
                    $newUrl = $resultArray['newUrl'];
                    $curl_error_msg = $resultArray['error'];
                }

                //catch errors
                if ($curl_error_msg!= '') {
                    $message = array();
                    $message['error'] = true;
                    $message['message'] = 'An error occurred, url could not be shortened : ' . $curl_error_msg;
                    $response->write(json_encode($message));
                    return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(422);
                } else {
                    $message = array();
                    $message['error'] = false;
                    $message['message'] = 'Url shortened successfully';
                    $message['requested_url'] = $url;
                    $message['provider_requested'] = $provider_req;
                    $message['provider_used'] = $provider_used;
                    $message['shortened_url'] = $newUrl;
                    $message['cache'] = $cacheUsed;
                    $response->write(json_encode($message));
                    return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(201);
                }
            }

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(422);
        });

        $this->app = $app;
    }

    /**
     * Get an instance of the application.
     *
     * @return \Slim\App
     */
    public function get()
    {
        return $this->app;
    }
}
