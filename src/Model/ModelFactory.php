<?php

namespace Klaviyo\Model;

use Klaviyo\Exception\MissingModelTypeException;
use Klaviyo\KlaviyoApi;

/**
 * Model creation factory.
 */
class ModelFactory
{
    protected static $modelMap = [
        'campaign' => CampaignModel::class,
        'email-template' => TemplateModel::class,
        'rendered-template' => RenderedTemplateModel::class,
        'list' => ListModel::class,
        'membership' => MembershipModel::class,
        'page' => PageModel::class,
        'person' => PersonModel::class,
        'person_list' => PersonListModel::class,
        'empty' => EmptyModel::class,
    ];

    /**
     * Retrieve the model map.
     */
    public static function getModelMap()
    {
        return self::$modelMap;
    }

    /**
     * Create a new model.
     *
     * @param array $configuration
     *   The key, value pair array to use for populating the data model.
     * @param string $type
     *   The type of the data model to create.
     *
     * @return ModelInterface
     *   A data model representing the specified data type.
     * @throws MissingModelTypeException
     */
    public static function create(array $configuration = [], string $type = ''): ModelInterface
    {
        return self::callModelCreationMethod('create', $configuration, $type);
    }

    /**
     * Create a new model from JSON.
     *
     * @param array|string $configuration
     *   The key, value pair array to use for populating the data model.
     * @param string $type
     *   The type of the data model to create.
     *
     * @return ModelInterface
     *   A data model representing the specified data type.
     * @throws MissingModelTypeException
     */
    public static function createFromJson($configuration, string $type = ''): ModelInterface
    {
        return self::callModelCreationMethod('createFromJson', $configuration, $type);
    }

    /**
     * Create a new model from using the specified method.
     *
     * @param string $method
     *   The method to execute on the model for creation.
     * @param array|string $configuration
     *   The key, value pair array to use for populating the data model.
     * @param string $type
     *   The type of the data model to create.
     *
     * @return ModelInterface
     *   A data model representing the specified data type.
     * @throws MissingModelTypeException
     */
    public static function callModelCreationMethod(string $method, $configuration, string $type = ''): ModelInterface
    {
        $type = self::getModelType($configuration, $type);

        $model = null;
        if (isset(self::$modelMap[$type])) {
            $model = call_user_func(self::$modelMap[$type] . "::$method", $configuration);
        } else {
            $model = call_user_func(self::$modelMap['empty'] . "::$method", $configuration);
        }

        return $model;
    }

    /**
     * Get the model type.
     *
     * @param array|string $configuration
     *   The key, value pair array to use for populating the data model.
     * @param string $type
     *   If set then we will check out data map and retrieve the model type.
     *
     * @return string
     *   The model data type.
     * @throws MissingModelTypeException
     */
    public static function getModelType($configuration = [], string $type = ''): string
    {
        if (empty($type) && !empty($configuration['object'])) {
            $type = $configuration['object'];
        }

        if (empty($type) && !empty($configuration)) {
            throw new MissingModelTypeException('Unable to determine the model type.');
        }

        return KlaviyoApi::getModelType($type);
    }
}
