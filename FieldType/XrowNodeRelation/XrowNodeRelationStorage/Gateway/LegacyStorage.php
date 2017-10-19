<?php
/**
 * File containing the XrowGisStorage Gateway
 */

namespace xrow\FieldTypesBundle\FieldType\XrowNodeRelation\XrowNodeRelationStorage\Gateway;

use xrow\FieldTypesBundle\FieldType\XrowNodeRelation\XrowNodeRelationStorage\Gateway;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;

class LegacyStorage extends Gateway
{
    /**
     * Connection
     *
     * @var mixed
     */
    protected $dbHandler;

    /**
     * Set database handler for this gateway
     *
     * @param mixed $dbHandler
     *
     * @return void
     * @throws \RuntimeException if $dbHandler is not an instance of
     *         {@link \eZ\Publish\Core\Persistence\Database\DatabaseHandler}
     */
    public function setConnection( $dbHandler )
    {
        // This obviously violates the Liskov substitution Principle, but with
        // the given class design there is no sane other option. Actually the
        // dbHandler *should* be passed to the constructor, and there should
        // not be the need to post-inject it.
        if ( !$dbHandler instanceof \eZ\Publish\Core\Persistence\Database\DatabaseHandler )
        {
            throw new \RuntimeException( "Invalid dbHandler passed" );
        }

        $this->dbHandler = $dbHandler;
    }

    /**
     * Returns the active connection
     *
     * @throws \RuntimeException if no connection has been set, yet.
     *
     * @return \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    protected function getConnection()
    {
        if ( $this->dbHandler === null )
        {
            throw new \RuntimeException( "Missing database connection." );
        }
        return $this->dbHandler;
    }

    /**
     * Stores the data stored in the given $field
     *
     * Potentially rewrites data in $field and returns true, if the $field
     * needs to be updated in the database.
     *
     * @param VersionInfo $versionInfo
     * @param Field $field
     *
     * @return boolean If restoring of the internal field data is required
     */
    public function storeFieldData( VersionInfo $versionInfo, Field $field )
    {
        if ( $field->value->externalData === null )
        {
            // Store empty value and return
            $this->deleteFieldData( $versionInfo, array( $field->id ) );
            $field->value->data = null;
            $field->value->sortKey = null;
            return;
        }

        if ( $this->hasFieldData( $field->id, $versionInfo->versionNo ) )
        {
            $this->updateFieldData( $versionInfo, $field );
        }
        else
        {
            $this->storeNewFieldData( $versionInfo, $field );
        }
        
        if(isset($field->value->externalData['destinationContentId']) && !is_null( $field->value->externalData['destinationContentId'])) {
            $field->value->data = array('destinationContentId' => $field->value->externalData['destinationContentId']);
            $field->value->sortKey = (int)$field->value->externalData['destinationContentId'];
        }
        
        return true;
    }

    /**
     * Performs an update on the field data
     *
     * @param VersionInfo $versionInfo
     * @param Field $field
     *
     * @return boolean
     */
    protected function updateFieldData( VersionInfo $versionInfo, Field $field )
    {
        $connection = $this->getConnection();

        $updateQuery = $connection->createUpdateQuery();
        $updateQuery->update( $connection->quoteTable( 'ezxgis_position' ) )
            ->set(
                $connection->quoteColumn( 'latitude' ),
                $updateQuery->bindValue( $field->value->externalData['latitude'] )
            )->set(
                $connection->quoteColumn( 'longitude' ),
                $updateQuery->bindValue( $field->value->externalData['longitude'] )
            )->set(
                $connection->quoteColumn( 'street' ),
                $updateQuery->bindValue( $field->value->externalData['street'] )
            )->set(
                $connection->quoteColumn( 'zip' ),
                $updateQuery->bindValue( $field->value->externalData['zip'] )
            )->set(
                $connection->quoteColumn( 'district' ),
                $updateQuery->bindValue( $field->value->externalData['district'] )
            )->set(
                $connection->quoteColumn( 'city' ),
                $updateQuery->bindValue( $field->value->externalData['city'] )
            )->set(
                $connection->quoteColumn( 'state' ),
                $updateQuery->bindValue( $field->value->externalData['state'] )
            )->set(
                $connection->quoteColumn( 'country' ),
                $updateQuery->bindValue( $field->value->externalData['country'] )
            )->set(
                $connection->quoteColumn( 'accurate' ),
                $updateQuery->bindValue( $field->value->externalData['accurate'] )
            )->where(
                $updateQuery->expr->lAnd(
                    $updateQuery->expr->eq(
                        $connection->quoteColumn( 'contentobject_attribute_id' ),
                        $updateQuery->bindValue( $field->id, null, \PDO::PARAM_INT )
                    ),
                    $updateQuery->expr->eq(
                        $connection->quoteColumn( 'contentobject_attribute_version' ),
                        $updateQuery->bindValue( $versionInfo->versionNo, null, \PDO::PARAM_INT )
                    )
                )
            );

        $updateQuery->prepare()->execute();
    }

