Kaliber5SyliusResourceExtensionBundle
=====================================

SonataAdmin
-----------

If your EntityRepository extends the

    Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository

you should use the SyliusRepositoryAdminTrait in your Admin class, because the find method is incompatible with the use in the Admin's ModelManager

    <php
    
    use Sonata\AdminBundle\Admin\Admin;
    use Kaliber5\SyliusResourceExtensionBundle\SonataAdmin\SyliusRepositoryAdminTrait;
    
    class ExampleAdmin extends Admin
    {
        use SyliusRepositoryAdminTrait;
        
        //...   
    }


If you entity implements the

    Sylius\Component\Translation\Model\TranslatableInterface
     
you should use the 

    Kaliber5\SyliusResourceExtensionBundle\SonataAdmin\TranslatableAdminTrait
    
in your Admin class to inject a default- and fallbackLocale on new created objects.


ResourceLoader
--------------

This Bundle replace the Sylius ResourceLoader to provide generated URL's compatible with JsonApi. It will remove the trailing slashes from the index routes. 


RequestConfiguration
--------------------

This Bundle replace the Sylius RequestConfiguration class with 

    Kaliber5\SyliusResourceExtensionBundle\Request\JsonApiRequestConfiguration

It provides the `filter` query parameter, like suggested in [JsonApi](http://jsonapi.org/format/#fetching-filtering):

    http://api.example.de/api/users?filter[username]=user1

the filters will be merged with the criteria and applied to the ResourceProviders

Futhermore you'll be able to whitelist your fields in your route configuration under the `defaults._sylius.filter` key:

    //routing.yml
    app_user:
        resource: |
          alias: app.user
          only: ['index', 'show']
        type: sylius.resource_api
        prefix: /api
        defaults:
          _format: json
          _sylius:
            filterable: true
            filter: ['username', 'email']
    
    
The filter key must be an array if used.


SyliusFilterEntityRepository
----------------------------

Your Repository can extend the

    Kaliber5\SyliusResourceExtensionBundle\Repository\SyliusFilterEntityRepository

Then your Repository can apply criteria that contains Json-Data for advanced filtering like:

    http://api.example.de/api/users?filter[age]={">":18, "<=":40}

### The Json object

For numeric values:
```json
     {">":50, "<=":100}
```
This will result in the following query expression: `key > 50 AND key <= 100`

For discrete values:
```json
{"=":["excellent", "good"]}
```
or:
```json
{"<>":["excellent", "good"]}
```
This will result in the following query expression: `key IN ("excellent", "good")`
respectively `key NOT IN ("excellent", "good")`

By default, the criterias will be joined with an "AND" condition, to use an
"OR" condition, use:
```json
{"or":{"<":50, ">=":100}}
```

This will result in the following query expression: `key < 50 OR key >= 100`

Allowed comparison operators: `=, >, >=, <, <=, <>`