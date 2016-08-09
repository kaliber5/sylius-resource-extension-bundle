<?php
/**
 * Created by PhpStorm.
 * User: andreasschacht
 * Date: 28.07.16
 * Time: 17:57
 */

namespace Kaliber5\SyliusResourceExtensionBundle\SonataAdmin;

/**
 * Class SyliusRepositoryAdminTrait
 *
 * USE THIS TRAIT IF YOUR ENTITY REPOSITORY IS AN
 * INSTANCE OF Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository
 * CAUSE ITS FIND METHOD IS INCOMPATIBLE WITH THE SONATA MODEL MANAGER
 *
 * @package Kaliber5\SyliusResourceExtensionBundle\SonataAdmin
 */
trait SyliusRepositoryAdminTrait
{

    /**
     * @param int $id
     *
     * @return mixed
     */
    public function getObject($id)
    {
        $object = $this->getModelManager()->findOneBy($this->getClass(), ['id' => $id]);
        foreach ($this->getExtensions() as $extension) {
            $extension->alterObject($this, $object);
        }

        return $object;
    }
}
