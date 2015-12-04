<?php

namespace Pim\Bundle\PrestashopConnectorBundle\Entity;

use Akeneo\Bundle\BatchBundle\Entity\JobInstance;

/**
 * Delta configurable export entity.
 *
 */
class DeltaConfigurableExport
{
    /** @var int */
    protected $id;

    /** @var \DateTime */
    protected $lastExport;

    /** @var string|int */
    protected $productId;

    /** @var JobInstance */
    protected $jobInstance;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \DateTime $lastExport
     *
     * @return DeltaConfigurableExport
     */
    public function setLastExport(\DateTime $lastExport)
    {
        $this->lastExport = $lastExport;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastExport()
    {
        return $this->lastExport;
    }

    /**
     * @param string|int $productId
     *
     * @return DeltaConfigurableExport
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;

        return $this;
    }

    /**
     * @return string|int
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * @param JobInstance $jobInstance
     *
     * @return DeltaConfigurableExport
     */
    public function setJobInstance(JobInstance $jobInstance = null)
    {
        $this->jobInstance = $jobInstance;

        return $this;
    }

    /**
     * @return JobInstance
     */
    public function getJobInstance()
    {
        return $this->jobInstance;
    }
}
