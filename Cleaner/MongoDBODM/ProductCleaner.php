<?php

namespace Pim\Bundle\PrestashopConnectorBundle\Cleaner\MongoDBODM;

use Doctrine\ODM\MongoDB\Query\Builder;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\PrestashopConnectorBundle\Cleaner\AbstractProductCleaner;
use Pim\Bundle\PrestashopConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\PrestashopConnectorBundle\Webservice\PrestashopRestClientParametersRegistry;

/**
 * Prestashop product cleaner for MongoDB.
 *
 */
class ProductCleaner extends AbstractProductCleaner
{
    /** @var AttributeRepository */
    protected $attributeRepository;

    /**
     * @param WebserviceGuesser                   $webserviceGuesser
     * @param PrestashopRestClientParametersRegistry $clientParametersRegistry
     * @param ChannelManager                      $channelManager
     * @param ProductManager                      $productManager
     * @param AttributeRepository                 $attributeRepository
     */
    public function __construct(
        WebserviceGuesser $webserviceGuesser,
        PrestashopRestClientParametersRegistry $clientParametersRegistry,
        ChannelManager $channelManager,
        ProductManager $productManager,
        AttributeRepository $attributeRepository
    ) {
        parent::__construct($webserviceGuesser, $clientParametersRegistry, $channelManager, $productManager);

        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function getExportedProductsSkus()
    {
        $identifierCode = $this->getIdentifierAttributeCode();

        $qb = $this->productManager->getProductRepository()
            ->buildByChannelAndCompleteness($this->getChannelByCode())
            ->select([sprintf("normalizedData.%s", $identifierCode)]);

        return $this->getProductsSkus($qb, $identifierCode);
    }

    /**
     * {@inheritdoc}
     */
    protected function getPimProductsSkus()
    {
        $identifierCode = $this->getIdentifierAttributeCode();
        $qb = $this->productManager->getProductRepository()->createQueryBuilder('p');

        $qb
            ->addAnd($qb->expr()->field('enabled')->equals(true))
            ->select([sprintf("normalizedData.%s", $identifierCode)]);

        return $this->getProductsSkus($qb, $identifierCode);
    }

    /**
     * {@inheritdoc}
     */
    protected function getProductsSkus(Builder $qb, $identifierCode)
    {
        $results = $qb->hydrate(false)->getQuery()->execute()->toArray();
        $skus = [];
        foreach ($results as $result) {
            $skus[] = $result['normalizedData'][$identifierCode];
        }

        return $skus;
    }

    /**
     * Get the identifier attribute code.
     *
     * @return string
     */
    protected function getIdentifierAttributeCode()
    {
        return $this->attributeRepository->getIdentifierCode();
    }
}
