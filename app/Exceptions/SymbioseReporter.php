<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class SymbioseReporter
{
    private static string $endpoint = 'https://symbiose.cabalex.dev/api/webhook';

    /**
     * Capture et envoie l'exception a Symbiose.
     * Appele depuis le callback withExceptions() dans bootstrap/app.php.
     */
    public static function report(\Throwable $e): void
    {
        $key = config('services.symbiose.project_key', env('SYMBIOSE_PROJECT_KEY'));
        $secret = config('services.symbiose.secret', env('SYMBIOSE_SECRET'));

        if (!$key || !$secret) {
            return;
        }

        // Deduplication : max 1 envoi par erreur unique par minute
        $hash = md5($e::class . $e->getFile() . $e->getLine());
        $cacheKey = "symbiose_error_{$hash}";

        if (Cache::has($cacheKey)) {
            return;
        }

        Cache::put($cacheKey, true, now()->addMinute());

        // Contexte enrichi pour l'IA
        $payload = [
            'project_key' => $key,
            'level' => 'error',
            'message' => $e->getMessage(),
            'exception_class' => $e::class,
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'stack_trace' => $e->getTraceAsString(),
            'code_snippet' => static::getCodeSnippet($e->getFile(), $e->getLine()),
            'request_data' => static::getRequestData(),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'environment' => app()->environment(),
            'timestamp' => now()->toISOString(),
        ];

        // Signature HMAC
        $jsonPayload = json_encode($payload);
        $signature = 'sha256=' . hash_hmac('sha256', $jsonPayload, $secret);

        // Envoi async (non bloquant)
        try {
            Http::withHeaders([
                'X-Symbiose-Signature' => $signature,
                'Content-Type' => 'application/json',
                'User-Agent' => 'SymbioseReporter/1.0',
            ])
                ->timeout(5)
                ->post(static::$endpoint, $payload);
        } catch (\Throwable) {
            // Silencieux — on ne veut jamais que le reporter casse l'app du client
        }
    }

    /**
     * Recupere les lignes de code autour de l'erreur.
     * Donne du contexte a l'IA pour comprendre le bug.
     */
    private static function getCodeSnippet(string $file, int $line, int $radius = 10): ?array
    {
        if (!is_readable($file)) {
            return null;
        }

        $lines = file($file);
        if ($lines === false) {
            return null;
        }

        $start = max(0, $line - $radius - 1);
        $end = min(count($lines), $line + $radius);

        $snippet = [];
        for ($i = $start; $i < $end; $i++) {
            $snippet[$i + 1] = rtrim($lines[$i]);
        }

        return [
            'lines' => $snippet,
            'error_line' => $line,
        ];
    }

    /**
     * Donnees de la requete HTTP en cours (si disponible).
     */
    private static function getRequestData(): ?array
    {
        try {
            $request = request();
            return [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_id' => $request->user()?->id,
                'route' => $request->route()?->getName(),
                'input' => $request->except(['password', 'password_confirmation', 'token', 'secret', 'credit_card']),
            ];
        } catch (\Throwable) {
            return null;
        }
    }
}
