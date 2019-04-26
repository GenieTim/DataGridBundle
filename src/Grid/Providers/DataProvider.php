<?php


namespace Pfilsx\DataGrid\Grid\Providers;


use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Pfilsx\DataGrid\DataGridException;
use Pfilsx\DataGrid\Grid\Pager;

abstract class DataProvider implements DataProviderInterface
{
    /**
     * @var Pager
     */
    protected $pager;

    protected $countFieldName;

    /**
     * @internal
     * @return Pager
     */
    public function getPager(): Pager
    {
        return $this->pager;
    }

    /**
     * @internal
     * @param Pager $pager
     */
    public function setPager(Pager $pager): void
    {
        $this->pager = $pager;
    }

    public function setCountFieldName(string $name): DataProviderInterface
    {
        $this->countFieldName = $name;
        return $this;
    }

    public function getCountFieldName(): string
    {
        return $this->countFieldName;
    }

    public function addEqualFilter(string $attribute, $value): DataProviderInterface
    {
        throw new DataGridException("Method addEqualFilter() is not supported in " . static::class);
    }

    public function addLikeFilter(string $attribute, $value): DataProviderInterface
    {
        throw new DataGridException("Method addLikeFilter() is not supported in " . static::class);
    }

    public function addRelationFilter(string $attribute, $value, string $relationClass): DataProviderInterface
    {
        throw new DataGridException("Method addRelationFilter() is not supported in " . static::class);
    }

    public function addCustomFilter(string $attribute, $value, callable $callback): DataProviderInterface
    {
        throw new DataGridException("Method addCustomFilter() is not supported in " . static::class);
    }

    public function addDateFilter(string $attribute, $value, string $comparison = 'equal'): DataProviderInterface
    {
        $comparisonFunc = lcfirst($comparison) . 'Date';
        if (method_exists($this, $comparisonFunc)) {
            $this->$comparisonFunc($attribute, $value);
        } else {
            $this->equalDate($attribute, $value);
        }
        return $this;
    }

    /**
     * @param $attribute
     * @param $value
     */
    protected function equalDate($attribute, $value): void
    {
        throw new DataGridException("Method equalDate() is not supported in " . static::class);
    }


    public static function create($data, EntityManager $doctrine = null): DataProviderInterface
    {
        if ($data instanceof EntityRepository && $doctrine !== null) {
            return new RepositoryDataProvider($data, $doctrine);
        }
        if ($data instanceof QueryBuilder && $doctrine !== null) {
            return new QueryBuilderDataProvider($data, $doctrine);
        }
        if (is_array($data)) {
            return new ArrayDataProvider($data);
        }
        throw new DataGridException('Provided data must be one of: ' . implode(', ', [
                ServiceEntityRepository::class,
                QueryBuilder::class,
                'Array'
            ]) . ', ' . (($type = gettype($data)) == 'object' ? get_class($data) : $type) . ' given');
    }
}