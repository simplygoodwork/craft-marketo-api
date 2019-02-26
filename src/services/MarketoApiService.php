<?php
/**
 * Marketo API plugin for Craft CMS 3.x
 *
 * Pass form submissions to Marketo
 *
 * @link      http://simplygoodwork.com
 * @copyright Copyright (c) 2019 simplygoodwork
 */

namespace simplygoodwork\marketoapi\services;

use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use GuzzleHttp\RequestOptions;

use simplygoodwork\marketoapi\MarketoApi;

use Craft;
use craft\base\Component;

/**
 * MarketoApiService Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    simplygoodwork
 * @package   MarketoApi
 * @since     1.0.0
 */
class MarketoApiService extends Component
{
    // Public Methods
    // =========================================================================

    public function getToken($host, $clientId, $clientSecret) {
        try {
            $guzzleClient = new \GuzzleHttp\Client;

            $urlData = array(
                'grant_type' => 'client_credentials',
                'client_id' => $clientId,
                'client_secret' => $clientSecret
            );
            $url = "https://" . $host . ".mktorest.com/identity/oauth/token?" . http_build_query($urlData);

            $response = $guzzleClient->get($url);
            $responseBody = $response->getBody();
            $result = json_decode($responseBody)->access_token;
        } catch (GuzzleRequestException $e){
            $result = json_decode($e->getResponse()->getBody());
        }

        return $result;
    }

    public function createLead($host, $clientId, $clientSecret, $fields) {
        try {
            $guzzleClient = new \GuzzleHttp\Client();
            $urlData = array(
              'access_token' => $this->getToken($host, $clientId, $clientSecret)
            );
            $url = "https://" . $host . ".mktorest.com/rest/v1/leads.json?" . http_build_query($urlData);
            $postData = array(
                'action' => "createOnly",
                'lookupField' => "email",
                'input' => array(
                    $fields
                )
            );

            $reqOpts = new \GuzzleHttp\RequestOptions();
            $response = $guzzleClient->request('post', $url, [
                $reqOpts::JSON => $postData
            ]);
            return [
                'statusCode' => $response->getStatusCode(),
                'reason' => $response->getReasonPhrase(),
                'body' => json_decode($response->getBody(), true)
            ];

        } catch (GuzzleRequestException $e){
            $result = json_decode($e->getResponse()->getBody());
        }

        return $result;

    }
}
