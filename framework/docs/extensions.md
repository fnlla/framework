# Extensions

Extensions are Composer packages that integrate with Finella via service providers.

## Creating a package
1. Create a Composer package.
2. Add PSR-4 autoloading for your namespace.
3. Implement a service provider extending `Finella\Support\ServiceProvider`.
4. Add providers to `extra.finella.providers`.

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
    "finella": {
      "providers": [
        "Finella\\Acme\\AcmeServiceProvider"
      ]
    }
  }
}
```

## Versioning
Follow SemVer and keep compatibility with the framework major version.
