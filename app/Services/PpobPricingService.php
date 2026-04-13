<?php

namespace App\Services;

use App\Models\PpobPricingRule;
use Illuminate\Support\Collection;

class PpobPricingService
{
    public function resolvePricing(array $context, float $providerPrice): array
    {
        $providerPrice = max(0, round($providerPrice, 2));
        $rule = $this->resolveRule($context);
        $markupAmount = $this->calculateMarkupAmount($rule, $providerPrice);
        $sellPrice = $this->applyRounding(
            max(0, $providerPrice + $markupAmount),
            $rule?->rounding_unit ?? 1,
        );

        return [
            'provider_price' => $providerPrice,
            'sell_price' => round($sellPrice, 2),
            'markup_amount' => round(max(0, $sellPrice - $providerPrice), 2),
            'rule' => $rule,
        ];
    }

    public function resolveRule(array $context): ?PpobPricingRule
    {
        /** @var Collection<int, PpobPricingRule> $rules */
        $rules = PpobPricingRule::query()
            ->where('is_active', true)
            ->get()
            ->filter(fn (PpobPricingRule $rule): bool => $this->matchesRule($rule, $context))
            ->sort(function (PpobPricingRule $left, PpobPricingRule $right): int {
                $specificityCompare = $right->specificity() <=> $left->specificity();
                if ($specificityCompare !== 0) {
                    return $specificityCompare;
                }

                $priorityCompare = $left->priority <=> $right->priority;
                if ($priorityCompare !== 0) {
                    return $priorityCompare;
                }

                return $left->id <=> $right->id;
            })
            ->values();

        return $rules->first();
    }

    private function matchesRule(PpobPricingRule $rule, array $context): bool
    {
        foreach (['service_type', 'category', 'brand', 'buyer_sku_code'] as $field) {
            $ruleValue = trim((string) ($rule->{$field} ?? ''));
            if ($ruleValue === '') {
                continue;
            }

            $contextValue = trim((string) ($context[$field] ?? ''));
            if ($contextValue === '' || strcasecmp($ruleValue, $contextValue) !== 0) {
                return false;
            }
        }

        return true;
    }

    private function calculateMarkupAmount(?PpobPricingRule $rule, float $providerPrice): float
    {
        if (! $rule) {
            return 0;
        }

        $baseMarkup = $rule->markup_type === PpobPricingRule::MARKUP_PERCENT
            ? ($providerPrice * ((float) $rule->markup_value / 100))
            : (float) $rule->markup_value;

        $baseMarkup = max(0, $baseMarkup);

        if ($rule->min_markup !== null) {
            $baseMarkup = max($baseMarkup, (float) $rule->min_markup);
        }

        if ($rule->max_markup !== null) {
            $baseMarkup = min($baseMarkup, (float) $rule->max_markup);
        }

        return round($baseMarkup, 2);
    }

    private function applyRounding(float $amount, int $roundingUnit): float
    {
        $unit = max(1, $roundingUnit);

        if ($unit === 1) {
            return round($amount, 2);
        }

        return ceil($amount / $unit) * $unit;
    }
}
