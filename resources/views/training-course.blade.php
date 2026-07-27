@extends('layouts.academy')

@section('title', 'ArkCrest Sales Academy')

@section('content')
            <section class="training-hero">
                <div class="training-copy">
                    <div class="training-eyebrow">ArkCrest Sales Academy</div>
                    <h1>Build confidence. Master the process. <em>Close with integrity.</em></h1>
                    <p>
                        Welcome, {{ $trainingName }}. This training mockup introduces the future learning path for ArkCrest staff, sales associates, and real estate agents.
                    </p>
                    <div class="training-actions">
                        <button type="button" class="start-course-btn" onclick="startMockCourse()">
                            <svg fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            Start Course
                        </button>
                        <a href="#course-modules" class="outline-course-btn">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                            View Modules
                        </a>
                    </div>
                    <div id="courseNotice" class="course-notice">This is currently a course mockup. Lesson videos, quizzes, progress tracking, and certificates can be connected in the next phase.</div>
                </div>

                <aside class="course-overview">
                    <div class="overview-label">Your Learning Path</div>
                    <div class="overview-title">Real Estate Sales Foundations</div>
                    <div class="progress-head"><span>Course progress</span><strong>0%</strong></div>
                    <div class="progress-track"><div class="progress-bar"></div></div>
                    <div class="overview-stats">
                        <div class="overview-stat"><strong>7</strong><span>Modules</span></div>
                        <div class="overview-stat"><strong>18</strong><span>Lessons</span></div>
                        <div class="overview-stat"><strong>4.5h</strong><span>Duration</span></div>
                    </div>
                </aside>
            </section>

            <section class="training-section" id="course-modules">
                <div class="section-heading">
                    <div>
                        <h2>Course Modules</h2>
                        <p>A mock learning path designed around ArkCrest sales and client service standards.</p>
                    </div>
                    <div class="mockup-badge">Preview Version</div>
                </div>

                <div class="module-grid">
                    <article class="module-card" id="module-01">
                        <div class="module-number">01</div>
                        <h3>Real Estate Sales Fundamentals</h3>
                        <p>Understand the agent's role, the sales cycle, buyer expectations, and professional conduct.</p>
                        <div class="module-meta"><span>3 Lessons · 35 min</span><span class="module-status">Ready</span></div>
                    </article>
                    <article class="module-card" id="module-02">
                        <div class="module-number">02</div>
                        <h3>Property and Market Knowledge</h3>
                        <p>Present developments clearly, explain value drivers, and match properties to client goals.</p>
                        <div class="module-meta"><span>3 Lessons · 45 min</span><span class="module-status locked">Locked</span></div>
                    </article>
                    <article class="module-card" id="module-03">
                        <div class="module-number">03</div>
                        <h3>Client Discovery and Qualification</h3>
                        <p>Ask better questions, identify priorities, qualify leads, and prepare relevant recommendations.</p>
                        <div class="module-meta"><span>3 Lessons · 40 min</span><span class="module-status locked">Locked</span></div>
                    </article>
                    <article class="module-card" id="module-04">
                        <div class="module-number">04</div>
                        <h3>Site Visits and Property Presentation</h3>
                        <p>Prepare professional site visits and communicate features, benefits, and investment potential.</p>
                        <div class="module-meta"><span>3 Lessons · 50 min</span><span class="module-status locked">Locked</span></div>
                    </article>
                    <article class="module-card" id="module-05">
                        <div class="module-number">05</div>
                        <h3>Documentation and Ethical Selling</h3>
                        <p>Follow responsible documentation practices and protect the client through transparent communication.</p>
                        <div class="module-meta"><span>3 Lessons · 45 min</span><span class="module-status locked">Locked</span></div>
                    </article>
                    <article class="module-card" id="module-06">
                        <div class="module-number">06</div>
                        <h3>Closing and After-Sales Service</h3>
                        <p>Handle objections, guide the decision, complete the handoff, and maintain long-term relationships.</p>
                        <div class="module-meta"><span>3 Lessons · 35 min</span><span class="module-status locked">Locked</span></div>
                    </article>
                    <a href="{{ route('practice') }}" class="module-card module-card-link" id="module-07">
                        <div class="module-number">07</div>
                        <h3>Persuasion Practice</h3>
                        <p>Practice live persuasion and closing skills against an AI buyer roleplay, then get scored feedback.</p>
                        <div class="module-meta"><span>AI Roleplay · Self-paced</span><span class="module-status">Ready</span></div>
                    </a>
                </div>
            </section>

            <section class="training-section">
                <div class="section-heading">
                    <div>
                        <h2>First Lesson Preview</h2>
                        <p>The content below is visual scaffolding for the future training experience.</p>
                    </div>
                </div>

                <div class="learning-grid">
                    <article class="lesson-preview">
                        <div class="lesson-thumbnail">
                            <div class="play-button">
                                <svg fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            </div>
                        </div>
                        <div class="lesson-content">
                            <div class="lesson-kicker">Module 01 · Lesson 01</div>
                            <h3>The ArkCrest Standard of Client Service</h3>
                            <p>Learn how professional preparation, product knowledge, transparency, and timely follow-through shape a trusted client experience.</p>
                            <div class="lesson-list">
                                <div class="lesson-item"><span>1</span><span>Represent the brand professionally</span></div>
                                <div class="lesson-item"><span>2</span><span>Understand the client's real objective</span></div>
                                <div class="lesson-item"><span>3</span><span>Guide every next step with clarity</span></div>
                            </div>
                        </div>
                    </article>

                    <aside class="academy-panel">
                        <h3>Planned Course Features</h3>
                        <p>These items can be implemented when the final course materials and rules are ready.</p>
                        <div class="feature-list">
                            <div class="feature-row">
                                <div class="feature-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M4 6h9a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2z"/></svg></div>
                                <div><strong>Video Lessons</strong><span>Structured modules with lesson playback.</span></div>
                            </div>
                            <div class="feature-row">
                                <div class="feature-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                                <div><strong>Knowledge Checks</strong><span>Short quizzes after each learning section.</span></div>
                            </div>
                            <div class="feature-row">
                                <div class="feature-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></div>
                                <div><strong>Completion Certificate</strong><span>Certificate generation after requirements are met.</span></div>
                            </div>
                        </div>
                    </aside>
                </div>
            </section>

@endsection

@push('academy-scripts')
    <script>
        function startMockCourse() {
            var notice = document.getElementById('courseNotice');
            notice.style.display = 'block';
            notice.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    </script>
@endpush