<?php
/**
 * Marketo API plugin for Craft CMS 3.x
 *
 * Pass form submissions to Marketo
 *
 * @link      http://simplygoodwork.com
 * @copyright Copyright (c) 2019 simplygoodwork
 */

namespace simplygoodwork\marketoapi\controllers;

use simplygoodwork\marketoapi\MarketoApi;
use simplygoodwork\marketoapi\services\MarketoApiService;

use Craft;
use craft\web\Controller;

/**
 * Default Controller
 *
 * Generally speaking, controllers are the middlemen between the front end of
 * the CP/website and your plugin’s services. They contain action methods which
 * handle individual tasks.
 *
 * A common pattern used throughout Craft involves a controller action gathering
 * post data, saving it on a model, passing the model off to a service, and then
 * responding to the request appropriately depending on the service method’s response.
 *
 * Action methods begin with the prefix “action”, followed by a description of what
 * the method does (for example, actionSaveIngredient()).
 *
 * https://craftcms.com/docs/plugins/controllers
 *
 * @author    simplygoodwork
 * @package   MarketoApi
 * @since     1.0.0
 */
class DefaultController extends Controller
{
    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = ['submit'];
    private $_marketoApi;

    // Public Methods
    // =========================================================================
    /**
     * @return \Marketo_api|null
     */
    public function getMarketoApi ()
    {
        return $this->_marketoApi;
    }


    /**
     * Handle a request going to our plugin's submit URL
     *
     * @return mixed
     */
    public function actionSubmit()
    {
        $this->requirePostRequest();
        $submittedFrom = Craft::$app->request->getHostInfo().Craft::$app->request->getUrl();
        $fields = Craft::$app->request->getParam('fields');
        $settings = MarketoApi::$plugin->getSettings();

        if ($settings['mktoCookieFieldName'] && Craft::$app->request->getParam($settings['mktoCookieFieldName']))
        {
            $mktoCookie = Craft::$app->request->getParam($settings['mktoCookieFieldName']);
        }
        $result = MarketoApi::$plugin->service->createLead($fields, $settings['hostKey']);

        if ($result['success'] == true) {
            $activity = MarketoApi::$plugin->service->addActivity(
                $settings['hostKey'],
                $settings['customActivityTypeId'],
                $result['leadId'],
                $submittedFrom,
                $result['urlData']
            );
            
            if ($mktoCookie) {
                $association = MarketoApi::$plugin->service->associateLead(
                    $result['leadId'],
                    $mktoCookie,
                    $settings['hostKey']
                );
            }

            if ($activity['success'] == true) {
                return $this->redirectToPostedUrl();
            } else {
                // TODO: Set up & show error flash message
                return $this->redirect($submittedFrom);
            }
        } else {
            // TODO: Set up & show error flash message
            return $this->redirect($submittedFrom);
        }

    }
}
