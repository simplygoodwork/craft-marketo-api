<?php
/**
 * Marketo API plugin for Craft CMS 3.x
 *
 * Pass form submissions to Marketo
 *
 * @link      http://simplygoodwork.com
 * @copyright Copyright (c) 2019 simplygoodwork
 */

namespace simplygoodwork\marketoapi\models;

use simplygoodwork\marketoapi\MarketoApi;

use Craft;
use craft\base\Model;

/**
 * MarketoApi Settings Model
 *
 * This is a model used to define the plugin's settings.
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    simplygoodwork
 * @package   MarketoApi
 * @since     1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    public $hostKey = '';
    public $clientId = '';
    public $clientSecret = '';
    public $customActivityTypeId = '';
    public $mktoCookieFieldName = '';

    // Public Methods
    // =========================================================================

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
     *
     * @return array
     */
    public function rules()
    {
        return [
            [['hostKey', 'clientId', 'clientSecret', 'customActivityTypeId', 'mktoCookieFieldName'], 'string'],
            [['hostKey', 'clientId', 'clientSecret'], 'required'],
        ];
    }
}
