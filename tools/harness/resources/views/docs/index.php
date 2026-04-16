<h1>finellaPHP Documentation</h1>
<p>A minimal framework ready for commercial projects. The online documentation lives in the skeleton, while the core stays lightweight.</p>

<h2>Quick start (after unpacking)</h2>
<ol>
    <li>Enter the <code>skeleton/</code> directory.</li>
    <li>Run <code>composer install</code>.</li>
    <li>Copy <code>.env.example</code> to <code>.env</code>.</li>
    <li>Start the server: <code>php -S localhost:8000 -t public public/index.php</code>.</li>
    <li>Open <code>http://localhost:8000</code> and the documentation at <code>/docs</code>.</li>
</ol>

<h2>Quick start (separate project)</h2>
<ol>
    <li>Copy the <code>skeleton/</code> directory to a new project.</li>
    <li>In <code>composer.json</code>, update the <code>repositories</code> section to point to the framework location.</li>
    <li>Run <code>composer install</code>, set <code>.env</code>, and start the server as above.</li>
</ol>

<h2>Core vs modules</h2>
<p>The core is only: HTTP + routing + middleware, DI + config, error handling. Everything else is enabled via providers.</p>

<h2>PSR compliance</h2>
<p>The core supports PSR-7/15/17 (HTTP), PSR-11 (container), PSR-3 (logger), PSR-6/16 (cache).</p>

<h2>Configuration</h2>
<ul>
    <li>Files in <code>config/**/*.php</code></li>
    <li>Validation schema in <code>config/schema.php</code></li>
    <li>Config cache: <code>php cli/finella.php config:cache</code></li>
    <li>Route cache: <code>php cli/finella.php routes:cache</code></li>
    <li><code>APP_BASE_PATH</code> and <code>APP_SITE_URL</code> for apps in a subdirectory</li>
</ul>

<h2>Routing (cache friendly)</h2>
<pre><code class="language-php">return [
    ['GET', '/', [App\Http\Controllers\HomeController::class, 'index'], 'home', ['web']],
];</code></pre>

<h2>Host-based routing</h2>
<pre><code class="language-php">return [
    ['GET', '/', [App\Http\Controllers\HomeController::class, 'index'], 'home', ['web'], '{tenant}.example.com'],
];</code></pre>

<p>Route caching requires routes defined as controllers (no closures).</p>

<h2>Middleware (PSR-15)</h2>
<pre><code class="language-php">// config/http/http.php
'global' =&gt; [
    SecurityHeadersMiddleware::class,
    RequestLoggerMiddleware::class,
    CookieMiddleware::class,
],
'middleware_groups' =&gt; [
    'web' =&gt; [
        SessionMiddleware::class,
        CsrfMiddleware::class,
    ],
],</code></pre>

<h2>HTTP helpers</h2>
<ul>
    <li><code>Response::html</code>, <code>Response::json</code>, <code>Response::redirect</code>, <code>Response::file</code>, <code>Response::stream</code></li>
    <li><code>Request::wantsJson()</code>, <code>Request::input()</code>, <code>Request::validate()</code>, <code>Request::file()</code></li>
</ul>

<h2>Security defaults</h2>
<p>By default: CSP nonce + secure headers. CSRF is in the <code>web</code> group.</p>

<h2>Sessions &amp; Cookies</h2>
<pre><code class="language-php">session($app)->put('user_id', 123);
$cookie = cookie($app)->make('promo', 'yes', 3600);
cookie($app)->queue($cookie);</code></pre>

<h2>Providers</h2>
<p>Enable modules in <code>config/app.php</code>.</p>
<pre><code class="language-php">'providers' =&gt; [
    App\Providers\AppServiceProvider::class,
    LogServiceProvider::class,
    CookieServiceProvider::class,
    SessionServiceProvider::class,
    // AuthServiceProvider::class,
    // DatabaseServiceProvider::class,
    // RateLimitServiceProvider::class,
    // CacheServiceProvider::class,
    // EventsServiceProvider::class,
    // QueueServiceProvider::class,
],</code></pre>

<h2>Auth</h2>
<pre><code class="language-php">$user = auth($app)->user($request);
// middleware
['GET', '/account', [AccountController::class, 'index'], 'account', [AuthMiddleware::class]]</code></pre>

<h2>Rate limiting</h2>
<pre><code class="language-php">['GET', '/api', [ApiController::class, 'index'], 'api', [RateLimitMiddleware::class]]</code></pre>
<p>Rate limiting requires CacheServiceProvider (PSR-16) to be enabled.</p>

<h2>Validation</h2>
<pre><code class="language-php">$data = $request->validate([
    'email' =&gt; 'required|email',
    'name' =&gt; 'required|string|min:3|max:80',
]);</code></pre>

<h2>Uploads</h2>
<pre><code class="language-php">$file = $request->file('avatar');
if ($file && $file->isValid()) {
    $path = $file->store($app->basePath() . '/storage/uploads', null, ['image/png', 'image/jpeg'], 2_000_000);
}</code></pre>

<h2>Cache</h2>
<pre><code class="language-php">cache($app)->put('foo', 'bar', 60);
$psr16 = app($app, Psr\SimpleCache\CacheInterface::class);
$psr6 = app($app, Psr\Cache\CacheItemPoolInterface::class);</code></pre>

<h2>Events and queue (optional)</h2>
<pre><code class="language-php">event($app, 'user.registered', ['id' => 1]);
queue($app)?->push(fn () => do_something());</code></pre>

<h2>Database + Query Builder</h2>
<pre><code class="language-php">$users = db($app, 'users')
    ->where('active', 1)
    ->orderBy('id', 'desc')
    ->limit(10)
    ->get();</code></pre>

<h2>Migrations</h2>
<pre><code class="language-bash">php cli/finella.php migrate
php cli/finella.php migrate:status
php cli/finella.php migrate:rollback 1</code></pre>

<h2>CLI</h2>
<pre><code class="language-bash">php cli/finella.php config:cache
php cli/finella.php config:clear
php cli/finella.php routes:cache
php cli/finella.php routes:clear
php cli/finella.php health</code></pre>

<h2>Health check</h2>
<p>HTTP: <code>/health</code> (optional token in <code>HEALTH_TOKEN</code>).</p>

<h2>Config validation</h2>
<p>The configuration schema lives in <code>config/schema.php</code> and is validated on application start.</p>

<div class="actions">
    <a class="btn outline" href="<?= e(route($app, 'starter')) ?>">Clean skeleton</a>
    <a class="btn" href="<?= e(route($app, 'home')) ?>">Back home</a>
</div>


