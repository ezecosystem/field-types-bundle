<?php
/**
 * File containing the LegacyConverter class.
 */
namespace xrow\FieldTypesBundle\FieldType\XrowNodeRelation;

use eZ\Publish\Core\FieldType\GatewayBasedStorage;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;

/**
 * Storage for the XrowNodeRelation field type
 */
class XrowNodeRelationStorage extends GatewayBasedStorage
{
    /**
     * @see \eZ\Publish\SPI\FieldType\FieldStorage
     */
    public function storeFieldData( VersionInfo $versionInfo, Field $field, array $context )
    {
        $gateway = $this->getGateway( $context );

        return $gateway->storeFieldData( $versionInfo, $field );
    }

    /**
     * Populates $field value property based on the external data.
     * $field->value is a {@link eZ\Publish\SPI\Persistence\Content\FieldValue} object.
     * This value holds the data as a {@link eZ\Publish\Core\FieldType\Value} based object,
     * according to the field type (e.g. for TextLine, it will be a {@link eZ\Publish\Core\FieldType\TextLine\Value} object).
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param array $context
     *
     * @return void
     */
    public function getFieldData( VersionInfo $versionInfo, Field $field, array $context )
    {
        $gateway = $this->getGateway( $context );
        $gateway->getFieldData( $versionInfo, $field );
    }

    /**
     * @param VersionInfo $versionInfo
     * @param array $fieldId
     * @param array $context
     *
     * @return boolean
     */
    public function deleteFieldData( VersionInfo $versionInfo, array $fieldIds, array $context )
    {
        $this->getGateway( $context )->deleteFieldData( $versionInfo, $fieldIds );
    }

    /**
     * Checks if field type has external data to deal with
     *
     * @return boolean
     */
    public function hasFieldData()
    {
        return true;
    }

    /**
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param array $context
     */
    public function getIndexData( VersionInfo $versionInfo, Field $field, array $context )
    {
        return ( is_array( $field->value->externalData ) ? $field->value->externalData['street'] : null );
    }
}