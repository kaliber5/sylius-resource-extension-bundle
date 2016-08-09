<?php
/**
 * Created by PhpStorm.
 * User: andreasschacht
 * Date: 28.07.16
 * Time: 17:03
 */

namespace Kaliber5\SyliusResourceExtensionBundle\SonataAdmin;


/**
 * Class TranslatableAdminTrait
 *
 * This trait is for admin classes for translatable entities and sets the defaultLocale and fallbackLocale
 * on a new instance
 *
 * @package Kaliber5\SyliusResourceExtensionBundle\SonataAdmin
 */
trait TranslatableAdminTrait
{

    /**
     * @var string
     */
    protected $defaultLocale = 'de_DE';

    /**
     * @var string
     */
    protected $fallbackLocale = 'de';

    /**
     * @return string
     */
    public function getDefaultLocale()
    {
        return $this->defaultLocale;
    }

    /**
     * @param string $defaultLocale
     */
    public function setDefaultLocale($defaultLocale)
    {
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * @return string
     */
    public function getFallbackLocale()
    {
        return $this->fallbackLocale;
    }

    /**
     * @param string $fallbackLocale
     */
    public function setFallbackLocale($fallbackLocale)
    {
        $this->fallbackLocale = $fallbackLocale;
    }

    /**
     * @return mixed
     */
    public function getNewInstance()
    {
        $object = parent::getNewInstance();
        $object->setCurrentLocale($this->defaultLocale);
        $object->setFallbackLocale($this->fallbackLocale);

        return $object;
    }
}
