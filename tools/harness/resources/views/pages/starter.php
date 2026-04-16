<h1>Clean skeleton</h1>
<p>A minimal start without extra pages and modules.</p>

<h2>Minimal steps</h2>
<ol>
    <li>Copy the <code>skeleton/</code> directory to a new project.</li>
    <li>Run <code>composer install</code>.</li>
    <li>Copy <code>.env.example</code> to <code>.env</code>.</li>
    <li>In <code>config/routes.php</code> keep only the home page route.</li>
    <li>Remove the documentation views and docs files if you do not need them.</li>
    <li>In <code>config/app.php</code> disable module providers you do not need.</li>
</ol>

<h2>Minimal routes</h2>
<pre><code class="language-php">return [
    ['GET', '/', [App\Http\Controllers\HomeController::class, 'index'], 'home', ['web']],
];</code></pre>

<div class="actions">
    <a class="btn" href="<?= e(route($app, 'docs')) ?>">Documentation</a>
    <a class="btn outline" href="<?= e(route($app, 'home')) ?>">Back home</a>
</div>
