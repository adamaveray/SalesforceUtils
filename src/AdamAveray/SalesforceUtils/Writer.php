<?php
namespace AdamAveray\SalesforceUtils;

use AdamAveray\SalesforceUtils\Exceptions\SaveFailureException;
use AdamAveray\SalesforceUtils\Client\ClientInterface;
use Phpforce\SoapClient\Result\SaveResult;
use Phpforce\SoapClient\Result\SObject;

class Writer
{
    const FIELD_RECORD_TYPE_ID = 'RecordTypeId';

    /** @var ClientInterface $client */
    private $client;

    /**
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $type The type of object to create
     * @param array|object $values The values for the object, or the object itself
     * @param string|null $recordTypeId The ID for the record type to set on the object, or null to leave as default
     * @return SaveResult
     * @throws SaveFailureException The create was unsuccessful
     */
    public function create(
        string $type,
        $values,
        ?string $recordTypeId = null
    ): SaveResult {
        if (is_object($values)) {
            $object = $values;
        } else {
            $object = (object) $values;
        }

        if ($recordTypeId !== null) {
            // Set record type
            $object->{self::FIELD_RECORD_TYPE_ID} = $recordTypeId;
        }

        $result = $this->client->createOne($object, $type);
        return $this->handleResult($result);
    }

    /**
     * @param string $type The type of object to update
     * @param SObject|string $base The base object or object ID
     * @param array $updates The values to update
     * @return SaveResult
     * @throws SaveFailureException The update was unsuccessful
     */
    public function update(string $type, $base, array $updates): SaveResult
    {
        if ($base instanceof SObject) {
            $baseId = $base->getId();
        } else {
            $baseId = (string) $base;
        }

        $object = $this->buildSObject($baseId, $updates);
        $result = $this->client->updateOne($object, $type);
        return $this->handleResult($result);
    }

    /**
     * @param string $id The ID for the object
     * @param array $values Additional values to assign to the object
     * @return SObject
     */
    public function buildSObject(?string $id, array $values): SObject
    {
        $object = new SObject();
        if ($id !== null) {
            $object->Id = $id;
        }
        foreach ($values as $key => $value) {
            if (is_object($value)) {
                $value = (string) $value;
            }
            $object->{$key} = $value;
        }
        return $object;
    }

    /**
     * @param SaveResult $result The result to handle
     * @return SaveResult
     * @throws SaveFailureException The given $result was not successful
     */
    private function handleResult(SaveResult $result): SaveResult
    {
        if (!$result->isSuccess()) {
            throw new SaveFailureException($result);
        }
        return $result;
    }
}
