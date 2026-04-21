**FNLLA (FINELLA) API INVENTORY**

> Generated from `framework/src` (public methods + global helpers).
> Some items listed here are optional utilities and may be moved to packages over time.

**CORE**
**-** `Finella\Core\Application` (`src\Core\Application.php`)
public: __construct, basePath, bootProviders, config, configRepository, registerProvider, registerProviders, version
**-** `Finella\Core\ConfigRepository` (`src\Core\ConfigRepository.php`)
public: __construct, all, forget, fromDirectory, fromRoot, get, resolveAppRoot, set
**-** `Finella\Core\ConfigValidator` (`src\Core\ConfigValidator.php`)
public: assertValid, validate
**-** `Finella\Core\Container` (`src\Core\Container.php`)
public: bind, call, configRepository, get, has, instance, make, registerResetter, reset, resetters, scoped, scopedInstance, singleton
**-** `Finella\Core\ContainerException` (`src\Core\ContainerException.php`)
**-** `Finella\Core\Controller` (`src\Core\Controller.php`)
public: __construct
**-** `Finella\Core\ExceptionHandler` (`src\Core\ExceptionHandler.php`)
public: __construct, handleError, handleException, handleShutdown, register, render, report
**-** `Finella\Core\NotFoundException` (`src\Core\NotFoundException.php`)
**-** `Finella\Core\ServiceProvider` (`src\Core\ServiceProvider.php`)
public: __construct, boot, register

**HTTP**
**-** `Finella\Contracts\Http\KernelInterface` (`src\Contracts\Http\KernelInterface.php`)
public: handle
**-** `Finella\Http\HttpFactory` (`src\Http\HttpFactory.php`)
public: createRequest, createResponse, createServerRequest, createStream, createStreamFromFile, createStreamFromResource, createUploadedFile, createUri
**-** `Finella\Http\HttpKernel` (`src\Http\HttpKernel.php`)
public: __construct, boot, handle, isBooted
**-** `Finella\Http\RouteCacheCompiler` (`src\Http\RouteCacheCompiler.php`)
public: compile
**-** `Finella\Http\Middleware\AuthMiddleware` (`src\Http\Middleware\AuthMiddleware.php`)
public: __construct, __invoke, process
**-** `Finella\Http\Middleware\CookieMiddleware` (`src\Http\Middleware\CookieMiddleware.php`)
public: __construct, __invoke, process
**-** `Finella\Http\Middleware\CsrfMiddleware` (`src\Http\Middleware\CsrfMiddleware.php`)
public: __construct, __invoke, process
**-** `Finella\Http\Middleware\RateLimitMiddleware` (`src\Http\Middleware\RateLimitMiddleware.php`)
public: __construct, __invoke, process
**-** `Finella\Http\Middleware\RequestLoggerMiddleware` (`src\Http\Middleware\RequestLoggerMiddleware.php`)
public: __construct, __invoke, process
**-** `Finella\Http\Middleware\SecurityHeadersMiddleware` (`src\Http\Middleware\SecurityHeadersMiddleware.php`)
public: __construct, __invoke, process
**-** `Finella\Http\Middleware\SessionMiddleware` (`src\Http\Middleware\SessionMiddleware.php`)
public: __construct, __invoke, process
**-** `Finella\Http\Middleware\TrustedProxyMiddleware` (`src\Http\Middleware\TrustedProxyMiddleware.php`)
public: __invoke, process
**-** `Finella\Http\Request` (`src\Http\Request.php`)
public: __construct, all, allInput, clientIp, file, fromGlobals, fromPsr, getAttribute, getAttributes, getBody, getCookieParams, getHeader, getHeaderLine, getHeaders, getMethod, getParsedBody, getProtocolVersion, getQueryParams, getRequestTarget, getServerParams, getUploadedFiles, getUri, hasHeader, header, input, isSecure, validate, wantsJson, withAddedHeader, withAttribute, withBody, withCookieParams, withHeader, withMethod, withParams, withParsedBody, withProtocolVersion, withQueryParams, withRequestTarget, withUploadedFiles, withUri, withoutAttribute, withoutHeader
**-** `Finella\Http\RequestHandler` (`src\Http\RequestHandler.php`)
public: __construct, handle
**-** `Finella\Http\Response` (`src\Http\Response.php`)
public: __construct, download, file, getBody, getHeader, getHeaderLine, getHeaders, getProtocolVersion, getReasonPhrase, getStatusCode, hasHeader, html, json, redirect, send, stream, text, withAddedHeader, withBasePath, withBody, withHeader, withHeaders, withProtocolVersion, withStatus, withoutHeader, xml
**-** `Finella\Http\Router` (`src\Http\Router.php`)
public: __construct, add, cacheIssues, dispatch, get, group, middlewareGroup, post, use
**-** `Finella\Http\Stream` (`src\Http\Stream.php`)
public: __construct, __toString, close, detach, eof, fromString, getContents, getMetadata, getSize, hasCallback, invokeCallback, isReadable, isSeekable, isWritable, read, rewind, seek, tell, withCallback, write
**-** `Finella\Http\UploadedFile` (`src\Http\UploadedFile.php`)
public: __construct, extension, getClientFilename, getClientMediaType, getError, getSize, getStream, isValid, moveTo, store
**-** `Finella\Http\Uri` (`src\Http\Uri.php`)
public: __construct, __toString, getAuthority, getFragment, getHost, getPath, getPort, getQuery, getScheme, getUserInfo, withFragment, withHost, withPath, withPort, withQuery, withScheme, withUserInfo

