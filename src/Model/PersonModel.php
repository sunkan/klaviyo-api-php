<?php

namespace Klaviyo\Model;

use Klaviyo\Exception\CannotDeleteRequiredSpecialAttributeKeyException;
use Klaviyo\Exception\InvalidSpecialAttributeKeyException;

/**
 * Simple model for a Klaviyo "Person".
 *
 * @property string $id
 * @property string email
 * @property string firstName
 * @property string lastName
 * @property string organization
 * @property string title
 * @property string city
 * @property string region
 * @property string zip
 * @property string country
 * @property string timezone
 * @property string phoneNumber
 */
class PersonModel extends BaseModel implements PersonIdInterface
{
    protected $id;
    protected $objectType = 'person';
    protected $email;
    protected $firstName;
    protected $lastName;
    protected $organization;
    protected $title;
    protected $city;
    protected $region;
    protected $zip;
    protected $country;
    protected $timezone;
    protected $phoneNumber;
    protected $customAttributes;
    protected $unsetAttributes;
    protected static $optionalDefaults = [
        'id' => '',
        '$last_name' => '',
        '$organization' => '',
        '$title' => '',
        '$city' => '',
        '$region' => '',
        '$zip' => '',
        '$country' => '',
        '$timezone' => '',
        '$phone_number' => '',
    ];
    protected static $attributeKeys = [
        'object',
        'id',
        '$email',
        '$first_name',
        '$last_name',
        '$organization',
        '$title',
        '$city',
        '$region',
        '$zip',
        '$country',
        '$timezone',
        '$phone_number',
    ];

    /**
     * {@inheritdoc}
     */
    public function __construct($configuration)
    {
        parent::__construct($configuration);

        $this->setAttributes($configuration);
    }

    /**
     * {@inheritdoc}
     */
    public static function createFromJson($json = ''): ModelInterface
    {
        $configuration = json_decode($json, true);

        $allowed_attributes = array_flip(array_filter(array_keys($configuration), function ($attribute_key) {
            // The API is returning these values as custom attributes when it really
            // probably should not.
            return !($attribute_key === 'email' || $attribute_key === 'first_name' || $attribute_key === 'last_name');
        }));
        $configuration = array_intersect_key($configuration, $allowed_attributes);

        return new static($configuration);
    }

    /**
     * Set the attributes for the person model.
     * @param array $configuration
     */
    protected function setAttributes(array $configuration)
    {
        $this->id = $configuration['id'];
        $this->email = $configuration['$email'];
        $this->firstName = $configuration['$first_name'];
        $this->lastName = $configuration['$last_name'];
        $this->organization = $configuration['$organization'];
        $this->title = $configuration['$title'];
        $this->city = $configuration['$city'];
        $this->region = $configuration['$region'];
        $this->zip = $configuration['$zip'];
        $this->country = $configuration['$country'];
        $this->timezone = $configuration['$timezone'];
        $this->phoneNumber = $configuration['$phone_number'];
        if (!empty($configuration['$unset'])) {
            $this->unsetAttributes = $configuration['$unset'];
        }

        $this->setCustomAttributes($configuration);
    }

    /**
     * Update the person model from an array.
     *
     * @param array $configuration
     * @return $this
     */
    public function updateFromArray($configuration)
    {
        $configuration += $this->toArray();
        $this->setAttributes($configuration);

        return $this;
    }

    /**
     * Retrieve an array of all attribute keys.
     */
    public static function getAttributeKeys(): array
    {
        return self::$attributeKeys;
    }

    /**
     * Set the custom attributes for the person.
     */
    private function setCustomAttributes($configuration)
    {
        $custom_attribute_keys = array_flip(
            array_filter(
                array_keys($configuration),
                [__CLASS__, 'isCustomAttributeKey']
            )
        );
        $custom_attributes = array_intersect_key($configuration, $custom_attribute_keys);
        $this->customAttributes = $custom_attributes;
    }

