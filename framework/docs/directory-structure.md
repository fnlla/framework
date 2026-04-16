# Directory Structure

## Framework package
```
framework/
|-- src/
|-- docs/
|-- LICENSE
|-- README.md
|-- CREDITS.md
|-- composer.json
```

## Typical application
```
app/
|-- public/
|-- bootstrap/
|-- config/
|-- routes/
|-- resources/
|-- storage/
|-- vendor/
|-- composer.json
|-- .env
```

## Public vs private
- Public: `public/` only.
- Private: `config/`, `storage/`, `vendor/`, `bootstrap/`.

## Deployable artefacts
- Application code
- Composer dependencies installed on the server
- Config and environment variables