**VIEW**
**-** `Finella\View\View` (`src\View\View.php`)
public: hasShared, render, share

**SECURITY**
**-** `Finella\Security\CsrfTokenManager` (`src\Security\CsrfTokenManager.php`)
public: __construct, token, validate

**EXTENSIONS**
**-** `Finella\Contracts\Support\ServiceProviderInterface` (`src\Contracts\Support\ServiceProviderInterface.php`)
public: boot, manifest, register
**-** `Finella\Support\ArrayCacheStore` (`src\Support\ArrayCacheStore.php`)
**-** `Finella\Support\ArrayStore` (`src\Support\ArrayStore.php`)
public: clear, forget, get, put
**-** `Finella\Support\Auth\AuthManager` (`src\Support\Auth\AuthManager.php`)
public: __construct, guard, user
**-** `Finella\Support\Auth\AuthServiceProvider` (`src\Support\Auth\AuthServiceProvider.php`)
public: register
**-** `Finella\Support\Auth\CallableUserProvider` (`src\Support\Auth\CallableUserProvider.php`)
public: __construct, retrieveById, retrieveByToken
**-** `Finella\Support\Auth\SessionGuard` (`src\Support\Auth\SessionGuard.php`)
public: __construct, check, id, login, logout, user
**-** `Finella\Support\Auth\TokenGuard` (`src\Support\Auth\TokenGuard.php`)
public: __construct, check, user
**-** `Finella\Support\Auth\UserProviderInterface` (`src\Support\Auth\UserProviderInterface.php`)
public: retrieveById, retrieveByToken
**-** `Finella\Support\Cache` (`src\Support\Cache.php`)
public: __construct, clear, forget, get, put, remember, store
**-** `Finella\Support\CacheItem` (`src\Support\CacheItem.php`)
public: __construct, expirationTimestamp, expiresAfter, expiresAt, get, getKey, isHit, markHit, set
**-** `Finella\Support\CacheItemPool` (`src\Support\CacheItemPool.php`)
public: __construct, clear, commit, deleteItem, deleteItems, getItem, getItems, hasItem, save, saveDeferred
**-** `Finella\Support\CacheServiceProvider` (`src\Support\CacheServiceProvider.php`)
public: register
**-** `Finella\Support\CacheStoreInterface` (`src\Support\CacheStoreInterface.php`)
**-** `Finella\Support\ComposerProviderDiscovery` (`src\Support\ComposerProviderDiscovery.php`)
public: discover
**-** `Finella\Support\Cookie` (`src\Support\Cookie.php`)
public: __construct, toHeader
**-** `Finella\Support\CookieJar` (`src\Support\CookieJar.php`)
public: attachToResponse, make, queue
**-** `Finella\Support\CookieServiceProvider` (`src\Support\CookieServiceProvider.php`)
public: register
**-** `Finella\Support\EventDispatcher` (`src\Support\EventDispatcher.php`)
public: __construct, dispatch, listen
**-** `Finella\Support\EventsServiceProvider` (`src\Support\EventsServiceProvider.php`)
public: register
**-** `Finella\Support\FileCacheStore` (`src\Support\FileCacheStore.php`)
**-** `Finella\Support\FileStore` (`src\Support\FileStore.php`)
public: __construct, clear, forget, get, put
**-** `Finella\Support\HealthChecker` (`src\Support\HealthChecker.php`)
public: __construct, fromConfig, run
**-** `Finella\Support\Logger` (`src\Support\Logger.php`)
public: __construct, alert, critical, debug, emergency, error, info, log, notice, warning
**-** `Finella\Support\LogServiceProvider` (`src\Support\LogServiceProvider.php`)
public: register
**-** `Finella\Support\ProviderCache` (`src\Support\ProviderCache.php`)
public: write
**-** `Finella\Support\ProviderCapability` (`src\Support\ProviderCapability.php`)
**-** `Finella\Support\ProviderManifest` (`src\Support\ProviderManifest.php`)
public: __construct
**-** `Finella\Support\ProviderReport` (`src\Support\ProviderReport.php`)
public: addEntry, toArray, toText
**-** `Finella\Support\ProviderRepository` (`src\Support\ProviderRepository.php`)
public: __construct, add, bootAll, registerAll
**-** `Finella\Support\ProviderValidator` (`src\Support\ProviderValidator.php`)
public: validate
**-** `Finella\Support\Psr\Cache\CacheItemInterface` (`src\Support\Psr\Cache\CacheItemInterface.php`)
public: expiresAfter, expiresAt, get, getKey, isHit, set
**-** `Finella\Support\Psr\Cache\CacheItemPoolInterface` (`src\Support\Psr\Cache\CacheItemPoolInterface.php`)
public: clear, commit, deleteItem, deleteItems, getItem, getItems, hasItem, save, saveDeferred
**-** `Finella\Support\Psr\Cache\InvalidArgumentException` (`src\Support\Psr\Cache\InvalidArgumentException.php`)
**-** `Finella\Support\Psr\Container\ContainerExceptionInterface` (`src\Support\Psr\Container\ContainerExceptionInterface.php`)
**-** `Finella\Support\Psr\Container\ContainerInterface` (`src\Support\Psr\Container\ContainerInterface.php`)
public: get, has
**-** `Finella\Support\Psr\Container\NotFoundExceptionInterface` (`src\Support\Psr\Container\NotFoundExceptionInterface.php`)
**-** `Finella\Support\Psr\Http\Factory\RequestFactoryInterface` (`src\Support\Psr\Http\Factory\RequestFactoryInterface.php`)
public: createRequest
**-** `Finella\Support\Psr\Http\Factory\ResponseFactoryInterface` (`src\Support\Psr\Http\Factory\ResponseFactoryInterface.php`)
public: createResponse
**-** `Finella\Support\Psr\Http\Factory\ServerRequestFactoryInterface` (`src\Support\Psr\Http\Factory\ServerRequestFactoryInterface.php`)
public: createServerRequest
**-** `Finella\Support\Psr\Http\Factory\StreamFactoryInterface` (`src\Support\Psr\Http\Factory\StreamFactoryInterface.php`)
public: createStream, createStreamFromFile, createStreamFromResource
**-** `Finella\Support\Psr\Http\Factory\UploadedFileFactoryInterface` (`src\Support\Psr\Http\Factory\UploadedFileFactoryInterface.php`)
public: createUploadedFile
**-** `Finella\Support\Psr\Http\Factory\UriFactoryInterface` (`src\Support\Psr\Http\Factory\UriFactoryInterface.php`)
public: createUri
**-** `Finella\Support\Psr\Http\Message\MessageInterface` (`src\Support\Psr\Http\Message\MessageInterface.php`)
public: getBody, getHeader, getHeaderLine, getHeaders, getProtocolVersion, hasHeader, withAddedHeader, withBody, withHeader, withProtocolVersion, withoutHeader
**-** `Finella\Support\Psr\Http\Message\RequestInterface` (`src\Support\Psr\Http\Message\RequestInterface.php`)
public: getMethod, getRequestTarget, getUri, withMethod, withRequestTarget, withUri
**-** `Finella\Support\Psr\Http\Message\ResponseInterface` (`src\Support\Psr\Http\Message\ResponseInterface.php`)
public: getReasonPhrase, getStatusCode, withStatus
**-** `Finella\Support\Psr\Http\Message\ServerRequestInterface` (`src\Support\Psr\Http\Message\ServerRequestInterface.php`)
public: getAttribute, getAttributes, getCookieParams, getParsedBody, getQueryParams, getServerParams, getUploadedFiles, withAttribute, withCookieParams, withParsedBody, withQueryParams, withUploadedFiles, withoutAttribute
**-** `Finella\Support\Psr\Http\Message\StreamInterface` (`src\Support\Psr\Http\Message\StreamInterface.php`)
public: __toString, close, detach, eof, getContents, getMetadata, getSize, isReadable, isSeekable, isWritable, read, rewind, seek, tell, write
**-** `Finella\Support\Psr\Http\Message\UploadedFileInterface` (`src\Support\Psr\Http\Message\UploadedFileInterface.php`)
public: getClientFilename, getClientMediaType, getError, getSize, getStream, moveTo
**-** `Finella\Support\Psr\Http\Message\UriInterface` (`src\Support\Psr\Http\Message\UriInterface.php`)
public: __toString, getAuthority, getFragment, getHost, getPath, getPort, getQuery, getScheme, getUserInfo, withFragment, withHost, withPath, withPort, withQuery, withScheme, withUserInfo
**-** `Finella\Support\Psr\Http\Server\MiddlewareInterface` (`src\Support\Psr\Http\Server\MiddlewareInterface.php`)
public: process
**-** `Finella\Support\Psr\Http\Server\RequestHandlerInterface` (`src\Support\Psr\Http\Server\RequestHandlerInterface.php`)
public: handle
**-** `Finella\Support\Psr\Log\LoggerInterface` (`src\Support\Psr\Log\LoggerInterface.php`)
public: alert, critical, debug, emergency, error, info, log, notice, warning
**-** `Finella\Support\Psr\Log\LogLevel` (`src\Support\Psr\Log\LogLevel.php`)
**-** `Finella\Support\Psr\SimpleCache\CacheInterface` (`src\Support\Psr\SimpleCache\CacheInterface.php`)
public: clear, delete, deleteMultiple, get, getMultiple, has, set, setMultiple
**-** `Finella\Support\Psr\SimpleCache\InvalidArgumentException` (`src\Support\Psr\SimpleCache\InvalidArgumentException.php`)
**-** `Finella\Support\Queue` (`src\Support\Queue.php`)
**-** `Finella\Support\QueueServiceProvider` (`src\Support\QueueServiceProvider.php`)
public: register
**-** `Finella\Support\RateLimiter` (`src\Support\RateLimiter.php`)
public: __construct, attempt, remaining
**-** `Finella\Support\RateLimitServiceProvider` (`src\Support\RateLimitServiceProvider.php`)
public: register
**-** `Finella\Support\ServiceProvider` (`src\Support\ServiceProvider.php`)
public: __construct, boot, manifest, register
**-** `Finella\Support\SessionInterface` (`src\Support\SessionInterface.php`)
public: forget, get, put
**-** `Finella\Support\SessionManager` (`src\Support\SessionManager.php`)
public: __construct, all, flash, forget, get, getFlash, put, regenerate, start
**-** `Finella\Support\SessionServiceProvider` (`src\Support\SessionServiceProvider.php`)
public: register
**-** `Finella\Support\SimpleCacheAdapter` (`src\Support\SimpleCacheAdapter.php`)
public: __construct, clear, delete, deleteMultiple, get, getMultiple, has, set, setMultiple
**-** `Finella\Support\SyncQueue` (`src\Support\SyncQueue.php`)
public: __construct, push
**-** `Finella\Support\ValidationException` (`src\Support\ValidationException.php`)
public: __construct, errors, status
**-** `Finella\Support\Validator` (`src\Support\Validator.php`)
public: __construct, errors, make, passes, validated

