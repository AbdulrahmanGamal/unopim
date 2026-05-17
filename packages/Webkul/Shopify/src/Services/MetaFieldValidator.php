<?php

namespace Webkul\Shopify\Services;

use Webkul\Shopify\Repositories\ShopifyMetaFieldRepository;

/**
 * Owns all business-rule validation for Shopify metafield definitions.
 *
 * Extracted from MetaFieldController where these rules sat as 200+ lines of
 * inline code inside store() and update(). Keeping the rules here makes the
 * controller thin, makes the rules unit-testable in isolation, and prevents
 * drift between the create and update validation paths.
 *
 * Public surface:
 *   validateStore(array $data): array     — returns errors array (empty = valid)
 *   validateUpdate(array $data): array
 *   buildValidationsJson(array $data): ?string
 *   buildOptionsJson(array $data, bool $pretty = false): ?string
 */
class MetaFieldValidator
{
    /**
     * Conversion factors to the smallest unit per measurement type. Used to
     * compare min and max values in a normalised scale so cross-unit comparisons
     * (e.g. min=1 KILOGRAMS vs max=500 GRAMS) work correctly.
     */
    public const SMALLESTUNIT = [
        'weight' => [
            'GRAMS'     => 1,
            'KILOGRAMS' => 1000,
            'POUNDS'    => 453.592,
            'OUNCES'    => 28.3495,
        ],

        'volume' => [
            'MILLILITERS'           => 1,
            'CENTILITERS'           => 10,
            'LITERS'                => 1000,
            'CUBIC_METERS'          => 1000000,
            'FLUID_OUNCES'          => 29.5735,
            'PINTS'                 => 473.176,
            'QUARTS'                => 946.353,
            'GALLONS'               => 3785.41,
            'IMPERIAL_FLUID_OUNCES' => 28.4131,
            'IMPERIAL_PINTS'        => 568.261,
            'IMPERIAL_QUARTS'       => 1136.52,
            'IMPERIAL_GALLONS'      => 4546.09,
        ],

        'dimension' => [
            'MILLIMETERS' => 1,
            'CENTIMETERS' => 10,
            'METERS'      => 1000,
            'INCHES'      => 25.4,
            'FEET'        => 304.8,
            'YARDS'       => 914.4,
        ],
    ];

    /**
     * Max pinned metafield definitions per ownerType. Shopify allows up to 20.
     */
    public const MAX_PINNED_PER_OWNER = 19;

    public function __construct(
        protected ShopifyMetaFieldRepository $repository,
    ) {}

    /**
     * Returns an errors map suitable for a 422 JsonResponse. Empty = valid.
     */
    public function validateStore(array $data): array
    {
        $errors = [];

        $this->validatePinLimitOnCreate($data, $errors);
        $this->validateUniqueCode($data, $errors);
        $this->validateNamespaceKey($data, $errors);
        $this->validateAttributeLength($data, $errors);
        $this->validateTypeRequired($data, $errors);
        $this->validateDescriptionLength($data, $errors);
        $this->validateUnitValues($data, $errors);

        return $errors;
    }

    /**
     * Update has a different pin-limit calculation (exempt the current row from
     * the count) and skips namespace/code uniqueness because those fields are
     * not editable post-creation.
     */
    public function validateUpdate(array $data): array
    {
        $errors = [];

        $this->validatePinLimitOnUpdate($data, $errors);
        $this->validateUnitValues($data, $errors);
        $this->validateDescriptionLength($data, $errors);
        $this->validateAttributeLength($data, $errors);

        return $errors;
    }