    /**
     * Determine if the attribute is a custom attribute.
     *
     * @param string $attributeKey
     * @return bool
     *   Returns TRUE if the attribute is considered to be a custom attribute.
     */
    public static function isCustomAttributeKey(string $attributeKey): bool
    {
        return !self::isSpecialAttributeKey($attributeKey);
    }

    /**
     * Determine if the attribute is a special attribute.
     *
     * @param string $attributeKey
     * @return bool
     *   Returns TRUE if the attribute is considered to be a "special" Klaviyo
     *   attribute.
     */
    public static function isSpecialAttributeKey(string $attributeKey): bool
    {
        return in_array($attributeKey, self::$attributeKeys);
    }

    /**
     * Retrieve a custom attribute by its attribute key.
     * @param string $attributeKey
     * @return mixed
     */
    public function getCustomAttribute(string $attributeKey)
    {
        return !empty($this->customAttributes[$attributeKey]) ? $this->customAttributes[$attributeKey] : '';
    }

    /**
     * Retrieve all custom attributes for the person.
     */
    public function getAllCustomAttributes()
    {
        return $this->customAttributes;
    }

    /**
     * Delete an attribute from the person model.
     *
     * @param string $attributeKey
     *   The attribute key of the attribute to delete.
     *
     * @return $this
     * @throws CannotDeleteRequiredSpecialAttributeKeyException
     * @throws InvalidSpecialAttributeKeyException
     */
    public function deleteAttribute(string $attributeKey)
    {
        if (self::isSpecialAttributeKey($attributeKey) && isset(self::$optionalDefaults[$attributeKey])) {
            $property = self::getModelPropertyFromSpecialAttributeKey($attributeKey);
            $this->{$property} = self::$optionalDefaults[$attributeKey];
        } elseif ($this->getCustomAttribute($attributeKey)) {
            unset($this->customAttributes[$attributeKey]);
        } elseif (isset(self::$attributeKeys)) {
            throw new CannotDeleteRequiredSpecialAttributeKeyException(
                sprintf('%s is a required special attribute and cannot be deleted.', $attributeKey)
            );
        }

        $this->unsetAttributes[] = $attributeKey;
        return $this;
    }

    /**
     * Retrieve the model property from the special attribute key.
     *
     * @param string $attributeKey      The special attribute key for which to retrieve the model property.
     * @return string                   The string representing the model property.
     * @throws InvalidSpecialAttributeKeyException
     */
    public static function getModelPropertyFromSpecialAttributeKey(string $attributeKey): string
    {
        if (!self::isSpecialAttributeKey($attributeKey)) {
            throw new InvalidSpecialAttributeKeyException(
                sprintf('%s is not a valid special Klaivyo attribute.', $attributeKey)
            );
        }

        if (strpos($attributeKey, '$') !== false) {
            $attribute_key_segements = explode('_', substr($attributeKey, 1));
            $attributeKey = $attribute_key_segements[0] . implode('', array_map(
                'ucfirst',
                array_slice($attribute_key_segements, 1)
            ));
        } elseif ($attributeKey === 'object') {
            $attributeKey = 'objectType';
        }

        return $attributeKey;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        $serializable_options = [
            // Object type intentionally left out because the Klaviyo API treats it as
            // a custom field.
            'id' => $this->id,
            '$email' => $this->email,
            '$first_name' => $this->firstName,
            '$last_name' => $this->lastName,
            '$organization' => $this->organization,
            '$title' => $this->title,
            '$city' => $this->city,
            '$region' => $this->region,
            '$zip' => $this->zip,
            '$country' => $this->country,
            '$timezone' => $this->timezone,
            '$phone_number' => $this->phoneNumber,
        ] + $this->getAllCustomAttributes();

        if (!empty($this->unsetAttributes)) {
            $serializable_options['$unset'] = $this->unsetAttributes;
        }

        return $serializable_options;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        // Add object type back when converting to an array since we removed it due
        // to an oddity in the Klaviyo API.
        return ['object' => $this->objectType] + json_decode(json_encode($this), true);
    }

    public function getId(): string
    {
        return $this->id;
    }
}
