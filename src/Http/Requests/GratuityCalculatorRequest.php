<?php

declare(strict_types=1);

namespace Crmleaf\Payroll\Tools\GratuityCalculator\Http\Requests;

use Crmleaf\Payroll\Money;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the wire input for Gratuity Calculator and turns it into named arguments
 * for Crmleaf\Payroll\Calculators\GratuityCalculator::calculate().
 *
 * Optional fields that were not sent are left out of the payload entirely
 * rather than passed as null, so the calculator's own documented defaults apply
 * and there is exactly one place each default is written down.
 */
final class GratuityCalculatorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        if (!$this->submitted()) {
            return [];
        }

        return [
            'last_drawn_salary' => ['required', 'numeric', 'min:0'],
            'years_of_service' => ['required', 'integer', 'min:0', 'max:60'],
            'months_of_service' => ['nullable', 'integer', 'min:0', 'max:11'],
            'covered' => ['nullable', 'boolean'],
            'separation_reason' => ['nullable', 'string', 'in:resignation,retirement,superannuation,termination,retrenchment,death,disablement'],
            'as_of' => ['nullable', 'date'],
        ];
    }

    /**
     * Named arguments for GratuityCalculator::calculate().
     *
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        /** @var array<string, mixed> $input */
        $input = $this->validated();

        $payload = [
            'lastDrawnSalary' => Money::fromRupees((float) $input['last_drawn_salary']),
            'yearsOfService' => (int) $input['years_of_service'],
        ];

        if (array_key_exists('months_of_service', $input) && $input['months_of_service'] !== null) {
            $payload['monthsOfService'] = (int) $input['months_of_service'];
        }

        if (array_key_exists('covered', $input) && $input['covered'] !== null) {
            $payload['covered'] = (bool) $input['covered'];
        }

        if (array_key_exists('separation_reason', $input) && $input['separation_reason'] !== null) {
            $payload['separationReason'] = (string) $input['separation_reason'];
        }

        if (array_key_exists('as_of', $input) && $input['as_of'] !== null) {
            $payload['asOf'] = new \DateTimeImmutable((string) $input['as_of']);
        }

        return $payload;
    }

    /**
     * A bare GET renders an empty form; everything else is a submission.
     */
    public function submitted(): bool
    {
        return $this->isMethod('post') || $this->expectsJson() || $this->query->count() > 0;
    }
}
