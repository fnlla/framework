# Error Handling

## Global handling
`HttpKernel` uses `ExceptionHandler` to handle errors and exceptions. It registers error handlers at runtime and restores them at the end of the request.

## APP_DEBUG
In the starter app, `APP_DEBUG=1` allows a simple error message to be returned. In production, responses are generic.

## Logging
If you provide a logger (PSR-3), errors can be reported to logs. Finella's core logging module provides Monolog integration.

## Error reporting
For external error tracking, bind `Finella\Contracts\Log\ErrorReporterInterface` in your container.
`ExceptionHandler` calls it alongside the logger.

## Recommendations
- Never expose stack traces in production.
- Capture errors centrally and keep logs for diagnostics.
