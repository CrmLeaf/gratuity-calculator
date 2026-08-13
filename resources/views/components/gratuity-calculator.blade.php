@props([
    'action' => null,
    'method' => 'post',
    'defaults' => [],
    'input' => [],
    'result' => null,
    'error' => null,
    'heading' => 'Gratuity Calculator',
    'tagline' => 'Payment of Gratuity Act 1972 formula with the exemption cap applied.',
    'showWorking' => true,
])

<section class="crmleaf-tool crmleaf-tool--gratuity-calculator" data-crmleaf-tool="gratuity-calculator">
    <header class="crmleaf-tool__header">
        <h2 class="crmleaf-tool__heading">{{ $heading }}</h2>
        <p class="crmleaf-tool__tagline">{{ $tagline }}</p>
    </header>

    @if ($error)
        <p class="crmleaf-tool__error" role="alert">{{ $error }}</p>
    @endif

    <form class="crmleaf-tool__form"
          method="{{ strtolower($method) === 'get' ? 'get' : 'post' }}"
          action="{{ $action }}"
          data-crmleaf-form>
        @if (strtolower($method) !== 'get')
            @csrf
        @endif

        <label class="crmleaf-field">
            <span>Last drawn monthly salary (basic + DA)</span>
            <input type="number" step="0.01" min="0" inputmode="decimal" name="last_drawn_salary" value="{{ old('last_drawn_salary', $input['last_drawn_salary'] ?? ($defaults['last_drawn_salary'] ?? '')) }}" required>
        </label>

        <label class="crmleaf-field">
            <span>Completed years of service</span>
            <input type="number" step="1" inputmode="numeric" name="years_of_service" value="{{ old('years_of_service', $input['years_of_service'] ?? ($defaults['years_of_service'] ?? '')) }}" required>
        </label>

        <label class="crmleaf-field">
            <span>Additional months</span>
            <input type="number" step="1" inputmode="numeric" name="months_of_service" value="{{ old('months_of_service', $input['months_of_service'] ?? ($defaults['months_of_service'] ?? '')) }}">
            <small>More than six rounds the year up; six or fewer is discarded.</small>
        </label>

        <label class="crmleaf-field crmleaf-field--bool">
            <input type="hidden" name="covered" value="0">
            <input type="checkbox" name="covered" value="1" @checked(old('covered', $input['covered'] ?? ($defaults['covered'] ?? false)))>
            <span>Establishment is covered by the Act</span>
            <small>Covered divides by 26 working days; not covered divides by 30 calendar days.</small>
        </label>

        <label class="crmleaf-field">
            <span>Reason for separation</span>
            <select name="separation_reason">
                <option value="resignation" @selected(old('separation_reason', $input['separation_reason'] ?? ($defaults['separation_reason'] ?? '')) === 'resignation')>Resignation</option>
                <option value="retirement" @selected(old('separation_reason', $input['separation_reason'] ?? ($defaults['separation_reason'] ?? '')) === 'retirement')>Retirement</option>
                <option value="superannuation" @selected(old('separation_reason', $input['separation_reason'] ?? ($defaults['separation_reason'] ?? '')) === 'superannuation')>Superannuation</option>
                <option value="termination" @selected(old('separation_reason', $input['separation_reason'] ?? ($defaults['separation_reason'] ?? '')) === 'termination')>Termination</option>
                <option value="retrenchment" @selected(old('separation_reason', $input['separation_reason'] ?? ($defaults['separation_reason'] ?? '')) === 'retrenchment')>Retrenchment</option>
                <option value="death" @selected(old('separation_reason', $input['separation_reason'] ?? ($defaults['separation_reason'] ?? '')) === 'death')>Death</option>
                <option value="disablement" @selected(old('separation_reason', $input['separation_reason'] ?? ($defaults['separation_reason'] ?? '')) === 'disablement')>Disablement</option>
            </select>
            <small>Death and disablement waive the five-year qualifying period.</small>
        </label>

        <label class="crmleaf-field">
            <span>Rates as on</span>
            <input type="date" name="as_of" value="{{ old('as_of', $input['as_of'] ?? ($defaults['as_of'] ?? '')) }}">
        </label>

        <input type="hidden" name="tool" value="gratuity-calculator">

        <div class="crmleaf-tool__actions">
            <button type="submit" class="crmleaf-tool__submit">Calculate</button>
        </div>
    </form>

    {{-- The client-side path writes its answer here; the server-side path fills it below. --}}
    <div class="crmleaf-tool__output" data-crmleaf-output hidden></div>

    @if ($result)
        <div class="crmleaf-tool__result">
            <p class="crmleaf-tool__explain"><code>{{ $result->explain() }}</code></p>

            <table class="crmleaf-tool__figures">
                <tbody>
                @foreach ($result->toArray() as $key => $value)
                    @continue(is_array($value) || str_ends_with((string) $key, '_formatted'))
                    <tr>
                        <th scope="row">{{ ucfirst(str_replace('_', ' ', (string) $key)) }}</th>
                        <td>{{ $result->toArray()[$key.'_formatted'] ?? (is_bool($value) ? ($value ? 'Yes' : 'No') : $value) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            @if ($showWorking && count($result->steps()))
                <details class="crmleaf-tool__working" open>
                    <summary>How this was worked out</summary>
                    <ol>
                        @foreach ($result->steps() as $step)
                            <li>
                                <span class="crmleaf-step__label">{{ $step->label }}</span>
                                @if ($step->amount)
                                    <span class="crmleaf-step__amount">{{ $step->amount->format() }}</span>
                                @endif
                                @if ($step->formula)
                                    <code class="crmleaf-step__formula">{{ $step->formula }}</code>
                                @endif
                                @if ($step->citation)
                                    <small class="crmleaf-step__citation">{{ $step->citation }}</small>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </details>
            @endif

            @if (count($result->citations()))
                <ul class="crmleaf-tool__citations">
                    @foreach ($result->citations() as $citation)
                        <li>{{ $citation }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif
</section>
