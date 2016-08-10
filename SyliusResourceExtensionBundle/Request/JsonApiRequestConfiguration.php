<?php
/**
 * Created by PhpStorm.
 * User: andreasschacht
 * Date: 10.08.16
 * Time: 11:54
 */

namespace Kaliber5\SyliusResourceExtensionBundle\Request;

use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Webmozart\Assert\Assert;

/**
 * Class JsonApiRequestConfiguration
 *
 * @package AppBundle\Request
 */
class JsonApiRequestConfiguration extends RequestConfiguration
{

    /**
     * Add the filter query parameter as criteria, e.g. ...&filter[name]=... see JsonApi
     *
     * @param array $criteria
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getCriteria(array $criteria = [])
    {
        $criteria = parent::getCriteria($criteria);
        if ($this->isFilterable()) {
            $filters = $this->getRequestParameter('filter', []);
            $criteria = array_merge($filters, $criteria);
            $filterfields = $this->getFilterFields();
            if (!empty($filterfields)) {
                $allowed = array_filter(
                    $criteria,
                    function ($field) use ($filterfields) {
                        return in_array($field, $filterfields);
                    },
                    ARRAY_FILTER_USE_KEY
                );
                $criteria = $allowed;
            }
        }

        return $criteria;
    }

    /**
     * returns the available filter fields or empty array if the fields weren't restricted
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function getFilterFields()
    {
        $filterFields = $this->parameters->get('filter', []);
        Assert::isArray($filterFields, 'wrong route configuration: defaults._sylius.filter must be an array');

        return $filterFields;
    }
}