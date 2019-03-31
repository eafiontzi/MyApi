<?php
namespace Eafion\MyApi;

class Provider
{
    /**
     * Returns provider parameters based on the provider name
     *
     * @var $providerName
     * @var $url
     * @return array $parameters
     */
    public static function getProviderParameters($providerName, $url)
    {
        $parameters = array();
        if (!empty($providerName)) {
            switch ($providerName) {
                case "bitly":
                    $post_data = [
                        'group_guid' => 'Bj1blMz26aN',
                        'long_url' => $url,
                    ];
                    $parameters = [
                        'post_data' => $post_data,
                        'provider_url' => "https://api-ssl.bitly.com/v4/shorten",
                        'response_result_param' => 'link',
                        'extra_header' => 'Authorization: Bearer 2d585c4f54c9e3f24451ece2bd7c5cf7e097908d'
                    ];
                    break;
                case "rebrandly":
                    $post_data = [
                        'domain' => [
                            'fullName' => 'rebrand.ly'
                        ],
                        'destination' => $url,
                    ];
                    $parameters = [
                        'post_data' => $post_data,
                        'provider_url' => "https://api.rebrandly.com/v1/links",
                        'response_result_param' => 'shortUrl',
                        'extra_header' => 'apikey: fef715b9d3fc4020b38490b6e4f8d24d'
                    ];
                    break;
            }
        }

        return $parameters;
    }
}
