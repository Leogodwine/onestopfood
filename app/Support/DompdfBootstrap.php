<?php

namespace App\Support;

final class DompdfBootstrap
{
    private static bool $registered = false;

    public static function register(): void
    {
        if (self::$registered || class_exists(\Dompdf\Dompdf::class, false)) {
            self::$registered = true;
            return;
        }

        $prefixes = [
            'Dompdf\\' => 'vendor/dompdf/dompdf/src/',
            'FontLib\\' => 'vendor/dompdf/php-font-lib/src/FontLib/',
            'Svg\\' => 'vendor/dompdf/php-svg-lib/src/Svg/',
            'Masterminds\\' => 'vendor/masterminds/html5/src/',
            'Sabberworm\\CSS\\' => 'vendor/sabberworm/php-css-parser/src/',
        ];

        spl_autoload_register(static function (string $class) use ($prefixes): void {
            foreach ($prefixes as $prefix => $dir) {
                if (! str_starts_with($class, $prefix)) {
                    continue;
                }

                $relative = str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
                $path = base_path($dir . $relative);

                if (is_file($path)) {
                    require $path;
                }
            }
        }, prepend: true);

        $cpdf = base_path('vendor/dompdf/dompdf/lib/Cpdf.php');
        if (is_file($cpdf)) {
            require_once $cpdf;
        }

        self::$registered = true;
    }
}
