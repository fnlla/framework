<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$src = $root . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'src';
$outDir = $root . DIRECTORY_SEPARATOR . 'documentation' . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . 'api';
$outFile = $outDir . DIRECTORY_SEPARATOR . 'public-api.json';

if (!is_dir($src)) {
    fwrite(STDERR, "framework/src not found.\n");
    exit(1);
}

$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($src));
$classes = [];

foreach ($files as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    $code = file_get_contents($file->getPathname());
    if ($code === false) {
        continue;
    }

    $tokens = token_get_all($code);
    $namespace = '';
    $docComment = null;
    $currentClass = null;
    $currentType = null;
    $classDepth = null;
    $depth = 0;
    $visibility = 'public';

    $count = count($tokens);
    for ($i = 0; $i < $count; $i++) {
        $token = $tokens[$i];

        if (is_string($token)) {
            if ($token === '{') {
                $depth++;
            } elseif ($token === '}') {
                $depth--;
                if ($classDepth !== null && $depth < $classDepth) {
                    $currentClass = null;
                    $currentType = null;
                    $classDepth = null;
                    $visibility = 'public';
                }
            }
            continue;
        }

        [$id, $text] = $token;

        if ($id === T_DOC_COMMENT) {
            $docComment = $text;
            continue;
        }

        if ($id === T_NAMESPACE) {
            $namespace = '';
            for ($j = $i + 1; $j < $count; $j++) {
                $t = $tokens[$j];
                if (is_string($t) && ($t === ';' || $t === '{')) {
                    break;
                }
                if (is_array($t) && in_array($t[0], [T_STRING, T_NAME_QUALIFIED, T_NS_SEPARATOR], true)) {
                    $namespace .= $t[1];
                }
            }
            $namespace = trim($namespace);
            continue;
        }

        if (in_array($id, [T_CLASS, T_INTERFACE, T_TRAIT], true)) {
            if ($id === T_CLASS) {
                $prev = $i - 1;
                while ($prev >= 0) {
                    $t = $tokens[$prev];
                    if (is_array($t) && $t[0] === T_WHITESPACE) {
                        $prev--;
                        continue;
                    }
                    if (is_array($t) && $t[0] === T_DOUBLE_COLON) {
                        // Skip ::class constant usage.
                        continue 2;
                    }
                    break;
                }
            }
            // Skip anonymous classes.
            $nameToken = null;
            for ($j = $i + 1; $j < $count; $j++) {
                $t = $tokens[$j];
                if (is_array($t) && $t[0] === T_STRING) {
                    $nameToken = $t;
                    break;
                }
                if (is_string($t) && $t === '{') {
                    break;
                }
            }
            if ($nameToken === null) {
                continue;
            }

            $isInternal = is_string($docComment) && str_contains($docComment, '@internal');
            $docComment = null;
            if ($isInternal) {
                $currentClass = null;
                $currentType = null;
                $classDepth = null;
                continue;
            }

            $className = $nameToken[1];
            $fqcn = $namespace !== '' ? $namespace . '\\' . $className : $className;
            $currentClass = $fqcn;
            $currentType = $id === T_CLASS ? 'class' : ($id === T_INTERFACE ? 'interface' : 'trait');
            $classes[$fqcn] = [
                'type' => $currentType,
                'methods' => [],
            ];

            // Class body depth starts at the next '{'.
            for ($j = $i + 1; $j < $count; $j++) {
                $t = $tokens[$j];
                if (is_string($t) && $t === '{') {
                    $classDepth = $depth + 1;
                    break;
                }
            }

            continue;
        }

        if ($currentClass !== null) {
            if ($id === T_PUBLIC) {
                $visibility = 'public';
            } elseif ($id === T_PROTECTED || $id === T_PRIVATE) {
                $visibility = 'non-public';
            } elseif ($id === T_FUNCTION) {
                $isInternalMethod = is_string($docComment) && str_contains($docComment, '@internal');
                $methodName = null;
                for ($j = $i + 1; $j < $count; $j++) {
                    $t = $tokens[$j];
                    if (is_array($t) && $t[0] === T_STRING) {
                        $methodName = $t[1];
                        break;
                    }
                    if (is_string($t) && $t === '(') {
                        break;
                    }
                }
                if ($methodName !== null && $visibility === 'public' && !$isInternalMethod) {
                    $classes[$currentClass]['methods'][] = $methodName;
                }
                $docComment = null;
                $visibility = 'public';
            }
        }
    }
}

foreach ($classes as $name => $info) {
    $methods = array_values(array_unique($info['methods']));
    sort($methods);
    $classes[$name]['methods'] = $methods;
}
ksort($classes);

$existing = null;
if (is_file($outFile)) {
    $existing = json_decode((string) file_get_contents($outFile), true);
}
if (is_array($existing) && isset($existing['classes']) && $existing['classes'] === $classes) {
    echo "Public API snapshot unchanged.\n";
    exit(0);
}

$payload = [
    'generated_at' => gmdate('c'),
    'classes' => $classes,
];

if (!is_dir($outDir) && !@mkdir($outDir, 0775, true) && !is_dir($outDir)) {
    fwrite(STDERR, "Unable to create documentation/build/api directory.\n");
    exit(1);
}

file_put_contents($outFile, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo "Public API snapshot written to {$outFile}\n";

