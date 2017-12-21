INN + KPP validation for ORM Entity
=======================================================

Install 
-------
composer require laxcorp/inn-kpp-bundle

Add in app/AppKernel.php
------------------------
```php
$bundles = [
    new LaxCorp\InnKppBundle\InnKppBundle()
]
```

Use in Entity
-------------
```
use LaxCorp\InnKppBundle\Validator\Constraints\InnKppEntity;
```

```php
/**
 *
 * @ORM\Entity
 *
 * @InnKppEntity(
 *     fieldInn="inn",
 *     fieldKpp="kpp",
 *     ignoreNull=true
 * )
 */
 class ...
```

Example AppBundle/Entity/Company.php
---------------------------------
```php
<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use LaxCorp\InnKppBundle\Validator\Constraints\InnKppEntity;

/**
 *
 * @ORM\Entity
 *
 * @InnKppEntity(
 *     fieldInn="inn",
 *     fieldKpp="kpp",
 *     ignoreNull=true
 * )
 */
class Company
{

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $inn;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $kpp;
    
    /**
     * Set inn
     *
     * @param string $inn
     *
     * @return Company
     */
    public function setInn($inn)
    {
        $this->inn = $inn;

        return $this;
    }

    /**
     * Get inn
     *
     * @return string
     */
    public function getInn()
    {
        return $this->inn;
    }

    /**
     * Set kpp
     *
     * @param string $kpp
     *
     * @return Company
     */
    public function setKpp($kpp)
    {
        $this->kpp = $kpp;

        return $this;
    }

    /**
     * Get kpp
     *
     * @return string
     */
    public function getKpp()
    {
        return $this->kpp;
    }
    
}
````
