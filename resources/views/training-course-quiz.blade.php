{{-- Renders the "Check Your Understanding" quiz for a single module. --}}
{{-- Expects: $module (int), $questions (array of ['question'=>..,'options'=>[..]]), $progress (module progress array), $passingScore (int) --}}
<div class="crs-quiz" data-module="{{ $module }}" data-passing="{{ $passingScore }}">
    <div class="crs-quiz-head">
        <h4>Check Your Understanding</h4>
    </div>
    <p class="crs-quiz-sub">Score at least {{ $passingScore }}% to complete this module{{ isset($questions) ? '' : '' }}{{ $module < 6 ? ' and unlock Module ' . sprintf('%02d', $module + 1) : '' }}.</p>

    @if ($progress['completed'])
        <div class="crs-already-passed">✓ Already completed — best score {{ $progress['best_score'] }}% ({{ $progress['attempts'] }} attempt{{ $progress['attempts'] === 1 ? '' : 's' }})</div>
    @endif

    <div class="crs-quiz-error" hidden></div>

    <form class="crs-quiz-form">
        @foreach ($questions as $i => $q)
            <div class="crs-quiz-question" data-index="{{ $i }}">
                <p class="crs-quiz-q-text">{{ $i + 1 }}. {{ $q['question'] }}</p>
                <div class="crs-quiz-options">
                    @foreach ($q['options'] as $oi => $opt)
                        <label class="crs-quiz-option">
                            <input type="radio" name="module{{ $module }}-q{{ $i }}" value="{{ $oi }}">
                            <span>{{ $opt }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach

        <div class="crs-quiz-actions">
            <button type="submit" class="crs-quiz-submit">Submit Quiz</button>
        </div>
    </form>

    <div class="crs-quiz-result" hidden></div>
</div>
