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
        $filters = [];
        if ($this->isFilterable()) {
            $filters = $this->convertFilters($this->getRequestParameter('filter', []));
            $filterfields = $this->getFilterFields();
            if (!empty($filterfields)) {
                $allowed = array_filter(
                    $filters,
                    function ($field) use ($filterfields) {
                        return in_array($field, $filterfields);
                    },
                    ARRAY_FILTER_USE_KEY
                );
                $filters = $allowed;
            }
        }
        $criteria = array_merge($filters, $criteria);

        return $criteria;
    }

    /**
     * @return bool
     */
    public function isFilterable()
    {
        return $this->getParameters()->get('filterable', true);
    }

    /**
     * Use the JSONAPI specs for sorting, use the sort param
     *
     * Sylius sort style:  ...?sorting[name]=ASC&sorting[age]=DESC
     * JSONAPI sort style: ...?sort=name,-age
     *
     * @param array $sorting
     *
     * @return array
     */
    public function getSorting(array $sorting = [])
    {
        $defaultSorting = array_merge($this->getParameters()->get('sorting', []), $sorting);

        if ($this->isSortable()) {
            $sorting = $this->convertSorting($this->getRequest()->get('sort'));
            foreach ($defaultSorting as $key => $value) {
                if (!isset($sorting[$key])) {
                    $sorting[$key] = $value;
                }
            }

            return $sorting;
        }

        return $defaultSorting;
    }

    /**
     * @return bool
     */
    public function isCsrfProtectionEnabled()
    {
        return $this->getParameters()->get('csrf_protection', false);
    }

    /**
     * Converts the JSONAPI sort to the Sylius style
     *
     * Sylius sort style:  ...?sorting[name]=ASC&sorting[age]=DESC
     * JSONAPI sort style: ...?sort=name,-age
     *
     * @param string $jsonApiSort
     *
     * @return array Sortingarray with [property => sorting,..]
     */
    protected function convertSorting($jsonApiSort)
    {
        $syliusSort = [];
        if (is_string($jsonApiSort)) {
            $keys = explode(',', $jsonApiSort);
            foreach ($keys as $key) {
                if (empty($key)) {
                    continue;
                }
                if ($key[0] === '-') {
                    $key = substr($key, 1);
                    $syliusSort[$key] = 'DESC';
                } else {
                    $syliusSort[$key] = 'ASC';
                }
            }
        }

        return $syliusSort;
    }

    /**
     * convert the filter value to an array if it's comma separated
     *
     * @param array $filter
     *
     * @return array
     */
    protected function convertFilters(array $filter): array
    {
        foreach ($filter as $key => $value) {
            if (preg_match("#^{.*}$#", $value) === 0 && strpos($value, ',') !== false) {
                $filter[$key] = explode(',', $value);
            }
        }

        return $filter;
    }

    /**
     * returns the available filter fields or the 'id'-field by default
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function getFilterFields()
    {
        $filterFields = $this->getParameters()->get('filter', ['id']);
        Assert::isArray($filterFields, 'wrong route configuration: defaults._sylius.filter must be an array');

        return $filterFields;
    }
}