    /**
     * Stores new field data
     *
     * @param VersionInfo $versionInfo
     * @param Field $field
     *
     * @return void
     */
    protected function storeNewFieldData( VersionInfo $versionInfo, Field $field )
    {
        $connection = $this->getConnection();

        $insertQuery = $connection->createInsertQuery();
        $insertQuery->insertInto( $connection->quoteTable( 'ezxgis_position' ) )
            ->set(
                $connection->quoteColumn( 'latitude' ),
                $insertQuery->bindValue( $field->value->externalData['latitude'] )
            )->set(
                $connection->quoteColumn( 'longitude' ),
                $insertQuery->bindValue( $field->value->externalData['longitude'] )
            )->set(
                $connection->quoteColumn( 'street' ),
                $insertQuery->bindValue( $field->value->externalData['street'] )
            )->set(
                $connection->quoteColumn( 'zip' ),
                $insertQuery->bindValue( $field->value->externalData['zip'] )
            )->set(
                $connection->quoteColumn( 'district' ),
                $insertQuery->bindValue( $field->value->externalData['district'] )
            )->set(
                $connection->quoteColumn( 'city' ),
                $insertQuery->bindValue( $field->value->externalData['city'] )
            )->set(
                $connection->quoteColumn( 'state' ),
                $insertQuery->bindValue( $field->value->externalData['state'] )
            )->set(
                $connection->quoteColumn( 'country' ),
                $insertQuery->bindValue( $field->value->externalData['country'] )
            )->set(
                $connection->quoteColumn( 'accurate' ),
                $insertQuery->bindValue( $field->value->externalData['accurate'] )
            )->set(
                $connection->quoteColumn( 'contentobject_attribute_id' ),
                $insertQuery->bindValue( $field->id, null, \PDO::PARAM_INT )
            )->set(
                $connection->quoteColumn( 'contentobject_attribute_version' ),
                $insertQuery->bindValue( $versionInfo->versionNo, null, \PDO::PARAM_INT )
            );

        $insertQuery->prepare()->execute();
    }

    /**
     * Sets the loaded field data into $field->externalData.
     *
     * @param VersionInfo $versionInfo
     * @param Field $field
     *
     * @return array
     */
    public function getFieldData( VersionInfo $versionInfo, Field $field )
    {
        $field->value->externalData = $this->loadFieldData( $field->id, $versionInfo->versionNo );
        $field->value->data = $this->loadFieldRelationData( $field->id, $versionInfo->versionNo );
    }

