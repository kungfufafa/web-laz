<?php

namespace App\Services;

use App\Models\DonationCategory;
use App\Models\DonationPaymentType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DonationCatalogService
{
    /** @var Collection<int, array<string, mixed>>|null */
    private ?Collection $catalog = null;

    private bool $catalogFromDatabase = false;

    /**
     * @return array<string, mixed>
     */
    public function donationConfigPayload(): array
    {
        $calculatorTypes = $this->zakatCalculatorTypes();

        return [
            'categories' => $this->catalog()->map(function (array $category): array {
                return [
                    'key' => $category['key'],
                    'label' => $category['label'],
                    'description' => $category['description'],
                    'requires_context' => (bool) ($category['requires_context'] ?? false),
                    'payment_types' => collect($category['payment_types'] ?? [])->map(function (array $paymentType): array {
                        return [
                            'key' => $paymentType['key'],
                            'label' => $paymentType['label'],
                            'description' => $paymentType['description'] ?? null,
                            'conditions' => $paymentType['conditions'] ?? null,
                            'is_zakat_calculator' => (bool) ($paymentType['is_zakat_calculator'] ?? false),
                        ];
                    })->values()->all(),
                ];
            })->values()->all(),
            'contexts' => config('donation.contexts', []),
            'zakat' => [
                'calculator_types' => collect($calculatorTypes)
                    ->map(fn (string $type): array => [
                        'key' => $type,
                        'label' => $this->paymentTypeLabel($type, 'zakat'),
                    ])
                    ->values()
                    ->all(),
                'defaults' => config('donation.zakat_defaults', []),
            ],
            'recommended_amounts' => config('donation.recommended_amounts', []),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function categoriesForSelect(): array
    {
        return $this->catalog()
            ->mapWithKeys(fn (array $category): array => [
                $category['key'] => $category['label'],
            ])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function paymentTypesForCategory(string $categoryKey): array
    {
        $category = $this->catalog()
            ->first(fn (array $item): bool => $item['key'] === $categoryKey);

        if (! is_array($category)) {
            return [];
        }

        return collect($category['payment_types'] ?? [])
            ->mapWithKeys(fn (array $paymentType): array => [
                $paymentType['key'] => $paymentType['label'],
            ])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function allPaymentTypeOptions(): array
    {
        $options = [];

        foreach ($this->catalog() as $category) {
            foreach ($category['payment_types'] ?? [] as $paymentType) {
                $key = $paymentType['key'] ?? null;
                $label = $paymentType['label'] ?? null;

                if (! is_string($key) || $key === '' || ! is_string($label) || $label === '') {
                    continue;
                }

                // Keep the first label to avoid overriding duplicates like "umum".
                if (! array_key_exists($key, $options)) {
                    $options[$key] = $label;
                }
            }
        }

        return $options;
    }

    /**
     * @return list<string>
     */
    public function validCategoryKeys(): array
    {
        return $this->catalog()
            ->pluck('key')
            ->filter(fn (mixed $key): bool => is_string($key) && $key !== '')
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    public function validPaymentTypeKeys(): array
    {
        return collect($this->catalog())
            ->flatMap(fn (array $category): array => collect($category['payment_types'] ?? [])
                ->pluck('key')
                ->filter(fn (mixed $key): bool => is_string($key) && $key !== '')
                ->values()
                ->all())
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    public function validPaymentTypesForCategory(string $categoryKey): array
    {
        return collect($this->paymentTypesForCategory($categoryKey))
            ->keys()
            ->values()
            ->all();
    }

    public function requiresContext(string $categoryKey): bool
    {
        $category = $this->catalog()
            ->first(fn (array $item): bool => $item['key'] === $categoryKey);

        return is_array($category) ? (bool) ($category['requires_context'] ?? false) : false;
    }

    public function hasContextOptions(string $categoryKey): bool
    {
        $contexts = config("donation.contexts.{$categoryKey}", []);

        return is_array($contexts) && count($contexts) > 0;
    }

    /**
     * @return list<string>
     */
    public function zakatCalculatorTypes(): array
    {
        $supportedTypes = ZakatCalculatorService::supportedTypes();

        $typesFromCatalog = collect($this->catalog())
            ->flatMap(fn (array $category): array => collect($category['payment_types'] ?? [])
                ->filter(fn (array $paymentType): bool => (bool) ($paymentType['is_zakat_calculator'] ?? false))
                ->pluck('key')
                ->filter(fn (mixed $key): bool => is_string($key) && in_array($key, $supportedTypes, true))
                ->values()
                ->all())
            ->unique()
            ->values()
            ->all();

        if ($this->catalogFromDatabase) {
            return $typesFromCatalog;
        }

        if ($typesFromCatalog !== []) {
            return $typesFromCatalog;
        }

        $typesFromConfig = config('donation.zakat_calculator_types', $supportedTypes);

        return collect(is_array($typesFromConfig) ? $typesFromConfig : [])
            ->filter(fn (mixed $item): bool => is_string($item) && in_array($item, $supportedTypes, true))
            ->values()
            ->all();
    }

    public function categoryLabel(?string $categoryKey): string
    {
        if (! is_string($categoryKey) || trim($categoryKey) === '') {
            return '-';
        }

        $trimmedCategoryKey = trim($categoryKey);
        $category = $this->catalog()
            ->first(fn (array $item): bool => $item['key'] === $trimmedCategoryKey);

        if (is_array($category) && is_string($category['label'] ?? null) && $category['label'] !== '') {
            return $category['label'];
        }

        return Str::of($trimmedCategoryKey)
            ->replace(['_', '-'], ' ')
            ->headline()
            ->toString();
    }

    public function paymentTypeLabel(?string $paymentTypeKey, ?string $categoryKey = null): string
    {
        if (! is_string($paymentTypeKey) || trim($paymentTypeKey) === '') {
            return '-';
        }

        $trimmedPaymentTypeKey = trim($paymentTypeKey);

        if (is_string($categoryKey) && trim($categoryKey) !== '') {
            $options = $this->paymentTypesForCategory(trim($categoryKey));
            if (isset($options[$trimmedPaymentTypeKey])) {
                return $options[$trimmedPaymentTypeKey];
            }
        }

        $label = $this->allPaymentTypeOptions()[$trimmedPaymentTypeKey] ?? null;

        if (is_string($label) && $label !== '') {
            return $label;
        }

        return Str::of($trimmedPaymentTypeKey)
            ->replace(['_', '-'], ' ')
            ->headline()
            ->toString();
    }

    /**
     * @return array<string, mixed>
     */
    public function paymentTypeConditions(string $categoryKey, string $paymentTypeKey): array
    {
        $category = $this->catalog()
            ->first(fn (array $item): bool => $item['key'] === $categoryKey);

        if (! is_array($category)) {
            return [];
        }

        $paymentType = collect($category['payment_types'] ?? [])
            ->first(fn (array $item): bool => ($item['key'] ?? null) === $paymentTypeKey);

        if (! is_array($paymentType)) {
            return [];
        }

        return is_array($paymentType['conditions'] ?? null)
            ? $paymentType['conditions']
            : [];
    }

    public function normalizeCategory(?string $rawCategory): ?string
    {
        if (! is_string($rawCategory)) {
            return null;
        }

        $normalized = strtolower(trim($rawCategory));
        if ($normalized === '') {
            return null;
        }

        $aliases = config('donation.category_aliases', []);

        if (is_array($aliases) && isset($aliases[$normalized]) && is_string($aliases[$normalized])) {
            return strtolower(trim($aliases[$normalized]));
        }

        return $normalized;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function catalog(): Collection
    {
        if ($this->catalog instanceof Collection) {
            return $this->catalog;
        }

        if ($this->hasDatabaseCatalogEntries()) {
            $catalog = $this->databaseCatalog();
            $this->catalogFromDatabase = true;
        } else {
            $catalog = $this->configCatalog();
            $this->catalogFromDatabase = false;
        }

        $this->catalog = $catalog->values();

        return $this->catalog;
    }

    private function hasDatabaseCatalogEntries(): bool
    {
        if (! Schema::hasTable('donation_categories') || ! Schema::hasTable('donation_payment_types')) {
            return false;
        }

        return DonationCategory::withTrashed()->exists()
            || DonationPaymentType::withTrashed()->exists();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function databaseCatalog(): Collection
    {
        if (! Schema::hasTable('donation_categories') || ! Schema::hasTable('donation_payment_types')) {
            return collect();
        }

        $categories = DonationCategory::query()
            ->where('is_active', true)
            ->whereHas('paymentTypes', fn ($query) => $query->where('is_active', true))
            ->with(['paymentTypes' => fn ($query) => $query
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return $categories->map(function (DonationCategory $category): array {
            $normalizedCategoryKey = strtolower(trim((string) $category->key));

            $paymentTypes = $category->paymentTypes
                ->map(function ($paymentType): array {
                    return [
                        'key' => strtolower(trim((string) $paymentType->key)),
                        'label' => trim((string) $paymentType->label),
                        'description' => filled($paymentType->description)
                            ? trim((string) $paymentType->description)
                            : null,
                        'conditions' => is_array($paymentType->conditions)
                            ? $paymentType->conditions
                            : null,
                        'is_zakat_calculator' => (bool) $paymentType->is_zakat_calculator,
                    ];
                })
                ->filter(fn (array $paymentType): bool => $paymentType['key'] !== '' && $paymentType['label'] !== '')
                ->values()
                ->all();

            return [
                'key' => $normalizedCategoryKey,
                'label' => trim($category->label),
                'description' => filled($category->description)
                    ? trim((string) $category->description)
                    : null,
                'requires_context' => (bool) $category->requires_context,
                'payment_types' => $paymentTypes,
            ];
        })->filter(function (array $category): bool {
            return $category['key'] !== ''
                && $category['label'] !== ''
                && ($category['payment_types'] !== []);
        })->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function configCatalog(): Collection
    {
        $categories = config('donation.categories', []);
        $contexts = config('donation.contexts', []);
        $calculatorTypes = config('donation.zakat_calculator_types', ZakatCalculatorService::supportedTypes());

        if (! is_array($categories)) {
            return collect();
        }

        return collect($categories)
            ->map(function (mixed $categoryConfig, mixed $categoryKey) use ($contexts, $calculatorTypes): ?array {
                if (! is_string($categoryKey) || ! is_array($categoryConfig)) {
                    return null;
                }

                $normalizedCategoryKey = strtolower(trim($categoryKey));
                if ($normalizedCategoryKey === '') {
                    return null;
                }

                $paymentTypes = collect($categoryConfig['payment_types'] ?? [])
                    ->map(function (mixed $label, mixed $paymentTypeKey) use ($calculatorTypes): ?array {
                        if (! is_string($paymentTypeKey)) {
                            return null;
                        }

                        $normalizedPaymentTypeKey = strtolower(trim($paymentTypeKey));
                        if ($normalizedPaymentTypeKey === '') {
                            return null;
                        }

                        $normalizedLabel = is_string($label) && trim($label) !== ''
                            ? trim($label)
                            : Str::of($normalizedPaymentTypeKey)->replace(['_', '-'], ' ')->headline()->toString();

                        return [
                            'key' => $normalizedPaymentTypeKey,
                            'label' => $normalizedLabel,
                            'description' => null,
                            'conditions' => null,
                            'is_zakat_calculator' => in_array($normalizedPaymentTypeKey, is_array($calculatorTypes) ? $calculatorTypes : [], true),
                        ];
                    })
                    ->filter(fn (mixed $item): bool => is_array($item))
                    ->values()
                    ->all();

                if ($paymentTypes === []) {
                    return null;
                }

                $requiresContext = (bool) ($categoryConfig['requires_context']
                    ?? ! empty(is_array($contexts[$normalizedCategoryKey] ?? null) ? $contexts[$normalizedCategoryKey] : []));

                return [
                    'key' => $normalizedCategoryKey,
                    'label' => is_string($categoryConfig['label'] ?? null) && trim($categoryConfig['label']) !== ''
                        ? trim($categoryConfig['label'])
                        : Str::of($normalizedCategoryKey)->replace(['_', '-'], ' ')->headline()->toString(),
                    'description' => is_string($categoryConfig['description'] ?? null) && trim($categoryConfig['description']) !== ''
                        ? trim($categoryConfig['description'])
                        : null,
                    'requires_context' => $requiresContext,
                    'payment_types' => $paymentTypes,
                ];
            })
            ->filter(fn (mixed $item): bool => is_array($item))
            ->values();
    }
}
