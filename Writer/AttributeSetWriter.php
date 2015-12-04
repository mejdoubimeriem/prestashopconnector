<?php

namespace Pim\Bundle\PrestashopConnectorBundle\Writer;

use Pim\Bundle\PrestashopConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\PrestashopConnectorBundle\Manager\AttributeMappingManager;
use Pim\Bundle\PrestashopConnectorBundle\Manager\FamilyMappingManager;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\PrestashopConnectorBundle\Webservice\RestCallException;
use Pim\Bundle\PrestashopConnectorBundle\Webservice\PrestashopRestClientParametersRegistry;

/**
 * Prestashop attribute set writer.
 *
 */
class AttributeSetWriter extends AbstractWriter
{
    /** @var FamilyMappingManager */
    protected $familyMappingManager;

    /** @var AttributeMappingManager */
    protected $attributeMappingManager;

    /**
     * @param WebserviceGuesser                   $webserviceGuesser
     * @param FamilyMappingManager                $familyMappingManager
     * @param AttributeMappingManager             $attributeMappingManager
     * @param PrestashopRestClientParametersRegistry $clientParametersRegistry
     */
    public function __construct(
        WebserviceGuesser $webserviceGuesser,
        FamilyMappingManager $familyMappingManager,
        AttributeMappingManager $attributeMappingManager,
        PrestashopRestClientParametersRegistry $clientParametersRegistry
    ) {
        parent::__construct($webserviceGuesser, $clientParametersRegistry);

        $this->attributeMappingManager = $attributeMappingManager;
        $this->familyMappingManager    = $familyMappingManager;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        $this->beforeExecute();
        foreach ($items as $item) {
            try {
                $this->handleNewFamily($item);
            } catch (RestCallException $e) {
                $this->stepExecution->incrementSummaryInfo('family_exists');
            }
        }
    }

    /**
     * Handle family creation.
     *
     * @param array $item
     *
     * @throws InvalidItemException
     */
    protected function handleNewFamily(array $item)
    {
        if (isset($item['families_to_create'])) {
            $pimFamily       = $item['family_object'];
            $prestashopFamilyId = $this->webservice->createAttributeSet($item['families_to_create']['attributeSetName']);
            $prestashopUrl      = $this->getPrestashopUrl();
            $this->familyMappingManager->registerFamilyMapping(
                $pimFamily,
                $prestashopFamilyId,
                $prestashopUrl
            );
            $this->stepExecution->incrementSummaryInfo('family_created');
        }
    }
}
