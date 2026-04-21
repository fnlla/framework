**EXTENSIONS**

Extensions are Composer packages that integrate with fnlla (finella) via service providers.

**CREATING A PACKAGE**
**-** Create a Composer package.
**-** Add PSR-4 autoloading for your namespace.
**-** Implement a service provider extending `Finella\Support\ServiceProvider`.
**-** Add providers to `extra.finella.providers`.

Example `composer.json`:
```json
{
  "name": "finella/acme-example",
  "type": "library",
  "require": {
    "php": ">=8.5",
    "finella/framework": "^3.0"
  },
  "autoload": {
    "psr-4": {
      "Finella\\Acme\\": "src/"
    }
  },
  "extra": {
    "fnlla (finella)": {
      "providers": [
        "Finella\\Acme\\AcmeServiceProvider"
      ]
    }
  }
}
```

**VERSIONING**
Follow SemVer and keep compatibility with the framework major version.