    /**
     * Build the `validations` JSON column payload from min/max value+unit pairs.
     * Returns null when nothing to encode.
     */
    public function buildValidationsJson(array $data, bool $prettyForUpdate = false): ?string
    {
        $validationValue = [];

        if (! empty($data['minvalue']) || ! empty($data['maxvalue'])) {
            $validationValue = [
                'max' => ! empty($data['maxvalue']) ? $data['maxvalue'] : null,
                'min' => ! empty($data['minvalue']) ? $data['minvalue'] : null,
            ];
        }

        if (! empty($data['maxunit']) || ! empty($data['minunit'])) {
            $validationValue['maxunit'] = ! empty($data['maxunit']) ? $data['maxunit'] : null;
            $validationValue['minunit'] = ! empty($data['minunit']) ? $data['minunit'] : null;
        }

        if (empty($validationValue)) {
            return null;
        }

        if (! $prettyForUpdate) {
            return json_encode($validationValue, true);
        }

        // Update path used pretty-printed JSON with spaces after `:` and `,`.
        // Preserve that exact format to avoid diff churn on existing rows.
        $formatted = json_encode($validationValue, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $formatted = preg_replace('/:/', ': ', $formatted);
        $formatted = preg_replace('/,/', ', ', $formatted);

        return $formatted;
    }

    /**
     * Build the `options` JSON column payload from adminFilterable +
     * smartCollectionCondition fields. Returns null when neither is set.
     */
    public function buildOptionsJson(array $data, bool $prettyForUpdate = false): ?string
    {
        if (! isset($data['adminFilterable']) && ! isset($data['smartCollectionCondition'])) {
            return null;
        }

        $payload = [
            'adminFilterable'          => $data['adminFilterable'] ?? null,
            'smartCollectionCondition' => $data['smartCollectionCondition'] ?? null,
        ];

        if (! $prettyForUpdate) {
            return json_encode($payload, true);
        }

        $formatted = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $formatted = preg_replace('/:/', ': ', $formatted);
        $formatted = preg_replace('/,/', ', ', $formatted);

        return $formatted;
    }

    // --- Internal rule helpers ------------------------------------------------

    protected function validatePinLimitOnCreate(array $data, array &$errors): void
    {
        if (! (bool) ($data['pin'] ?? false)) {
            return;
        }

        $pinnedCount = $this->repository
            ->where('pin', 1)
            ->where('ownerType', $data['ownerType'])
            ->count();

        if ($pinnedCount > self::MAX_PINNED_PER_OWNER) {
            $errors['pin'] = [trans('shopify::app.shopify.metafield.validation.pin-limit')];
        }
    }

    protected function validatePinLimitOnUpdate(array $data, array &$errors): void
    {
        if (! (bool) ($data['pin'] ?? false)) {
            return;
        }

        $allPinned = $this->repository
            ->where('pin', 1)
            ->where('ownerType', $data['ownerType'])
            ->get()
            ->toArray();

        $countPin = count($allPinned);
        $attrCodes = array_column($allPinned, 'code');

        // Exempt the row being updated from the count if it is already pinned.
        if (isset($data['code']) && in_array($data['code'], $attrCodes, true)) {
            $filtered = array_filter($allPinned, fn ($item) => $item['code'] === $data['code']);
            $oneField = reset($filtered);
            if ((bool) ($oneField['pin'] ?? false)) {
                $countPin--;
            }
        }

        if ($countPin > self::MAX_PINNED_PER_OWNER) {
            $errors['pin'] = [trans('shopify::app.shopify.metafield.validation.pin-limit')];
        }
    }

    protected function validateUniqueCode(array $data, array &$errors): void
    {
        $existing = $this->repository
            ->where('code', $data['code'])
            ->where('ownerType', $data['ownerType'])
            ->get()
            ->first();

        if ($existing) {
            $definitionType = $existing->ownerType === 'PRODUCT'
                ? 'Product Defintion'
                : 'Product variant Definition';
            $errors['code'] = [trans('shopify::app.shopify.metafield.validation.definition-exists', ['type' => $definitionType])];
        }
    }

    protected function validateNamespaceKey(array $data, array &$errors): void
    {
        if (! isset($data['name_space_key'])) {
            return;
        }

        $existing = $this->repository
            ->where('name_space_key', $data['name_space_key'])
            ->where('ownerType', $data['ownerType'])
            ->get()
            ->first();

        if ($existing) {
            $definitionType = $existing->ownerType === 'PRODUCT'
                ? 'Product Defintion'
                : 'Product variant Definition';
            $errors['name_space_key'] = [trans('shopify::app.shopify.metafield.validation.namespace-taken', ['type' => $definitionType])];

            return;
        }

        $parts = explode('.', $data['name_space_key']);

        if (count($parts) !== 2) {
            $errors['name_space_key'] = [trans('shopify::app.shopify.metafield.validation.namespace-format')];

            return;
        }

        $keyLen = strlen($parts[1]);
        if ($keyLen < 2) {
            $errors['name_space_key'] = [trans('shopify::app.shopify.metafield.validation.key-min-length')];
        } elseif ($keyLen > 64) {
            $errors['name_space_key'] = [trans('shopify::app.shopify.metafield.validation.key-max-length')];
        } elseif (! $this->isValidString($parts[1]) || ! $this->isValidString($parts[0])) {
            $errors['name_space_key'] = [trans('shopify::app.shopify.metafield.validation.namespace-invalid-chars')];
        }
    }

    protected function validateAttributeLength(array $data, array &$errors): void
    {
        if (isset($data['attribute']) && strlen((string) $data['attribute']) > 255) {
            $errors['attribute'] = trans('shopify::app.shopify.metafield.validation.name-too-long');
        }
    }

    protected function validateTypeRequired(array $data, array &$errors): void
    {
        if (empty($data['type'])) {
            $errors['type'] = [trans('shopify::app.shopify.metafield.validation.type-required')];
        }
    }

    protected function validateDescriptionLength(array $data, array &$errors): void
    {
        if (! empty($data['description']) && strlen((string) $data['description']) > 100) {
            $errors['description'] = [trans('shopify::app.shopify.metafield.validation.description-max-length')];
        }
    }

    /**
     * Validates min/max values + units. Preserves the exact behaviour of the
     * original MetaFieldController::checkUnitValue, including its cross-unit
     * comparison via SMALLESTUNIT and its rating-type min/max-required rule.
     */
    protected function validateUnitValues(array $data, array &$errors): void
    {
        $maxvalue = null;
        $minvalue = null;
        $maxunit = null;
        $minunit = null;

        if (! empty($data['maxvalue'])) {
            if (isset($data['maxunit']) && empty($data['maxunit'])) {
                $errors['maxunit'] = ['required'];
            }
            $maxunit = $data['maxunit'] ?? null;
            $maxvalue = $data['maxvalue'];
        }

        if (! empty($data['minvalue'])) {
            if (isset($data['minunit']) && empty($data['minunit'])) {
                $errors['minunit'] = ['required'];
            }
            $minunit = $data['minunit'] ?? null;
            $minvalue = $data['minvalue'];
        }

        if ($minvalue && $maxvalue) {
            $unitData = self::SMALLESTUNIT[$data['type']] ?? null;

            if (! ctype_digit((string) $minvalue)) {
                $errors['minvalue'] = [trans('shopify::app.shopify.metafield.validation.only-number')];

                return;
            }
            if (! ctype_digit((string) $maxvalue)) {
                $errors['maxvalue'] = [trans('shopify::app.shopify.metafield.validation.only-number')];

                return;
            }

            if ($unitData) {
                $minvalue = $minvalue * ($unitData[$minunit] ?? 0);
                $maxvalue = $maxvalue * ($unitData[$maxunit] ?? 0);
            } else {
                $validateValue = function ($value, $type, $field) use (&$errors) {
                    if ($type !== 'date' && ! ctype_digit((string) $value)) {
                        $errors[$field] = [trans('shopify::app.shopify.metafield.validation.only-number')];

                        return null;
                    }

                    return $type === 'date' ? new \DateTime($value) : (int) $value;
                };

                $minvalue = ! empty($data['minvalue'])
                    ? $validateValue($data['minvalue'], $data['type'] ?? '', 'minvalue')
                    : null;
                $maxvalue = ! empty($data['maxvalue'])
                    ? $validateValue($data['maxvalue'], $data['type'] ?? '', 'maxvalue')
                    : null;
            }

            if ($minvalue > $maxvalue) {
                $msg = trans('shopify::app.shopify.metafield.validation.min-less-than-max');
                $errors['minvalue'] = [$msg];
                $errors['maxvalue'] = [$msg];
            }
        }

        if (($data['type'] ?? null) === 'rating' && (! $minvalue || ! $maxvalue)) {
            $errors['minvalue'] = $errors['maxvalue'] = [trans('shopify::app.shopify.metafield.validation.rating-min-max-required')];
        }
    }

    public function isValidString(string $string): bool
    {
        return (bool) preg_match('/^[a-zA-Z0-9_-]+$/', $string);
    }
}
