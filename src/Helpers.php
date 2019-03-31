<?php
namespace Eafion\MyApi;

class Helpers
{
    /**
     * Checks if required parameters are present and valid
     *
     * @var $required_param
     * @var $request
     * @var $response
     * @return bool $error
     */
    public static function emptyOrInvalidParams($required_param, $request, $response)
    {
        $error = false;
        $error_detail = array();
        $request_params = $request->getParsedBody();

        //get the associated error message
        if (!isset($request_params[$required_param]) || strlen($request_params[$required_param])<=0) {
            $error = true;
            $error_detail['message'] = 'Required parameter ' . $required_param . ' is missing or empty';
        } else {
            if ($required_param == "url") {
                if (filter_var($request_params['url'], FILTER_VALIDATE_URL) === false) {
                    $error = true;
                    $error_detail['message'] = 'Parameter url is not valid';
                }
            }
        }

        if ($error) {
            $error_detail['error'] = true;
            $response->write(json_encode($error_detail));
        }
        return $error;
    }

    /**
     * Makes the request with needed parameters
     *
     * @var $provider_used
     * @var $url
     * @return array with $newUrl and $curl_error_msg
     */
    public static function callCurl($provider_used, $url)
    {
        $curl_error_msg = '';
        //use provider name to get necessary parameters for request
        $pr_params = Provider::getProviderParameters($provider_used, $url);
        foreach ($pr_params['post_data'] as $index => $post) {
            $post_data[$index] = $post;
        }
        $provider_url = $pr_params['provider_url'];
        $response_result_param = $pr_params['response_result_param'];
        $extra_header = isset($pr_params['extra_header']) ? $pr_params['extra_header'] : "";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $provider_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            $extra_header,
            "Content-Type: application/json"
        ));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
        $result = curl_exec($ch);
        if (curl_error($ch)) {
            $curl_error_msg = curl_error($ch);
        }
        curl_close($ch);
        $response_curl = json_decode($result, true);

        return [
            'newUrl' => $response_curl[$response_result_param],
            'error' => $curl_error_msg
        ];
    }
}
