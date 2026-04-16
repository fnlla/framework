**FINELLA/STORAGE**

Local storage and a minimal image pipeline for Finella.

**INSTALLATION**
```bash
composer require finella/storage
```

**CONFIGURATION**
Create `config/storage/storage.php` (see app stubs) and set `.env`:
```
STORAGE_DISK=local
STORAGE_LOCAL_ROOT=storage/uploads
STORAGE_LOCAL_URL=/uploads
```

**USAGE**
```php
use Finella\Storage\StorageManager;

$storage = app()->make(StorageManager::class);
$disk = $storage->disk();
$disk->put('avatars/user.png', $binary);
$url = $disk->url('avatars/user.png');
```

**S3 DRIVER**
Install `finella/storage-s3` and add an S3 disk in `config/storage/storage.php`:
```
's3' => [
  'driver' => 's3',
  'bucket' => env('STORAGE_S3_BUCKET', ''),
  'region' => env('STORAGE_S3_REGION', 'eu-west-1'),
  'key' => env('STORAGE_S3_KEY', ''),
  'secret' => env('STORAGE_S3_SECRET', ''),
  'endpoint' => env('STORAGE_S3_ENDPOINT', ''),
  'prefix' => env('STORAGE_S3_PREFIX', ''),
  'public_url' => env('STORAGE_S3_PUBLIC_URL', ''),
],
```

**IMAGE PIPELINE**
```php
use Finella\Storage\ImagePipeline;

$pipeline = new ImagePipeline();
$pipeline->resize($sourcePath, $targetPath, 400, 400);
```

**NOTES**
**-** Image operations require the GD extension.
**-** Local disk is the default; add more drivers as needed.
