<?php

namespace Pim\Bundle\PrestashopConnectorBundle\Writer;

use Pim\Bundle\PrestashopConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\PrestashopConnectorBundle\Manager\CategoryMappingManager;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\PrestashopConnectorBundle\Webservice\RestCallException;
use Pim\Bundle\PrestashopConnectorBundle\Webservice\PrestashopRestClientParametersRegistry;
use Pim\Bundle\PrestashopConnectorBundle\Webservice\Webservice;

/**
 * Prestashop category writer.
 *
 */
class CategoryWriter extends AbstractWriter
{
    /** @var CategoryMappingManager */
    protected $categoryMappingManager;

    /**
     * @param WebserviceGuesser                   $webserviceGuesser
     * @param CategoryMappingManager              $categoryMappingManager
     * @param PrestashopRestClientParametersRegistry $clientParametersRegistry
     */
    public function __construct(
        WebserviceGuesser $webserviceGuesser,
        CategoryMappingManager $categoryMappingManager,
        PrestashopRestClientParametersRegistry $clientParametersRegistry
    ) {
        parent::__construct($webserviceGuesser, $clientParametersRegistry);

        $this->categoryMappingManager = $categoryMappingManager;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $batches)
    {
        $this->beforeExecute();

        //creation for each product in the admin storeView (with default locale)
        foreach ($batches as $batch) {
            try {
                $this->handleNewCategory($batch);
                $this->handleUpdateCategory($batch);
                $this->handleMoveCategory($batch);
                $this->handleVariationCategory($batch);
            } catch (RestCallException $e) {
                throw new InvalidItemException($e->getMessage(), []);
            }
        }
    }

    /**
     * Handle category creation.
     *
     * @param array $batch
     */
    protected function handleNewCategory(array $batch)
    {
        if (isset($batch['create'])) {
            foreach ($batch['create'] as $newCategory) {
                $pimCategory       = $newCategory['pimCategory'];
                $prestashopCategoryId = $this->webservice->sendNewCategory($newCategory['prestashopCategory']);
                $prestashopUrl        = $this->getPrestashopUrl();

                $this->categoryMappingManager->registerCategoryMapping(
                    $pimCategory,
                    $prestashopCategoryId,
                    $prestashopUrl
                );

                $this->stepExecution->incrementSummaryInfo('category_created');
            }
        }
    }

    /**
     * Handle category update.
     *
     * @param array $batch
     */
    protected function handleUpdateCategory(array $batch)
    {
        if (isset($batch['update'])) {
            foreach ($batch['update'] as $categoryToUpdate) {
                $this->webservice->sendUpdateCategory($categoryToUpdate);

                $storeViewList = $this->webservice->getStoreViewsList();
                if (count($storeViewList) > 1) {
                    $this->updateAdminStoreView($categoryToUpdate);
                }

                $this->stepExecution->incrementSummaryInfo('category_updated');
            }
        }
    }

    /**
     * Update category in admin store view.
     *
     * @param array $categoryToUpdate
     */
    protected function updateAdminStoreView(array $categoryToUpdate)
    {
        $categoryToUpdate[2] = Webservice::ADMIN_STOREVIEW;
        $this->webservice->sendUpdateCategory($categoryToUpdate);
    }

    /**
     * Handle category move.
     *
     * @param array $batch
     */
    protected function handleMoveCategory(array $batch)
    {
        if (isset($batch['move'])) {
            foreach ($batch['move'] as $moveCategory) {
                $this->webservice->sendMoveCategory($moveCategory);

                $this->stepExecution->incrementSummaryInfo('category_moved');
            }
        }
    }

    /**
     * Handle category variation update.
     *
     * @param array $batch
     */
    protected function handleVariationCategory(array $batch)
    {
        if (isset($batch['variation'])) {
            foreach ($batch['variation'] as $variationCategory) {
                $pimCategory        = $variationCategory['pimCategory'];
                $prestashopCategoryId  = $this->categoryMappingManager
                    ->getIdFromCategory($pimCategory, $this->getPrestashopUrl());
                $prestashopCategory    = $variationCategory['prestashopCategory'];
                $prestashopCategory[0] = $prestashopCategoryId;

                $this->webservice->sendUpdateCategory($prestashopCategory);

                $this->stepExecution->incrementSummaryInfo('category_translation_sent');
            }
        }
    }
}