    /**
     * Returns the data for the given $fieldId
     *
     * If no data is found, null is returned.
     *
     * @param int $fieldId
     *
     * @return array|null
     */
    protected function loadFieldData( $fieldId, $versionNo )
    {
        $connection = $this->getConnection();

        $selectQuery = $connection->createSelectQuery();
        $selectQuery->select(
            $connection->quoteColumn( 'latitude' ),
            $connection->quoteColumn( 'longitude' ),
            $connection->quoteColumn( 'street' ),
            $connection->quoteColumn( 'zip' ),
            $connection->quoteColumn( 'district' ),
            $connection->quoteColumn( 'city' ),
            $connection->quoteColumn( 'state' ),
            $connection->quoteColumn( 'country' ),
            $connection->quoteColumn( 'accurate' )
        )->from(
            $connection->quoteTable( 'ezxgis_position' )
        )->where(
            $selectQuery->expr->lAnd(
                $selectQuery->expr->eq(
                    $connection->quoteColumn( 'contentobject_attribute_id' ),
                    $selectQuery->bindValue( $fieldId, null, \PDO::PARAM_INT )
                ),
                $selectQuery->expr->eq(
                    $connection->quoteColumn( 'contentobject_attribute_version' ),
                    $selectQuery->bindValue( $versionNo, null, \PDO::PARAM_INT )
                )
            )
        );

        $statement = $selectQuery->prepare();
        $statement->execute();

        $rows = $statement->fetchAll( \PDO::FETCH_ASSOC );

        if ( !isset( $rows[0] ) )
        {
            return null;
        }

        // Cast coordinates as the DB can return them as strings
        $rows[0]["latitude"] = (float)$rows[0]["latitude"];
        $rows[0]["longitude"] = (float)$rows[0]["longitude"];
        $rows[0]["accurate"] = (boolean)$rows[0]["accurate"];

        return $rows[0];
    }

    /**
     * Returns the destinationContentId for the given $fieldId
     *
     * If no data is found, null is returned.
     *
     * @param int $fieldId
     *
     * @return array|null
     */
    protected function loadFieldRelationData( $fieldId, $versionNo )
    {
        $connection = $this->getConnection();

        $selectQuery = $connection->createSelectQuery();
        $selectQuery->select(
            $connection->quoteColumn( 'data_int' )
            )->from(
                $connection->quoteTable( 'ezcontentobject_attribute' )
                )->where(
                    $selectQuery->expr->lAnd(
                        $selectQuery->expr->eq(
                            $connection->quoteColumn( 'id' ),
                            $selectQuery->bindValue( $fieldId, null, \PDO::PARAM_INT )
                            ),
                        $selectQuery->expr->eq(
                            $connection->quoteColumn( 'version' ),
                            $selectQuery->bindValue( $versionNo, null, \PDO::PARAM_INT )
                            )
                        )
                    );

                $statement = $selectQuery->prepare();
                $statement->execute();

                $rows = $statement->fetchAll( \PDO::FETCH_ASSOC );

                if ( !isset( $rows[0] ) )
                {
                    return null;
                }

                // Cast coordinates as the DB can return them as strings
                $rows[0]["data_int"] = (int)$rows[0]["data_int"];

                return $rows[0];
    }

    /**
     * Returns if field data exists for $fieldId
     *
     * @param int $fieldId
     * @param int $versionNo
     *
     * @return boolean
     */
    protected function hasFieldData( $fieldId, $versionNo )
    {
        return ( $this->loadFieldData( $fieldId, $versionNo ) !== null );
    }

    /**
     * Deletes the data for all given $fieldIds
     *
     * @param VersionInfo $versionInfo
     * @param array $fieldIds
     *
     * @return void
     */
    public function deleteFieldData( VersionInfo $versionInfo, array $fieldIds )
    {
        if ( empty( $fieldIds ) )
        {
            // Nothing to do
            return;
        }

        $connection = $this->getConnection();

        $deleteQuery = $connection->createDeleteQuery();
        $deleteQuery->deleteFrom(
            $connection->quoteTable( 'ezxgis_position' )
        )->where(
            $deleteQuery->expr->lAnd(
                $deleteQuery->expr->in(
                    $connection->quoteColumn( 'contentobject_attribute_id' ),
                    $fieldIds
                ),
                $deleteQuery->expr->eq(
                    $connection->quoteColumn( 'contentobject_attribute_version' ),
                    $deleteQuery->bindValue( $versionInfo->versionNo, null, \PDO::PARAM_INT )
                )
            )
        );

        $deleteQuery->prepare()->execute();
    }
}
