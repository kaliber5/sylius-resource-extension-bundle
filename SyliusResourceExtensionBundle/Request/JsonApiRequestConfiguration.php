<?php
/**
 * Created by PhpStorm.
 * User: andreasschacht
 * Date: 10.08.16
 * Time: 11:54
 */

namespace Kaliber5\SyliusResourceExtensionBundle\Request;

use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;

/**
 * Class JsonApiRequestConfiguration
 *
 * @package AppBundle\Request
 */
class JsonApiRequestConfiguration extends RequestConfiguration
{

    /**
     * Add the filter parameter as criteria, see JsonApi
     *
     * @param array $criteria
     *
     * @return array
     */
    public function getCriteria(array $criteria = [])
    {
        $criteria = parent::getCriteria($criteria);
        if ($this->isFilterable()) {
            $filters = $this->getRequestParameter('filter', []);
            $criteria = array_merge($filters, $criteria);
        }

        return $criteria;
    }
}