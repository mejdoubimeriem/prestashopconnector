<?php

namespace Pim\Bundle\PrestashopConnectorBundle\Mapper;

use Pim\Bundle\PrestashopConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\PrestashopConnectorBundle\Validator\Constraints\HasValidCredentialsValidator;
use Pim\Bundle\PrestashopConnectorBundle\Webservice\RestCallException;

/**
 * Prestashop category mapper.
 *
 */
class PrestashopCategoryMapper extends PrestashopMapper
{
    /** @staticvar int */
    const ROOT_CATEGORY_ID = 1;

    /** @var WebserviceGuesser */
    protected $webserviceGuesser;

    /**
     * @param HasValidCredentialsValidator $hasValidCredentialsValidator
     * @param WebserviceGuesser            $webserviceGuesser
     */
    public function __construct(
        HasValidCredentialsValidator $hasValidCredentialsValidator,
        WebserviceGuesser $webserviceGuesser
    ) {
        parent::__construct($hasValidCredentialsValidator);

        $this->webserviceGuesser = $webserviceGuesser;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllTargets()
    {
        $targets = [];

        if ($this->isValid()) {
            try {
                $categories = $this->webserviceGuesser->getWebservice($this->clientParameters)->getCategoriesStatus();
            } catch (RestCallException $e) {
                return array();
            }

            foreach ($categories as $categoryId => $category) {
                if ($categoryId != self::ROOT_CATEGORY_ID) {
                    $targets[] = ['id' => $categoryId, 'text' => $category['name']];
                }
            }
        }

        return $targets;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier($rootIdentifier = 'category')
    {
        return parent::getIdentifier($rootIdentifier);
    }
}
