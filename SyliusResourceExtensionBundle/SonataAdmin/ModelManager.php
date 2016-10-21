<?php
namespace Kaliber5\SyliusResourceExtensionBundle\SonataAdmin;

use Sonata\DoctrineORMAdminBundle\Model\ModelManager as BaseModelManager;

/**
 * Class ModelManager
 *
 * This ModelManager replace the find method to aware compatibility with Sylius Repository (v0.17)
 *
 * @package Kaliber5\SyliusResourceExtensionBundle\SonataAdmin
 */
class ModelManager extends BaseModelManager
{

    /**
     * {@inheritdoc}
     */
    public function find($class, $id)
    {
        if (!isset($id)) {
            return;
        }

        $values = array_combine($this->getIdentifierFieldNames($class), explode(self::ID_SEPARATOR, $id));

        return $this->getEntityManager($class)->getRepository($class)->findOneBy($values);
    }
}
