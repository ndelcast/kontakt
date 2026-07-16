<?php

namespace App\Services;

/**
 * Le bilan d'un import, que la commande affiche en console et que l'UI affiche
 * en toast. Immuable : c'est un constat, pas un état.
 */
class CodeurImportResult
{
    public function __construct(
        public readonly bool $success,
        public readonly bool $unchanged = false,
        public readonly int $received = 0,
        public readonly int $created = 0,
        public readonly int $known = 0,
        public readonly int $newCategories = 0,
        public readonly ?string $error = null,
    ) {}

    public static function unchanged(): self
    {
        return new self(success: true, unchanged: true);
    }

    public static function failed(string $error): self
    {
        return new self(success: false, error: $error);
    }

    /**
     * Une phrase lisible par un humain, en console comme à l'écran.
     */
    public function message(): string
    {
        if (! $this->success) {
            return $this->error ?? __('The import failed.');
        }

        if ($this->unchanged) {
            return __('Feed unchanged, nothing to import.');
        }

        if ($this->created === 0) {
            return __('No new offer: :count already known.', ['count' => $this->known]);
        }

        return trans_choice(
            '{1} :count new offer imported.|[2,*] :count new offers imported.',
            $this->created,
            ['count' => $this->created]
        );
    }
}
