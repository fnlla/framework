**FNLLA (FINELLA) STANDARD**

fnlla (finella) Standard is a meta-package that installs the full default web stack.
It contains no runtime code and only aggregates official packages.

**INCLUDED PACKAGES**
**-** `finella/framework`
**-** `finella/ops`
**-** `finella/rbac`
**-** `finella/settings`
**-** `finella/audit`
**-** `finella/deploy`

Development-only:
**-** `finella/debugbar` (require-dev)
**-** `finella/testing` (require-dev)

**INSTALLATION**
```bash
composer require finella/standard
```

**PROVIDER DISCOVERY**
All included packages expose their providers via `extra.finella.providers`.
fnlla (finella) auto-discovery will register them automatically once the dependencies are installed.

**LICENSE**
Proprietary
