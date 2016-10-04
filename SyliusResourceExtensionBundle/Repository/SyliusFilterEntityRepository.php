<?php
/**
 * Created by PhpStorm.
 * User: andreasschacht
 * Date: 10.08.16
 * Time: 10:56
 */

namespace Kaliber5\SyliusResourceExtensionBundle\Repository;

use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\QueryBuilder;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Webmozart\Assert\Assert;

/**
 * Class SyliusFilterEntityRepository
 *
 * @package AppBundle\Repository
 */
class SyliusFilterEntityRepository extends EntityRepository
{
    const EXPRESSION_AND = 'and';
    const EXPRESSION_OR = 'or';
    /**
     * @param QueryBuilder $queryBuilder
     * @param array        $criteria
     */
    protected function applyCriteria(QueryBuilder $queryBuilder, array $criteria = [])
    {
        foreach ($criteria as $property => $value) {
            $this->assertValidFieldName($property);
            $name = $this->getPropertyName($property);
            if (preg_match("#^{.*}$#", $value)) {
                $hash = json_decode($value, true);
                Assert::notEmpty($hash, 'Bad or empty JSON');
                $expr = $this->getExpressionFromHash($name, $hash);
                if ($expr->count() > 0) {
                    $queryBuilder->andWhere($expr);
                }
            } else {
                parent::applyCriteria($queryBuilder, [$property => $value]);
            }
        }
    }

    /**
     * Evaluate the hash, return an expression with all comparisions defined in the hash
     *
     * Use like this for numeric values:
     * ```json
     * {">":50, "<=":100}
     * ```
     * This will result in the following query expression: `key > 50 AND key <= 100`
     *
     * Use like this for discrete values:
     * ```json
     * {"=":["excellent", "good"]}
     * ```
     * or:
     * ```json
     * {"!=":["excellent", "good"]}
     * ```
     * This will result in the following query expression: `key IN ("excellent", "good")`
     * respectively `key NOT IN ("excellent", "good")`
     *
     * By default, the criterias will be joined with an "AND" condition, to use an
     * "OR" condition, use:
     * ```json
     * {"or":{"<":50, ">=":100}}
     * ```
     *
     * This will result in the following query expression: `key < 50 OR key >= 100`
     *
     * Allowed comparison operators: =, >, >=, <, <=, <>
     *
     * @param string $key
     * @param string $hash
     *
     * @return \Doctrine\ORM\Query\Expr\Andx
     * @throws \InvalidArgumentException
     */
    protected function getExpressionFromHash($key, $hash, $expression = 'and')
    {
        $allowedOperators = [
            Comparison::EQ,
            Comparison::GT,
            Comparison::GTE,
            Comparison::LT,
            Comparison::LTE,
            Comparison::NEQ,
        ];
        $allowedExpressions = [
            self::EXPRESSION_AND,
            self::EXPRESSION_OR,
        ];
        if (!in_array($expression, $allowedExpressions)) {
            throw new \InvalidArgumentException('Unknown expression "'.$expression);
        }
        $expClassname = 'Doctrine\\ORM\\Query\\Expr\\'.ucfirst($expression).'x';
        /** @var Andx $expClass */
        $expClass = new $expClassname();
        foreach ($hash as $comparator => $value) {
            if (in_array($comparator, $allowedExpressions) && is_array($value)) {
                $expClass->add($this->getExpressionFromHash($key, $value, $comparator));
                continue;
            }
            Assert::oneOf($comparator, $allowedOperators, 'Unknown comparison operator "'.$comparator);
            if ($comparator === Comparison::EQ && is_array($value)) {
                if (empty($value)) {
                    // if array is empty, build expression which is always false
                    $part = $this->getEntityManager()->getExpressionBuilder()->eq(1, 0);
                } else {
                    $part = $this->getEntityManager()->getExpressionBuilder()->in($key, $value);
                }
            } elseif ($comparator === Comparison::NEQ && is_array($value)) {
                if (empty($value)) {
                    // if array is empty, build expression which is always true
                    $part = $this->getEntityManager()->getExpressionBuilder()->eq(1, 1);
                } else {
                    $part = $this->getEntityManager()->getExpressionBuilder()->notIn($key, $value);
                }
            } else {
                $part = new Comparison($key, $comparator, $value);
            }

            $expClass->add($part);
        }

        return $expClass;
    }

    /**
     * Check if $field is a valid property of the entity.
     *
     * Throws exception if field is not valid.
     *
     * @param string $field
     *
     * @throws \InvalidArgumentException
     */
    protected function assertValidFieldName($field)
    {
        $classMetadata = $this->getClassMetadata();
        Assert::true($classMetadata->hasField($field), 'Unknown field: '.$field);
    }
}