**OTHER**
**-** `Finella\Contracts\Cache\CacheStoreInterface` (`src\Contracts\Cache\CacheStoreInterface.php`)
public: clear, forget, get, put
**-** `Finella\Contracts\Events\EventDispatcherInterface` (`src\Contracts\Events\EventDispatcherInterface.php`)
public: dispatch, listen
**-** `Finella\Contracts\Log\LoggerInterface` (`src\Contracts\Log\LoggerInterface.php`)
**-** `Finella\Contracts\Queue\JobInterface` (`src\Contracts\Queue\JobInterface.php`)
public: handle
**-** `Finella\Contracts\Queue\QueueInterface` (`src\Contracts\Queue\QueueInterface.php`)
public: push
**-** `Finella\Contracts\Runtime\RuntimeInterface` (`src\Contracts\Runtime\RuntimeInterface.php`)
public: run
**-** `Finella\Database\Database` (`src\Database\Database.php`)
public: pdo
**-** `Finella\Database\DatabaseManager` (`src\Database\DatabaseManager.php`)
public: __construct, connection, table
**-** `Finella\Database\DatabaseServiceProvider` (`src\Database\DatabaseServiceProvider.php`)
public: register
**-** `Finella\Database\IdentifierGuard` (`src\Database\IdentifierGuard.php`)
public: assertAllowed
**-** `Finella\Database\MigrationInterface` (`src\Database\MigrationInterface.php`)
public: down, up
**-** `Finella\Database\Migrator` (`src\Database\Migrator.php`)
public: __construct, rollback, run, status
**-** `Finella\Database\MySqlQuoter` (`src\Database\MySqlQuoter.php`)
public: quote
**-** `Finella\Database\OperatorGuard` (`src\Database\OperatorGuard.php`)
public: assertAllowed
**-** `Finella\Database\QueryBuilder` (`src\Database\QueryBuilder.php`)
public: __construct, delete, first, get, groupBy, insert, limit, offset, orWhere, orderBy, select, toSql, update, where
**-** `Finella\Database\QuoterInterface` (`src\Database\QuoterInterface.php`)
public: quote
**-** `Finella\Plugin\PluginInterface` (`src\Plugin\PluginInterface.php`)
public: register
**-** `Finella\Plugin\PluginManager` (`src\Plugin\PluginManager.php`)
public: __construct, all, app, boot, config, load
**-** `Finella\Runtime\FpmRuntime` (`src\Runtime\FpmRuntime.php`)
public: run
**-** `Finella\Runtime\RequestContext` (`src\Runtime\RequestContext.php`)
public: __construct, begin, cspNonce, current, end, includeRequestIdHeader, includeSpanIdHeader, includeTraceIdHeader, locale, requestId, setCspNonce, setHeaderFlags, setLocale, spanId, startedAt, traceId
**-** `Finella\Runtime\ResetManager` (`src\Runtime\ResetManager.php`)
public: register, reset
**-** `Finella\Runtime\Resetter` (`src\Runtime\Resetter.php`)
public: reset
**-** `Finella\Runtime\RoadRunnerRuntime` (`src\Runtime\RoadRunnerRuntime.php`)
public: __construct, run

**HELPERS (GLOBAL)**
**-** app, view
