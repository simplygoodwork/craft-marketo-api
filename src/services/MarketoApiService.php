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

    public function getToken() {

        $settings = MarketoApi::$plugin->getSettings();
        $host = $settings['hostKey'];
        $clientId = $settings['clientId'];
        $clientSecret = $settings['clientSecret'];

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

    public function createLead($fields, $host) {
        try {
            $guzzleClient = new \GuzzleHttp\Client();
            $urlData = array('access_token' => $this->getToken());

            $url = "https://" . $host . ".mktorest.com/rest/v1/leads.json?" . http_build_query($urlData);
            $postData = array(
                'action' => "createOrUpdate",
                'lookupField' => "email",
                'input' => array(
                    $fields
                )
            );

            $reqOpts = new \GuzzleHttp\RequestOptions();
            $response = $guzzleClient->request('post', $url, [
                $reqOpts::JSON => $postData
            ]);

            // exit(var_dump($fields, true));
            // exit(var_dump(json_decode($response->getBody()->getContents(), true)));
            $result = json_decode($response->getBody()->getContents(), true);

            return [
                'statusCode' => $response->getStatusCode(),
                'reason' => $response->getReasonPhrase(),
                'result' => $result['result'],
                'success' => $result['success'],
                'leadId' => $result['result'][0]['id'],
                'actionStatus' => $result['result'][0]['status'],
                'urlData' =>  $urlData
            ];

        } catch (GuzzleRequestException $e){
            $result = json_decode($e->getResponse()->getBody());
        }

        return $result;

    }

    public function associateLead($leadId, $cookie, $host) {
        try {
            $guzzleClient = new \GuzzleHttp\Client();
            $urlData = array(
                'access_token' => $this->getToken(),
                'cookie' => $cookie
            );

            $url = "https://" . $host . ".mktorest.com/rest/v1/leads/" . $leadId . "/associate.json?" . http_build_query($urlData);

            $reqOpts = new \GuzzleHttp\RequestOptions();
            $response = $guzzleClient->request('post', $url, [
                $reqOpts::JSON => []
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            return [
                'statusCode' => $response->getStatusCode(),
                'reason' => $response->getReasonPhrase(),
                'requestId' => $result['requestId'],
                'result' => $result['result'],
                'success' => $result['success'],
                'urlData' =>  $urlData
            ];

        } catch (GuzzleRequestException $e){
            $result = json_decode($e->getResponse()->getBody());
        }

        return $result;

    }

    public function addActivity($host, $activityTypeId, $leadId, $submittedFrom, $urlData = null) {
        try {
            $guzzleClient = new \GuzzleHttp\Client();

            if ($urlData == null) {
                $urlData = array(
                    'access_token' => $this->getToken()
                );
            }
            $url = "https://" . $host . ".mktorest.com/rest/v1/activities/external.json?" . http_build_query($urlData);

            $date = new \DateTime('now');
            $dateTime = $date->format('Y-m-d\TH:i:s');

            $postData = array(
                'input' => array([
                    'activityDate' => $dateTime,
                    'activityTypeId' => $activityTypeId,
                    'leadId' => $leadId,
                    'primaryAttributeValue' => $submittedFrom,
                ])
            );

            $reqOpts = new \GuzzleHttp\RequestOptions();
            $response = $guzzleClient->request('post', $url, [
                $reqOpts::JSON => $postData
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            return [
                'statusCode' => $response->getStatusCode(),
                'reason' => $response->getReasonPhrase(),
                'result' => $result['result'],
                'success' => $result['success'],
                'activityId' => $result['result'][0]['id'],
                'actionStatus' => $result['result'][0]['status']
            ];

        } catch (GuzzleRequestException $e){
            $result = json_decode($e->getResponse()->getBody());
        }

        return $result;

    }
}
