# Gratuity Calculator

Payment of Gratuity Act 1972 formula with the exemption cap applied.

Applies the statutory formula including the two rules that trip people up: a part-year counts only past six months, and the five-year qualifying period is waived on death or disablement.

One of the [CRMLeaf payroll tools](https://github.com/crmleaf). The arithmetic
and the dated statutory rate tables live in
[`crmleaf/payroll-core`](https://github.com/crmleaf/payroll-core); this package is
the thin skin that makes one calculator installable, mountable and embeddable on
its own.

> [!NOTE]
> A wrong figure or an out-of-date rate is almost always a
> [`payroll-core`](https://github.com/crmleaf/payroll-core/issues) matter, since
> that is where the tables live. Anything about this tool's routes, views or
> browser asset belongs here.

## Install

**Composer** - Laravel auto-discovers the service provider, so this is the whole
setup:

```bash
composer require crmleaf/gratuity-calculator
```

**npm** - the same calculation, re-exported from `@crmleaf/payroll-js` so you can
install this one tool and nothing else:

```bash
npm install @crmleaf/gratuity-calculator
```

> [!NOTE]
> Not on npm yet. The script-tag route below needs no registry and works today.
> Installing this package straight from git will not resolve
> `@crmleaf/payroll-js`, which is not published yet either.

**A plain script tag** - no build step, no bundler, no server. Build the browser
bundle once and serve the file yourself:

```html
<script src="/js/payroll.min.js"></script>
<script>
const result = CrmleafPayroll.gratuity({
  lastDrawnSalary: 45000,
  yearsOfService: 7,
  monthsOfService: 8,
});
console.log(result.explain);
</script>
```

`payroll.min.js` is the single-file browser build. Get it by running
`npm run build` in [`@crmleaf/payroll-js`][js] and copying `dist/payroll.min.js`
into whatever your site serves as static assets.

> A hosted CDN build is coming soon, which will reduce this to a single URL.
> Serving the file yourself works today and keeps working afterwards - it is the
> only option that needs no third-party request, so plenty of projects will want
> to stay on it.

### See it working first

`demo/index.html` in this repository is a working copy of Gratuity Calculator in one file:
the form, the calculation and the working, with no build step and no server. Drop
`payroll.min.js` beside it and open it from disk.

```bash
cp /path/to/payroll-js/dist/payroll.min.js demo/
open demo/index.html
```

Nothing on that page reaches the network, which is the point: it is a calculator
people paste salary figures into.

## Use it

**Plain PHP**, no framework and no container:

```php
use Crmleaf\Payroll\Calculators\GratuityCalculator;
use Crmleaf\Payroll\Money;

$result = (new GratuityCalculator())->calculate(
    lastDrawnSalary: Money::fromRupees(45_000),
    yearsOfService: 7,
    monthsOfService: 8,
);

echo $result->explain();      // the formula with the real operands in it
echo $result->workings();     // every step, one per line, with its citation
print_r($result->toArray());  // snake_case, ready for JSON
```

**Laravel** - resolve it from the container, or type-hint it anywhere:

```php
use Crmleaf\Payroll\Calculators\GratuityCalculator;

public function show(GratuityCalculator $calculator)
{
    return $calculator->calculate(
        lastDrawnSalary: Money::fromRupees(45_000),
        yearsOfService: 7,
        monthsOfService: 8,
    )->toArray();
}
```

**Blade** - one component, no controller:

```blade
<x-crmleaf::gratuity-calculator />
```

**HTTP** - off by default. Publish the config and turn the route on:

```bash
php artisan vendor:publish --tag=gratuity-calculator-config
```

```php
// config/gratuity-calculator.php
'route' => ['enabled' => true, 'prefix' => 'tools'],
```

```bash
curl -X POST https://example.test/tools/gratuity-calculator \
  -H 'Content-Type: application/json' \
  -H 'Accept: application/json' \
  -d '{"last_drawn_salary":45000,"years_of_service":7,"months_of_service":8}'
```

The JSON response carries the figures, the working and the statutory citations:

```json
{
  "tool": "gratuity-calculator",
  "data": { "…": "every figure, snake_case, with a *_formatted twin" },
  "explain": "the formula with the real operands substituted",
  "working": [{ "label": "…", "amount": 0, "formula": "…", "citation": "…" }],
  "citations": ["…"]
}
```

**JavaScript**:

```js
import { gratuity } from '@crmleaf/gratuity-calculator';

const result = gratuity({
  lastDrawnSalary: 45000,
  yearsOfService: 7,
  monthsOfService: 8,
});
```

## No server needed

The maths here is arithmetic over versioned rate tables, so it runs anywhere.
The published asset binds the markup and computes in the browser:

```bash
php artisan vendor:publish --tag=gratuity-calculator-assets
```

```html
<section data-crmleaf-tool="gratuity-calculator">
  <form data-crmleaf-form> … </form>
  <div data-crmleaf-output hidden></div>
</section>

<script src="/js/payroll.min.js"></script>
<script src="/vendor/gratuity-calculator/gratuity-calculator.js"></script>
```

If the browser build is absent the script does nothing and the form posts to the
server instead, so the page works either way.

## Inputs

| Field | Type | Required | Default | Notes |
|-------|------|----------|---------|-------|
| `last_drawn_salary` | money (₹) | Yes | `45000` |  |
| `years_of_service` | integer | Yes | `7` |  |
| `months_of_service` | integer | No | `8` | More than six rounds the year up; six or fewer is discarded. |
| `covered` | boolean | No | `true` | Covered divides by 26 working days; not covered divides by 30 calendar days. |
| `separation_reason` | one of `resignation`, `retirement`, `superannuation`, `termination`, `retrenchment`, `death`, `disablement` | No | `"resignation"` | Death and disablement waive the five-year qualifying period. |
| `as_of` | date (YYYY-MM-DD) | No | - |  |

Optional fields you leave out are omitted from the call entirely, so the
calculator's own documented defaults apply.

Every figure here rests on a statutory rate, so the call takes `as_of`. Set it
and the calculation runs on the rates in force on that date, which is what makes
a prior year recomputable rather than merely rememberable.

## Statutory basis

Payment of Gratuity Act 1972, section 4 - fifteen days' wages for every completed year, divided by 26 for a covered establishment and by 30 for one that is not - with the ₹20 lakh exemption under section 10(10) of the Income-tax Act 1961.

Rates are data, not code: they live in dated tables with a cited source in
`crmleaf/payroll-core`, so a rate change is a new dated entry rather than an edit
to a constant.

> [!IMPORTANT]
> This package implements our reading of the applicable statutes and is provided
> without warranty. It is a calculation library, not tax advice. Verify against
> your own compliance obligations before relying on the output for statutory
> filing.

## Publishing

| Tag | Publishes |
|-----|-----------|
| `gratuity-calculator-config` | `config/gratuity-calculator.php` |
| `gratuity-calculator-views` | `resources/views/vendor/gratuity-calculator` |
| `gratuity-calculator-assets` | `public/vendor/gratuity-calculator` |

## Licence

[MIT](LICENSE) © CRMLeaf. Use it commercially, embed it, fork it.

[js]: https://github.com/crmleaf/payroll-js
