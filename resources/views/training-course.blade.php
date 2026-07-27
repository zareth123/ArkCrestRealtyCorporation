@extends('layouts.academy')

@section('title', 'ArkCrest Sales Academy')

@section('content')
            <section class="training-hero">
                <div class="training-copy">
                    <div class="training-eyebrow">ArkCrest Sales Academy</div>
                    <h1>Build confidence. Master the process. <em>Close with integrity.</em></h1>
                    <p>
                        Welcome, {{ $trainingName }}. This is the Real Estate Agent Training course — practical,
                        Philippine-focused lessons for every stage of the sales cycle, with a short quiz to confirm
                        your understanding before each next module unlocks.
                    </p>
                    <div class="training-actions">
                        <button type="button" class="start-course-btn" id="crsContinueBtn" data-target="module-{{ sprintf('%02d', $continueModule) }}">
                            <svg fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            {{ $completedCount > 0 ? 'Continue Course' : 'Start Course' }}
                        </button>
                        <a href="#course-modules" class="outline-course-btn">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                            View Modules
                        </a>
                    </div>
                </div>

                <aside class="course-overview">
                    <div class="overview-label">Your Learning Path</div>
                    <div class="overview-title">Real Estate Sales Foundations</div>
                    <div class="progress-head"><span>Course progress</span><strong>{{ $overallPercent }}%</strong></div>
                    <div class="progress-track"><div class="progress-bar" style="width: {{ $overallPercent }}%"></div></div>
                    <div class="overview-stats">
                        <div class="overview-stat"><strong>{{ $completedCount }}/6</strong><span>Completed</span></div>
                        <div class="overview-stat"><strong>6</strong><span>Live Now</span></div>
                        <div class="overview-stat"><strong>4.5h</strong><span>Full Course</span></div>
                    </div>
                </aside>
            </section>

            <section class="training-section" id="course-modules">
                <div class="section-heading">
                    <div>
                        <h2>Course Modules</h2>
                        <p>Complete each module's quiz to unlock the next one. Your progress is saved to your account.</p>
                    </div>
                </div>

                <div class="module-grid">
                    @foreach ($progress as $m)
                        @php
                            $cardHref = $m['unlocked'] ? '#module-' . sprintf('%02d', $m['number']) : null;
                            $statusLabel = $m['completed'] ? 'Completed' : ($m['unlocked'] ? 'Ready' : ($m['implemented'] ? 'Locked' : 'Coming Soon'));
                            $statusClass = $m['completed'] ? 'is-complete' : (!$m['unlocked'] ? 'locked' : '');
                        @endphp
                        @if ($cardHref)
                            <a href="{{ $cardHref }}" class="module-card module-card-link" id="overview-module-{{ sprintf('%02d', $m['number']) }}">
                        @else
                            <article class="module-card" id="overview-module-{{ sprintf('%02d', $m['number']) }}">
                        @endif
                            <div class="module-number">{{ sprintf('%02d', $m['number']) }}</div>
                            <h3>{{ $m['title'] }}</h3>
                            <p>{{ $m['summary'] }}</p>
                            <div class="module-meta">
                                <span>{{ $m['lessons'] }} Lessons · {{ $m['minutes'] }} min</span>
                                <span class="module-status {{ $statusClass }}">{{ $statusLabel }}</span>
                            </div>
                        @if ($cardHref)
                            </a>
                        @else
                            </article>
                        @endif
                    @endforeach
                    <a href="{{ route('practice') }}" class="module-card module-card-link" id="module-07">
                        <div class="module-number">07</div>
                        <h3>Persuasion Practice</h3>
                        <p>Practice live persuasion and closing skills against an AI buyer roleplay, then get scored feedback.</p>
                        <div class="module-meta"><span>AI Roleplay · Self-paced</span><span class="module-status">Ready</span></div>
                    </a>
                </div>
            </section>

            {{-- ============================= MODULE 1 ============================= --}}
            <section class="crs-module-detail {{ $progress[1]['unlocked'] ? '' : 'is-locked' }}" id="module-01">
                <button type="button" class="crs-module-detail-head" {{ $progress[1]['unlocked'] ? '' : 'disabled' }}>
                    <div class="crs-module-detail-badge">Module 01</div>
                    <div class="crs-module-detail-titlewrap">
                        <h2>Real Estate Sales Fundamentals</h2>
                        <p>Understand the baseline responsibilities, professional standards, and operational cycles of a successful real estate agent.</p>
                    </div>
                    <div class="crs-module-detail-meta">
                        <span>⏱ 35 min</span>
                        <span>3 Lessons</span>
                        @if ($progress[1]['completed'])
                            <span class="crs-status crs-status-complete">✓ Completed · Best {{ $progress[1]['best_score'] }}%</span>
                        @elseif ($progress[1]['unlocked'])
                            <span class="crs-status crs-status-ready">Ready</span>
                        @else
                            <span class="crs-status crs-status-locked">Locked</span>
                        @endif
                        <svg class="crs-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </button>

                @if (!$progress[1]['unlocked'])
                    <div class="crs-locked-panel">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <p>This module isn't available yet.</p>
                    </div>
                @else
                <div class="crs-module-body">
                    <div class="crs-objective"><strong>Objective:</strong> Understand the baseline responsibilities, professional standards, and operational cycles of a successful real estate agent.</div>

                    <article class="crs-lesson">
                        <h3><span class="crs-lesson-num">Lesson 1.1</span> The Agent's Role &amp; The Complete Sales Cycle</h3>
                        <p>Many people still picture a real estate agent as someone who simply drives buyers around and unlocks doors. In reality, a modern agent works as a <strong>consultant</strong> who explains market conditions and property value, a <strong>project manager</strong> who keeps a multi-week (sometimes multi-month) transaction moving forward, and a <strong>negotiator</strong> who protects the client's interests at the table.</p>
                        <p>Every deal follows a recognizable cycle:</p>
                        <div class="crs-cycle">
                            <div class="crs-cycle-step"><span>1</span><strong>Prospecting</strong><small>Generating leads through referrals, social media, broker networks, and site visit sign-ups.</small></div>
                            <div class="crs-cycle-step"><span>2</span><strong>Discovery</strong><small>Understanding the buyer's budget, must-haves, financing plan, and timeline.</small></div>
                            <div class="crs-cycle-step"><span>3</span><strong>Showing</strong><small>Guiding site visits or virtual tours, highlighting both the unit and the community.</small></div>
                            <div class="crs-cycle-step"><span>4</span><strong>Negotiating</strong><small>Handling price, payment terms, reservation fees, and move-in timelines.</small></div>
                            <div class="crs-cycle-step"><span>5</span><strong>Escrow / Closing</strong><small>Reservation, Contract to Sell, loan processing, Deed of Absolute Sale, and title transfer.</small></div>
                            <div class="crs-cycle-step"><span>6</span><strong>Post-Sale</strong><small>Turnover support, warranty concerns, and building the relationship for referrals.</small></div>
                        </div>
                        <div class="crs-scenario">
                            <strong>🇵🇭 Philippine Scenario</strong>
                            <p>A buyer messages you on Facebook about a 2-bedroom condo unit in Pasig. Instead of only sending the price list, you ask about their financing plan, ideal move-in date, and must-haves. This lets you shortlist 2–3 truly matching units instead of flooding them with 15 random listings — saving both of you time and building early trust.</p>
                        </div>
                        <div class="crs-takeaway-line"><strong>Core Takeaway:</strong> You are not just selling a physical structure — you are managing a complex, multi-step transaction where your primary value is keeping the deal on track and minimizing client stress.</div>
                    </article>

                    <article class="crs-lesson">
                        <h3><span class="crs-lesson-num">Lesson 1.2</span> Navigating Buyer Expectations</h3>
                        <p>Filipino buyers today do their homework before they ever message an agent. They've scrolled listing sites, developer pages, and Facebook Marketplace, and probably estimated their own monthly amortization with an online calculator. This changes what they actually need from you.</p>
                        <p><strong>What today's buyer expects from you:</strong></p>
                        <ul class="crs-list">
                            <li>Fast, honest replies — ideally within a few hours, not days</li>
                            <li>Straight answers about a unit's flaws (noise, view obstruction, association dues, flood history) — not just the highlights</li>
                            <li>Local insight: traffic patterns, nearby developments, dues trends, upcoming infrastructure</li>
                            <li>Clear next steps at every stage, so they're never left guessing</li>
                        </ul>
                        <div class="crs-scenario">
                            <strong>🇵🇭 Philippine Scenario</strong>
                            <p>A client asks about flood risk in a subdivision in Cavite. Instead of avoiding the question, you share what you actually know — drainage history, elevation, and whether the area flooded in past typhoons — and offer to confirm specifics with the developer. This honesty is what turns a one-time inquiry into a signed reservation.</p>
                        </div>
                        <div class="crs-takeaway-line"><strong>Core Takeaway:</strong> Because buyers already have the data, they expect you to provide the interpretation. They expect speed in communication, absolute transparency regarding property flaws, and local market expertise that an app cannot provide.</div>
                    </article>

                    <article class="crs-lesson">
                        <h3><span class="crs-lesson-num">Lesson 1.3</span> Professional Conduct &amp; Daily Operations</h3>
                        <p>Top-producing agents aren't necessarily the most naturally talented — they're the most <strong>consistent</strong>. Three daily habits build a professional, trustworthy brand:</p>
                        <ul class="crs-list">
                            <li><strong>Time blocking</strong> — dedicating specific hours to prospecting, follow-ups, paperwork, and site visits instead of reacting all day.</li>
                            <li><strong>CRM discipline</strong> — logging every lead, preference, and follow-up date so no client falls through the cracks.</li>
                            <li><strong>Standardized communication</strong> — replying within a guaranteed timeframe, confirming site visits a day ahead, and following up after every meeting with a clear next step.</li>
                        </ul>
                        <div class="crs-scenario">
                            <strong>🇵🇭 Philippine Scenario</strong>
                            <p>You commit to a 9:00 AM site visit in a subdivision in Bulacan. Traffic is unpredictable, so you leave early and arrive by 8:45 — giving you time to check that the model unit and amenities are presentable before the client arrives. That small margin makes your client feel prioritized before you've said a single word.</p>
                        </div>
                        <div class="crs-takeaway-line"><strong>Core Takeaway:</strong> Consistency breeds trust. Arriving early to site visits, returning calls within a guaranteed timeframe, and maintaining a polished, professional demeanor are the foundations of your brand.</div>
                    </article>

                    <div class="crs-callout">
                        <strong>📌 TAKE NOTE — The "Consultant" Mindset</strong>
                        <p>Clients don't need you to read the brochure to them — they can do that on their phone. What they need is a trusted advisor who protects their financial interests and helps them make a confident decision. Always position yourself as a consultant, not a salesperson.</p>
                    </div>

                    <div class="crs-key-takeaways">
                        <h4>Key Takeaways</h4>
                        <ul>
                            <li>The agent's real value is managing the full sales cycle — Prospecting to Post-Sale — not just showing units.</li>
                            <li>Modern buyers already have data; they need your interpretation, honesty, and local expertise.</li>
                            <li>Daily discipline (time blocking, CRM, fast communication) separates top producers from the rest.</li>
                            <li>Always operate as a consultant protecting the client's interests, not a salesperson pushing a transaction.</li>
                        </ul>
                    </div>

                    @include('training-course-quiz', ['module' => 1, 'questions' => $quizzes[1], 'progress' => $progress[1], 'passingScore' => $passingScore])
                </div>
                @endif
            </section>

            {{-- ============================= MODULE 2 ============================= --}}
            <section class="crs-module-detail {{ $progress[2]['unlocked'] ? '' : 'is-locked' }}" id="module-02">
                <button type="button" class="crs-module-detail-head" {{ $progress[2]['unlocked'] ? '' : 'disabled' }}>
                    <div class="crs-module-detail-badge">Module 02</div>
                    <div class="crs-module-detail-titlewrap">
                        <h2>Property and Market Knowledge</h2>
                        <p>Master the ability to accurately assess property value and communicate a development's specific advantages to the right audience.</p>
                    </div>
                    <div class="crs-module-detail-meta">
                        <span>⏱ 45 min</span>
                        <span>3 Lessons</span>
                        @if ($progress[2]['completed'])
                            <span class="crs-status crs-status-complete">✓ Completed · Best {{ $progress[2]['best_score'] }}%</span>
                        @elseif ($progress[2]['unlocked'])
                            <span class="crs-status crs-status-ready">Ready</span>
                        @else
                            <span class="crs-status crs-status-locked">Locked</span>
                        @endif
                        <svg class="crs-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </button>

                @if (!$progress[2]['unlocked'])
                    <div class="crs-locked-panel">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <p>Complete <strong>Module 01 — Real Estate Sales Fundamentals</strong> to unlock this module.</p>
                    </div>
                @else
                <div class="crs-module-body">
                    <div class="crs-objective"><strong>Objective:</strong> Master the ability to accurately assess property value and communicate a development's specific advantages to the right audience.</div>

                    <article class="crs-lesson">
                        <h3><span class="crs-lesson-num">Lesson 2.1</span> Presenting Developments Clearly</h3>
                        <p>Whether it's a master-planned subdivision in Cavite, a mid-rise condo in Quezon City, or a high-rise tower in BGC, buyers aren't just buying four walls — they're buying into a community and a lifestyle. To present a development well, you need to know its <strong>site development plan</strong> (where each phase or building sits), its <strong>amenity locations</strong> (pool, clubhouse, retail row, parks), and which phases are already turned over versus still under construction.</p>
                        <div class="crs-scenario">
                            <strong>🇵🇭 Philippine Scenario</strong>
                            <p>Instead of opening with "This unit is 24 square meters with a balcony," you open with "This community has a resort-style pool, a co-working lounge, and 24/7 security, five minutes from the nearest mall and business district." Once the client is sold on the lifestyle, the specific unit details land far better.</p>
                        </div>
                        <div class="crs-takeaway-line"><strong>Core Takeaway:</strong> When presenting a development, always sell the macro (the community lifestyle and amenities) before selling the micro (the specific unit).</div>
                    </article>

                    <article class="crs-lesson">
                        <h3><span class="crs-lesson-num">Lesson 2.2</span> Explaining Value Drivers</h3>
                        <p>Property values in the Philippines rarely move because of the house alone — they move because of what's happening around it: a new expressway exit, an MRT/LRT extension or the North-South Commuter Railway, a new mall or business park nearby, or a well-regarded school moving into the area. Agents who track local infrastructure and zoning changes can explain — and even anticipate — why a property is likely to appreciate.</p>
                        <div class="crs-scenario">
                            <strong>🇵🇭 Philippine Scenario</strong>
                            <p>A property near a planned expressway interchange in Laguna may look "in the middle of nowhere" today. But if you can explain the interchange's expected completion date and its impact on commute time to Makati or BGC, you help the client see the investment angle — not just the current view from the window.</p>
                        </div>
                        <div class="crs-takeaway-line"><strong>Core Takeaway:</strong> Value is rarely just about the house itself. It is driven heavily by proximity to economic hubs and future city planning. You must know your local municipality's future development plans.</div>
                    </article>

                    <article class="crs-lesson">
                        <h3><span class="crs-lesson-num">Lesson 2.3</span> Matching Properties to Client Goals</h3>
                        <p>Not every buyer needs the same type of property. A growing family with school-age children usually prioritizes space, a nearby school, and a subdivision with a playground. A young professional working in a CBD often prioritizes commute time, security, and a lock-and-leave lifestyle — making a transit-oriented condo a stronger fit than a house-and-lot two hours away.</p>
                        <div class="crs-scenario">
                            <strong>🇵🇭 Philippine Scenario</strong>
                            <p>A young professional working in Ortigas tells you their main concern is commute time. Instead of sending listings across five different cities, you shortlist three condo options within a 15-minute radius of their office — respecting their time and dramatically increasing the chance of a decision within one or two site visits.</p>
                        </div>
                        <div class="crs-takeaway-line"><strong>Core Takeaway:</strong> Showing fewer, highly targeted properties is much more effective than showing 15 random homes. Quality of matching always beats quantity of showings.</div>
                    </article>

                    <div class="crs-callout">
                        <strong>📌 TAKE NOTE — Know Your Ground</strong>
                        <p>You don't need to be a licensed urban planner, but you should always be able to answer: "Why will this area be worth more in five years?" If you can't answer that, spend time with your municipality's zoning maps and infrastructure roadmap before your next site visit.</p>
                    </div>

                    <div class="crs-key-takeaways">
                        <h4>Key Takeaways</h4>
                        <ul>
                            <li>Sell the community and lifestyle first, then the specific unit.</li>
                            <li>Property value is driven by infrastructure, zoning, and future development — not just the structure itself.</li>
                            <li>Stay informed on your local municipality's infrastructure and zoning plans.</li>
                            <li>Match a small number of highly relevant properties to the client's actual goals instead of showing everything available.</li>
                        </ul>
                    </div>

                    @include('training-course-quiz', ['module' => 2, 'questions' => $quizzes[2], 'progress' => $progress[2], 'passingScore' => $passingScore])
                </div>
                @endif
            </section>

            {{-- ============================= MODULE 3 ============================= --}}
            <section class="crs-module-detail {{ $progress[3]['unlocked'] ? '' : 'is-locked' }}" id="module-03">
                <button type="button" class="crs-module-detail-head" {{ $progress[3]['unlocked'] ? '' : 'disabled' }}>
                    <div class="crs-module-detail-badge">Module 03</div>
                    <div class="crs-module-detail-titlewrap">
                        <h2>Client Discovery and Qualification</h2>
                        <p>Transition from pitching to diagnosing. Learn to uncover the client's true buying motives and financial readiness before stepping foot on a property.</p>
                    </div>
                    <div class="crs-module-detail-meta">
                        <span>⏱ 40 min</span>
                        <span>3 Lessons</span>
                        @if ($progress[3]['completed'])
                            <span class="crs-status crs-status-complete">✓ Completed · Best {{ $progress[3]['best_score'] }}%</span>
                        @elseif ($progress[3]['unlocked'])
                            <span class="crs-status crs-status-ready">Ready</span>
                        @else
                            <span class="crs-status crs-status-locked">Locked</span>
                        @endif
                        <svg class="crs-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </button>

                @if (!$progress[3]['unlocked'])
                    <div class="crs-locked-panel">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <p>Complete <strong>Module 02 — Property and Market Knowledge</strong> to unlock this module.</p>
                    </div>
                @else
                <div class="crs-module-body">
                    <div class="crs-objective"><strong>Objective:</strong> Transition from pitching to diagnosing. Learn to uncover the client's true buying motives and financial readiness before stepping foot on a property.</div>

                    <article class="crs-lesson">
                        <h3><span class="crs-lesson-num">Lesson 3.1</span> Asking Better Questions (The Discovery Phase)</h3>
                        <p>It's tempting to jump straight into close-ended questions like "Do you want 3 bedrooms?" — but questions like that only confirm a checklist. What actually uncovers what a client needs is an open-ended, lifestyle-focused question: <em>"Walk me through how you use your current space on a weekend."</em> That single question can surface far more than a bedroom count ever will.</p>
                        <p>A client's initial request is almost always just the surface. Deep discovery questions uncover the <strong>emotional drivers</strong> underneath — the need for security, status, or convenience — that actually decide which property they'll say yes to.</p>
                        <div class="crs-scenario">
                            <strong>🇵🇭 Philippine Scenario</strong>
                            <p>A client tells you they want "a bigger condo." Instead of pulling up every larger unit in your inventory, you ask what a typical weekend looks like at home. They mention their in-laws visit often and the current unit has nowhere for guests to sit comfortably. Now you're not just shopping for square meters — you're shopping for a layout with a proper living area, which narrows your search dramatically.</p>
                        </div>
                        <div class="crs-takeaway-line"><strong>Core Takeaway:</strong> The client's initial request is often just the surface. Deep discovery questions uncover the emotional drivers — security, status, or convenience — behind the purchase.</div>
                    </article>

                    <article class="crs-lesson">
                        <h3><span class="crs-lesson-num">Lesson 3.2</span> Identifying Priorities &amp; Qualifying Leads</h3>
                        <p>Once you understand the "why," you need to confirm the "can they, and when." The <strong>BANT</strong> framework gives you a fast, repeatable way to qualify a lead:</p>
                        <div class="crs-cycle">
                            <div class="crs-cycle-step"><span>B</span><strong>Budget</strong><small>Are they pre-approved, or do they have proof of funds for the reservation and down payment?</small></div>
                            <div class="crs-cycle-step"><span>A</span><strong>Authority</strong><small>Are all decision-makers — spouse, co-borrower, parents — present in the conversation?</small></div>
                            <div class="crs-cycle-step"><span>N</span><strong>Need</strong><small>What is the actual pain point driving the purchase?</small></div>
                            <div class="crs-cycle-step"><span>T</span><strong>Timeline</strong><small>Do they need to move in 30 days, or are they planning 6 months out?</small></div>
                        </div>
                        <div class="crs-scenario">
                            <strong>🇵🇭 Philippine Scenario</strong>
                            <p>A prospect is enthusiastic about a house-and-lot in Cavite, but every time you ask about financing, they change the subject and mention they still need to "talk to my parents about the down payment." That's a Budget and Authority flag — worth clarifying gently before you invest a weekend driving them to three site visits.</p>
                        </div>
                        <div class="crs-takeaway-line"><strong>Core Takeaway:</strong> Time is your most valuable asset. Qualifying protects you from spending weeks working with buyers who are financially unable or unmotivated to purchase.</div>
                    </article>

                    <article class="crs-lesson">
                        <h3><span class="crs-lesson-num">Lesson 3.3</span> Preparing Relevant Recommendations</h3>
                        <p>With BANT answered, build the <strong>Shortlist</strong>: review the MLS or developer inventory and select 3 to 4 properties that directly answer the needs you uncovered — not a scattershot list of everything in budget.</p>
                        <p>Just as important is how you present it. Never hand over a shortlist without justification — tie every recommendation back to what the client told you: <em>"I selected this property specifically because it solves your need for a home office while staying under your budget."</em></p>
                        <div class="crs-scenario">
                            <strong>🇵🇭 Philippine Scenario</strong>
                            <p>A qualified client needs a 2-bedroom unit near Ortigas, under ₱6M, move-in within 3 months, with space for a home office. Instead of sending 12 listings across Metro Manila, you shortlist 3 pre-selling and RFO units that meet every criterion, and explain in one line why each one made the cut.</p>
                        </div>
                        <div class="crs-takeaway-line"><strong>Core Takeaway:</strong> Present your recommendations with a clear justification tied directly to the client's stated budget, needs, and timeline — not a generic list of "available units."</div>
                    </article>

                    <div class="crs-callout">
                        <strong>📌 TAKE NOTE — Diagnose Before You Prescribe</strong>
                        <p>A doctor doesn't prescribe medicine before running a diagnosis — and neither should you. Resist the urge to start listing properties in the first five minutes of a conversation. Ask, qualify, then recommend, in that order.</p>
                    </div>

                    <div class="crs-key-takeaways">
                        <h4>Key Takeaways</h4>
                        <ul>
                            <li>Open-ended, lifestyle-focused questions uncover the real motive behind a client's request far better than close-ended checklist questions.</li>
                            <li>Use BANT — Budget, Authority, Need, Timeline — to qualify a lead before investing time in site visits.</li>
                            <li>Qualifying protects your most limited resource: time. It filters out buyers who aren't financially ready or motivated.</li>
                            <li>Build a tight shortlist of 3 to 4 properties and justify each recommendation against the client's actual needs.</li>
                        </ul>
                    </div>

                    @include('training-course-quiz', ['module' => 3, 'questions' => $quizzes[3], 'progress' => $progress[3], 'passingScore' => $passingScore])
                </div>
                @endif
            </section>

            {{-- ============================= MODULE 4 ============================= --}}
            <section class="crs-module-detail {{ $progress[4]['unlocked'] ? '' : 'is-locked' }}" id="module-04">
                <button type="button" class="crs-module-detail-head" {{ $progress[4]['unlocked'] ? '' : 'disabled' }}>
                    <div class="crs-module-detail-badge">Module 04</div>
                    <div class="crs-module-detail-titlewrap">
                        <h2>Site Visits and Property Presentation</h2>
                        <p>Execute flawless property showings that maximize the property's appeal and help the client visualize ownership.</p>
                    </div>
                    <div class="crs-module-detail-meta">
                        <span>⏱ 50 min</span>
                        <span>3 Lessons</span>
                        @if ($progress[4]['completed'])
                            <span class="crs-status crs-status-complete">✓ Completed · Best {{ $progress[4]['best_score'] }}%</span>
                        @elseif ($progress[4]['unlocked'])
                            <span class="crs-status crs-status-ready">Ready</span>
                        @else
                            <span class="crs-status crs-status-locked">Locked</span>
                        @endif
                        <svg class="crs-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </button>

                @if (!$progress[4]['unlocked'])
                    <div class="crs-locked-panel">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <p>Complete <strong>Module 03 — Client Discovery and Qualification</strong> to unlock this module.</p>
                    </div>
                @else
                <div class="crs-module-body">
                    <div class="crs-objective"><strong>Objective:</strong> Execute flawless property showings that maximize the property's appeal and help the client visualize ownership.</div>

                    <article class="crs-lesson">
                        <h3><span class="crs-lesson-num">Lesson 4.1</span> Preparing Professional Site Visits</h3>
                        <p>A great showing is won or lost before the client even arrives. Build a pre-showing routine: arrive around 30 minutes early, turn on all the lights, adjust the temperature so the unit feels comfortable, and open the blinds to let in natural light. If you're touring multiple properties in one day, map the driving route ahead of time so you're not improvising directions with a client in the car.</p>
                        <div class="crs-scenario">
                            <strong>🇵🇭 Philippine Scenario</strong>
                            <p>You have three back-to-back showings across Muntinlupa and Parañaque on a Saturday. The day before, you map the fastest route between all three addresses and confirm lockbox codes with each seller or developer. You arrive 30 minutes ahead of each visit, so by the time the client pulls up, the unit is already bright, cool, and presentable.</p>
                        </div>
                        <div class="crs-takeaway-line"><strong>Core Takeaway:</strong> Control the environment. A home shown in the dark, feeling stuffy, with an agent fumbling with a lockbox, immediately puts the buyer in a negative, defensive mindset.</div>
                    </article>

                    <article class="crs-lesson">
                        <h3><span class="crs-lesson-num">Lesson 4.2</span> Communicating Features vs. Benefits</h3>
                        <p>A <strong>feature</strong> is a spec. A <strong>benefit</strong> is what that spec actually does for the client's life. Your job on a tour is to translate one into the other, every time. "A large kitchen island" is a feature. "A place where your kids can do homework while you prepare dinner without feeling crowded" is a benefit — and it's the benefit that sells.</p>
                        <div class="crs-scenario">
                            <strong>🇵🇭 Philippine Scenario</strong>
                            <p>Instead of saying "this unit has a balcony," you say, "this is where you'll have your morning coffee before the Manila heat sets in." The buyer isn't picturing a balcony anymore — they're picturing their own morning routine, which is a much harder thing to walk away from.</p>
                        </div>
                        <div class="crs-takeaway-line"><strong>Core Takeaway:</strong> Always sell the benefit, not just the feature. Translate architectural specs into lifestyle upgrades the client can picture themselves living.</div>
                    </article>

                    <article class="crs-lesson">
                        <h3><span class="crs-lesson-num">Lesson 4.3</span> Highlighting Investment Potential</h3>
                        <p>Even buyers purchasing a primary home want reassurance they're making a sound financial decision. Weave in the investment angle during the tour: historical appreciation rates in the area, realistic rental yield if the unit were ever leased out, and opportunities to "force equity" — for example, pointing out that finishing an unfinished basement or den could instantly add resale value.</p>
                        <div class="crs-scenario">
                            <strong>🇵🇭 Philippine Scenario</strong>
                            <p>While touring a pre-selling unit near a planned MRT extension in Quezon City, you mention that similar units in already-completed phases nearby have appreciated over the past few years, and that the unit could realistically be leased out to a young professional once turned over. The buyer now sees the unit as both a home and a hedge.</p>
                        </div>
                        <div class="crs-takeaway-line"><strong>Core Takeaway:</strong> Even primary homebuyers want to know they are making a safe financial investment. Point out the elements that will make the home easy to resell in 5 to 10 years.</div>
                    </article>

                    <div class="crs-callout">
                        <strong>📌 TAKE NOTE — The "Silent" Tour</strong>
                        <p>Do not talk over the entire showing. Point out key benefits, then give the buyers physical space and silence to explore, discuss with each other, and emotionally connect with the property.</p>
                    </div>

                    <div class="crs-key-takeaways">
                        <h4>Key Takeaways</h4>
                        <ul>
                            <li>Prepare the environment before the client arrives — lights, temperature, blinds, and route planning all shape the first impression.</li>
                            <li>Always translate features into benefits the client can picture in their own life.</li>
                            <li>Bring up appreciation, rental yield, and ways to force equity — even primary homebuyers care about long-term value.</li>
                            <li>Practice the "Silent Tour": highlight the key points, then step back and let the buyer connect with the space on their own.</li>
                        </ul>
                    </div>

                    @include('training-course-quiz', ['module' => 4, 'questions' => $quizzes[4], 'progress' => $progress[4], 'passingScore' => $passingScore])
                </div>
                @endif
            </section>

            {{-- ============================= MODULE 5 ============================= --}}
            <section class="crs-module-detail {{ $progress[5]['unlocked'] ? '' : 'is-locked' }}" id="module-05">
                <button type="button" class="crs-module-detail-head" {{ $progress[5]['unlocked'] ? '' : 'disabled' }}>
                    <div class="crs-module-detail-badge">Module 05</div>
                    <div class="crs-module-detail-titlewrap">
                        <h2>Documentation and Ethical Selling</h2>
                        <p>Protect the client and the brokerage through rigorous documentation, honest communication, and strict adherence to real estate law.</p>
                    </div>
                    <div class="crs-module-detail-meta">
                        <span>⏱ 45 min</span>
                        <span>3 Lessons</span>
                        @if ($progress[5]['completed'])
                            <span class="crs-status crs-status-complete">✓ Completed · Best {{ $progress[5]['best_score'] }}%</span>
                        @elseif ($progress[5]['unlocked'])
                            <span class="crs-status crs-status-ready">Ready</span>
                        @else
                            <span class="crs-status crs-status-locked">Locked</span>
                        @endif
                        <svg class="crs-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </button>

                @if (!$progress[5]['unlocked'])
                    <div class="crs-locked-panel">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <p>Complete <strong>Module 04 — Site Visits and Property Presentation</strong> to unlock this module.</p>
                    </div>
                @else
                <div class="crs-module-body">
                    <div class="crs-objective"><strong>Objective:</strong> Protect the client and the brokerage through rigorous documentation, honest communication, and strict adherence to real estate law.</div>

                    <article class="crs-lesson">
                        <h3><span class="crs-lesson-num">Lesson 5.1</span> Responsible Documentation Practices</h3>
                        <p>Every transaction runs on paper — Purchase Agreements, Agency Disclosures, and Addendums — and every field on those documents carries weight. A single missing initial or an incorrectly typed deadline can cost a client their earnest money deposit, or kill a deal entirely.</p>
                        <p>Learn the anatomy of each document type: what a Purchase Agreement legally binds both parties to, what an Agency Disclosure clarifies about who you represent, and how an Addendum formally amends terms after the fact — then treat every field as if it will be scrutinized.</p>
                        <div class="crs-scenario">
                            <strong>🇵🇭 Philippine Scenario</strong>
                            <p>A Reservation Agreement lists the down payment deadline as "within 30 days" but the actual signed date field was left blank. Weeks later, the developer disputes when the clock started, putting the buyer's reservation fee at risk. A five-second check at signing would have prevented the entire dispute.</p>
                        </div>
                        <div class="crs-takeaway-line"><strong>Core Takeaway:</strong> A single missing initial or an incorrectly typed deadline can cost a client their earnest money deposit or kill a deal entirely. Attention to detail is non-negotiable.</div>
                    </article>

                    <article class="crs-lesson">
                        <h3><span class="crs-lesson-num">Lesson 5.2</span> Transparent Communication &amp; Disclosures</h3>
                        <p>You have a legal and ethical obligation to disclose known material facts about a property — past flood damage, roof leaks, or upcoming zoning changes nearby — even when disclosing them makes the sale harder.</p>
                        <p>Hiding a flaw to get a sale is a shortcut to losing your license. Transparently addressing flaws, on the other hand, builds massive trust and allows buyers to make informed decisions — which is exactly what keeps them coming back and referring others to you.</p>
                        <div class="crs-scenario">
                            <strong>🇵🇭 Philippine Scenario</strong>
                            <p>You know a subdivision in Laguna experienced minor street flooding two typhoon seasons ago, even though the specific unit you're selling sits on higher ground. Instead of staying silent, you disclose what you know and explain the drainage improvements made since. The buyer proceeds with full confidence instead of finding out later and losing trust in you.</p>
                        </div>
                        <div class="crs-takeaway-line"><strong>Core Takeaway:</strong> Hiding a flaw to get a sale is a shortcut to losing your license. Transparently addressing flaws builds massive trust and allows buyers to make informed decisions.</div>
                    </article>

                    <article class="crs-lesson">
                        <h3><span class="crs-lesson-num">Lesson 5.3</span> Protecting the Client (Fiduciary Duty)</h3>
                        <p>As a fiduciary, you owe your client <strong>confidentiality</strong>, <strong>loyalty</strong>, and honest <strong>accounting</strong>. In practice, this means negotiating fiercely on your client's behalf without revealing their underlying motivations or maximum budget to the other party — even when it would be easier to speed up the deal by tipping your hand.</p>
                        <div class="crs-scenario">
                            <strong>🇵🇭 Philippine Scenario</strong>
                            <p>Your buyer privately tells you they'd go as high as ₱7.5M if needed, but their opening offer is ₱6.8M. During negotiation, the seller's agent probes for "how flexible" your buyer really is. You hold the line on the ₱6.8M offer and its justification, without ever revealing the ceiling your client shared with you in confidence.</p>
                        </div>
                        <div class="crs-takeaway-line"><strong>Core Takeaway:</strong> You are a fiduciary. Your client's financial well-being must legally and ethically be placed above your desire to earn a commission.</div>
                    </article>

                    <div class="crs-callout">
                        <strong>📌 TAKE NOTE — When in Doubt, Disclose</strong>
                        <p>If you're unsure whether something counts as a "material fact," the safer move is almost always to disclose it and let the client decide. A conversation about a flaw costs you a little discomfort; hiding one can cost you your license.</p>
                    </div>

                    <div class="crs-key-takeaways">
                        <h4>Key Takeaways</h4>
                        <ul>
                            <li>Treat every field on a Purchase Agreement, Agency Disclosure, or Addendum as non-negotiable in its accuracy — small errors can cost a client their deposit.</li>
                            <li>You are legally and ethically required to disclose known material facts, even ones that make the sale harder.</li>
                            <li>Transparency about flaws builds trust; hiding them is a shortcut to losing your license.</li>
                            <li>As a fiduciary, protect your client's confidentiality and financial interests above your own desire to close quickly.</li>
                        </ul>
                    </div>

                    @include('training-course-quiz', ['module' => 5, 'questions' => $quizzes[5], 'progress' => $progress[5], 'passingScore' => $passingScore])
                </div>
                @endif
            </section>

            {{-- ============================= MODULE 6 ============================= --}}
            <section class="crs-module-detail {{ $progress[6]['unlocked'] ? '' : 'is-locked' }}" id="module-06">
                <button type="button" class="crs-module-detail-head" {{ $progress[6]['unlocked'] ? '' : 'disabled' }}>
                    <div class="crs-module-detail-badge">Module 06</div>
                    <div class="crs-module-detail-titlewrap">
                        <h2>Closing and After-Sales Service</h2>
                        <p>Master the final stages of the transaction and implement systems to generate lifelong repeat and referral business.</p>
                    </div>
                    <div class="crs-module-detail-meta">
                        <span>⏱ 45 min</span>
                        <span>3 Lessons</span>
                        @if ($progress[6]['completed'])
                            <span class="crs-status crs-status-complete">✓ Completed · Best {{ $progress[6]['best_score'] }}%</span>
                        @elseif ($progress[6]['unlocked'])
                            <span class="crs-status crs-status-ready">Ready</span>
                        @else
                            <span class="crs-status crs-status-locked">Locked</span>
                        @endif
                        <svg class="crs-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </button>

                @if (!$progress[6]['unlocked'])
                    <div class="crs-locked-panel">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <p>Complete <strong>Module 05 — Documentation and Ethical Selling</strong> to unlock this module.</p>
                    </div>
                @else
                <div class="crs-module-body">
                    <div class="crs-objective"><strong>Objective:</strong> Master the final stages of the transaction and implement systems to generate lifelong repeat and referral business.</div>

                    <article class="crs-lesson">
                        <h3><span class="crs-lesson-num">Lesson 6.1</span> Handling Objections &amp; Guiding the Decision</h3>
                        <p>Final-hour jitters are normal — the <strong>LAER method</strong> gives you a repeatable way to work through them: <strong>Listen</strong> fully without interrupting, <strong>Acknowledge</strong> the concern as valid, <strong>Explore</strong> what's really behind it, then <strong>Respond</strong> with relevant facts.</p>
                        <p>Final objections about price, inspection results, or market timing are rarely about the property itself — they're usually rooted in the fear of making a massive financial commitment. Validate that fear first, then rely on the data you gathered back in Module 3 (their stated needs, budget, and timeline) to reassure them.</p>
                        <div class="crs-scenario">
                            <strong>🇵🇭 Philippine Scenario</strong>
                            <p>A day before signing, your buyer suddenly says, "Maybe we should wait, prices might drop." Instead of arguing, you listen, acknowledge that it's a big decision, and explore what's driving the hesitation — it turns out they're anxious about affordability, not the market. You respond by walking through the amortization numbers you calculated together in Module 3, which addresses the real fear directly.</p>
                        </div>
                        <div class="crs-takeaway-line"><strong>Core Takeaway:</strong> Final objections are rarely about the property; they are usually rooted in the fear of making a massive financial commitment. Validate their fear, then rely on the data you gathered in Module 3 to reassure them.</div>
                    </article>

                    <article class="crs-lesson">
                        <h3><span class="crs-lesson-num">Lesson 6.2</span> Completing the Handoff (The Closing Process)</h3>
                        <p>The agent's job intensifies, not ends, once the contract is signed. During escrow, you coordinate with mortgage lenders, title officers, and home inspectors to make sure every contingency is cleared on time.</p>
                        <p>Think of yourself as the conductor of the deal: no single party is tracking every deadline across every other party — that's your job, and missing one can delay or even collapse the closing.</p>
                        <div class="crs-scenario">
                            <strong>🇵🇭 Philippine Scenario</strong>
                            <p>Your buyer's bank needs an updated Certificate of Employment before releasing the loan, while the developer needs the signed Contract to Sell before scheduling turnover. You proactively follow up with both sides so neither deadline slips, keeping the whole transaction on schedule instead of finding out about a missed requirement after it's already too late.</p>
                        </div>
                        <div class="crs-takeaway-line"><strong>Core Takeaway:</strong> The agent's job intensifies after the contract is signed. You are the conductor making sure all third parties hit their deadlines so the deal closes on schedule.</div>
                    </article>

                    <article class="crs-lesson">
                        <h3><span class="crs-lesson-num">Lesson 6.3</span> Maintaining Long-Term Relationships</h3>
                        <p>The best agents transition from "active agent on this deal" to "lifelong real estate advisor." Set up a post-close system for checking in at 30 days, 6 months, and 1 year, sending annual property value updates, and asking for referrals at the right moments.</p>
                        <p>80% of buyers say they would use their agent again, but only 20% actually do — almost always because the agent failed to stay in touch. Your past clients are your most profitable future lead source, but only if you keep the relationship alive.</p>
                        <div class="crs-scenario">
                            <strong>🇵🇭 Philippine Scenario</strong>
                            <p>A year after turnover, you send a past client a short message with updated resale values for similar units in their building, congratulating them on the appreciation. They reply thanking you — and two weeks later refer their officemate, who's now looking to buy in the same area.</p>
                        </div>
                        <div class="crs-takeaway-line"><strong>Core Takeaway:</strong> 80% of buyers say they would use their agent again, but only 20% actually do because the agent failed to stay in touch. Your past clients are your most profitable future lead source.</div>
                    </article>

                    <div class="crs-callout">
                        <strong>📌 TAKE NOTE — Closing Isn't the Finish Line</strong>
                        <p>Treat the handshake at turnover as the start of a relationship, not the end of a transaction. The agents who build a real book of repeat and referral business are the ones who kept showing up long after the commission was paid.</p>
                    </div>

                    <div class="crs-key-takeaways">
                        <h4>Key Takeaways</h4>
                        <ul>
                            <li>Use LAER — Listen, Acknowledge, Explore, Respond — to work through final-hour objections instead of arguing against them.</li>
                            <li>Final objections are usually about fear of commitment, not the property — reassure with the data gathered during discovery.</li>
                            <li>Act as the conductor during escrow, keeping lenders, title officers, and inspectors on schedule.</li>
                            <li>Build a systematic post-close check-in habit — most repeat business is lost to silence, not dissatisfaction.</li>
                        </ul>
                    </div>

                    @include('training-course-quiz', ['module' => 6, 'questions' => $quizzes[6], 'progress' => $progress[6], 'passingScore' => $passingScore])
                </div>
                @endif
            </section>

            <section class="training-section">
                <div class="learning-grid crs-info-grid">
                    <aside class="academy-panel">
                        <h3>How This Course Works</h3>
                        <p>A short set of rules so grading and unlocking always feel fair and predictable.</p>
                        <div class="feature-list">
                            <div class="feature-row">
                                <div class="feature-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                                <div><strong>Pass at {{ $passingScore }}%</strong><span>Score {{ $passingScore }}% or higher on a module's quiz to complete it.</span></div>
                            </div>
                            <div class="feature-row">
                                <div class="feature-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg></div>
                                <div><strong>Sequential unlocking</strong><span>Each module unlocks only after the previous one is completed.</span></div>
                            </div>
                            <div class="feature-row">
                                <div class="feature-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></div>
                                <div><strong>Progress is saved</strong><span>Your scores and completions are tied to your account, not your browser — they persist after logout and across devices.</span></div>
                            </div>
                            <div class="feature-row">
                                <div class="feature-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg></div>
                                <div><strong>Unlimited retakes</strong><span>Didn't pass? Retake the quiz right away — only your best score is kept.</span></div>
                            </div>
                        </div>
                    </aside>

                    <aside class="academy-panel">
                        <h3>Planned Course Features</h3>
                        <p>Coming as the remaining modules are finalized.</p>
                        <div class="feature-list">
                            <div class="feature-row">
                                <div class="feature-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M4 6h9a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2z"/></svg></div>
                                <div><strong>Video Lessons</strong><span>Structured modules with lesson playback.</span></div>
                            </div>
                            <div class="feature-row">
                                <div class="feature-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></div>
                                <div><strong>Completion Certificate</strong><span>Certificate generation after all 6 modules are completed.</span></div>
                            </div>
                            <div class="feature-row">
                                <div class="feature-icon"><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-5a4 4 0 11-8 0 4 4 0 018 0zm6 2a4 4 0 10-8 0"/></svg></div>
                                <div><strong>Team Leaderboard</strong><span>See how your progress compares across your sales team.</span></div>
                            </div>
                        </div>
                    </aside>
                </div>
            </section>

@endsection

@push('academy-scripts')
<style>
    .module-status.is-complete { color: #2f8f4e; }

    .crs-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    @media (max-width: 900px) { .crs-info-grid { grid-template-columns: 1fr; } }

    .crs-module-detail { scroll-margin-top: calc(var(--navbar-height) + 20px); margin-top: 22px; border: 1px solid var(--line); border-radius: 16px; background: #fff; box-shadow: 0 3px 14px rgba(20, 36, 58, .055); overflow: hidden; }
    .crs-module-detail-head { display: flex; align-items: center; gap: 18px; width: 100%; padding: 20px 24px; border: 0; background: none; cursor: pointer; text-align: left; }
    .crs-module-detail-head:disabled { cursor: not-allowed; }
    .crs-module-detail-badge { flex-shrink: 0; padding: 7px 12px; border-radius: 999px; color: #8c6512; background: #fff5d8; font-size: 11px; font-weight: 800; letter-spacing: .5px; }
    .crs-module-detail-titlewrap { flex: 1; min-width: 0; }
    .crs-module-detail-titlewrap h2 { margin: 0 0 4px; color: #14243a; font-size: 18px; }
    .crs-module-detail-titlewrap p { margin: 0; color: var(--muted); font-size: 12.5px; line-height: 1.5; }
    .crs-module-detail-meta { display: flex; align-items: center; gap: 14px; flex-shrink: 0; color: #8c99a9; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .3px; }
    .crs-status { padding: 5px 10px; border-radius: 999px; font-size: 10px; }
    .crs-status-ready { color: #946d16; background: #fff8e6; }
    .crs-status-complete { color: #2f8f4e; background: #eafaf0; }
    .crs-status-locked { color: #9aa4b1; background: #f3f5f8; }
    .crs-chevron { width: 18px; height: 18px; color: #9aa4b1; transition: transform .2s ease; }
    .crs-module-detail.is-open .crs-chevron { transform: rotate(180deg); }
    .crs-module-detail:not(.is-locked) .crs-module-detail-head:hover { background: #fafbfd; }

    .crs-locked-panel { display: flex; align-items: center; gap: 12px; margin: 0 24px 24px; padding: 16px 18px; border: 1px dashed #d8dee7; border-radius: 12px; color: #778599; background: #f8fafc; font-size: 12.5px; }
    .crs-locked-panel svg { width: 22px; height: 22px; flex-shrink: 0; color: #9aa4b1; }
    .crs-locked-panel p { margin: 0; }

    .crs-module-body { display: none; padding: 0 24px 28px; border-top: 1px solid #edf0f4; }
    .crs-module-detail.is-open .crs-module-body { display: block; }
    .crs-objective { margin: 20px 0 22px; padding: 12px 16px; border-left: 3px solid var(--gold); border-radius: 0 8px 8px 0; color: #536278; background: #fbf8f0; font-size: 12.5px; line-height: 1.6; }

    .crs-lesson { margin-bottom: 26px; padding-bottom: 26px; border-bottom: 1px solid #f0f2f5; }
    .crs-lesson:last-of-type { border-bottom: none; }
    .crs-lesson h3 { display: flex; align-items: center; gap: 10px; margin: 0 0 12px; color: #14243a; font-size: 15.5px; }
    .crs-lesson-num { flex-shrink: 0; padding: 4px 9px; border-radius: 6px; color: var(--blue-700); background: #edf3fa; font-size: 10px; font-weight: 800; letter-spacing: .4px; text-transform: uppercase; }
    .crs-lesson p { margin: 0 0 12px; color: #46536a; font-size: 13px; line-height: 1.75; }
    .crs-list { margin: 0 0 12px; padding-left: 20px; color: #46536a; font-size: 13px; line-height: 1.8; }
    .crs-list li { margin-bottom: 4px; }

    .crs-cycle { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin: 14px 0 18px; }
    @media (max-width: 900px) { .crs-cycle { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 560px) { .crs-cycle { grid-template-columns: 1fr; } }
    .crs-cycle-step { padding: 12px; border-radius: 10px; background: #f8fafc; }
    .crs-cycle-step span { display: inline-grid; place-items: center; width: 20px; height: 20px; margin-bottom: 6px; border-radius: 50%; color: #fff; background: var(--blue-700); font-size: 10px; font-weight: 800; }
    .crs-cycle-step strong { display: block; margin-bottom: 3px; color: #14243a; font-size: 12px; }
    .crs-cycle-step small { display: block; color: #778599; font-size: 10.5px; line-height: 1.5; }

    .crs-scenario { margin: 0 0 14px; padding: 14px 16px; border: 1px solid #dfeadf; border-radius: 10px; background: #f3faf4; }
    .crs-scenario strong { display: block; margin-bottom: 6px; color: #276b3a; font-size: 11.5px; letter-spacing: .3px; text-transform: uppercase; }
    .crs-scenario p { margin: 0; color: #385c40; font-size: 12.5px; line-height: 1.65; }

    .crs-takeaway-line { padding: 12px 16px; border-radius: 10px; color: #1f3350; background: #edf3fa; font-size: 12.5px; line-height: 1.6; }

    .crs-callout { margin: 4px 0 24px; padding: 16px 18px; border: 1px solid #eedb9f; border-radius: 12px; background: #fff8e6; }
    .crs-callout strong { display: block; margin-bottom: 6px; color: #8c6512; font-size: 12.5px; }
    .crs-callout p { margin: 0; color: #6b5417; font-size: 12.5px; line-height: 1.65; }

    .crs-key-takeaways { margin-bottom: 26px; padding: 18px 20px; border-radius: 12px; background: #14243a; }
    .crs-key-takeaways h4 { margin: 0 0 10px; color: #f4d98a; font-size: 13px; letter-spacing: .3px; text-transform: uppercase; }
    .crs-key-takeaways ul { margin: 0; padding-left: 18px; color: #dce4ef; font-size: 12.5px; line-height: 1.8; }

    .crs-quiz { padding-top: 6px; border-top: 2px dashed #e3e8ef; }
    .crs-quiz-head { display: flex; align-items: baseline; justify-content: space-between; gap: 10px; margin: 20px 0 4px; flex-wrap: wrap; }
    .crs-quiz h4 { margin: 0; color: #14243a; font-size: 15px; }
    .crs-quiz-sub { margin: 0 0 16px; color: var(--muted); font-size: 12px; }
    .crs-already-passed { display: inline-flex; align-items: center; gap: 6px; margin-bottom: 16px; padding: 8px 12px; border-radius: 999px; color: #2f8f4e; background: #eafaf0; font-size: 11.5px; font-weight: 700; }

    .crs-quiz-question { margin-bottom: 18px; padding: 14px 16px; border: 1px solid var(--line); border-radius: 12px; transition: border-color .15s ease; }
    .crs-quiz-question.crs-q-missing { border-color: #e0776f; background: #fff6f5; }
    .crs-quiz-q-text { margin: 0 0 12px; color: #26384f; font-size: 13px; font-weight: 600; line-height: 1.5; }
    .crs-quiz-options { display: grid; gap: 8px; }
    .crs-quiz-option { display: flex; align-items: flex-start; gap: 10px; padding: 10px 12px; border: 1px solid #e3e8ef; border-radius: 9px; cursor: pointer; font-size: 12.5px; color: #46536a; line-height: 1.5; transition: .15s ease; }
    .crs-quiz-option:hover { border-color: #cdd6e2; background: #fafbfd; }
    .crs-quiz-option input { margin-top: 2px; flex-shrink: 0; }
    .crs-quiz-option.is-correct { border-color: #7fd68a; background: #eafaf0; color: #1f5c31; }
    .crs-quiz-option.is-wrong { border-color: #e0776f; background: #fff1ef; color: #8c2f26; }

    .crs-quiz-error { margin-bottom: 14px; padding: 10px 14px; border-radius: 9px; color: #8c2f26; background: #fff1ef; font-size: 12px; }
    .crs-quiz-actions { display: flex; align-items: center; gap: 12px; }
    .crs-quiz-submit { padding: 11px 22px; border: 0; border-radius: 10px; color: #14243a; background: linear-gradient(120deg, var(--gold), var(--gold-light)); font-size: 12.5px; font-weight: 800; cursor: pointer; }
    .crs-quiz-submit:disabled { opacity: .6; cursor: default; }
    .crs-quiz-retake { padding: 10px 18px; border: 1px solid var(--line); border-radius: 10px; color: #26384f; background: #fff; font-size: 12px; font-weight: 700; cursor: pointer; }

    .crs-quiz-result { margin-top: 16px; padding: 16px 18px; border-radius: 12px; font-size: 13px; line-height: 1.6; }
    .crs-quiz-result.is-pass { color: #1f5c31; background: #eafaf0; border: 1px solid #b9e8c4; }
    .crs-quiz-result.is-fail { color: #8c2f26; background: #fff1ef; border: 1px solid #f3c3bd; }
    .crs-quiz-result strong { font-size: 18px; }
    .crs-unlock-note { margin-top: 8px; font-size: 11.5px; opacity: .8; }

    @media (max-width: 700px) {
        .crs-module-detail-head { flex-wrap: wrap; }
        .crs-module-detail-meta { width: 100%; justify-content: space-between; }
    }
</style>

<script>
(function () {
    var csrfToken = document.querySelector('meta[name=csrf-token]').content;

    // ---- Accordion open/close ----
    var details = document.querySelectorAll('.crs-module-detail:not(.is-locked)');
    var firstOpened = false;
    details.forEach(function (detail) {
        var head = detail.querySelector('.crs-module-detail-head');
        head.addEventListener('click', function () {
            detail.classList.toggle('is-open');
        });
        // Auto-open the first unlocked, not-yet-completed module.
        if (!firstOpened && !detail.querySelector('.crs-status-complete')) {
            detail.classList.add('is-open');
            firstOpened = true;
        }
    });

    // ---- "Continue / Start Course" button ----
    var continueBtn = document.getElementById('crsContinueBtn');
    if (continueBtn) {
        continueBtn.addEventListener('click', function () {
            var targetId = continueBtn.getAttribute('data-target');
            var target = document.getElementById(targetId);
            if (target) {
                target.classList.add('is-open');
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    }

    // ---- Quiz submission ----
    document.querySelectorAll('.crs-quiz-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var quizEl = form.closest('.crs-quiz');
            var moduleNum = quizEl.getAttribute('data-module');
            var questionEls = form.querySelectorAll('.crs-quiz-question');
            var answers = [];
            var missing = false;

            questionEls.forEach(function (qEl) {
                var checked = qEl.querySelector('input[type=radio]:checked');
                if (!checked) {
                    missing = true;
                    qEl.classList.add('crs-q-missing');
                } else {
                    qEl.classList.remove('crs-q-missing');
                    answers.push(parseInt(checked.value, 10));
                }
            });

            var errorBox = quizEl.querySelector('.crs-quiz-error');

            if (missing) {
                errorBox.textContent = 'Please answer every question before submitting.';
                errorBox.hidden = false;
                return;
            }
            errorBox.hidden = true;

            var submitBtn = form.querySelector('.crs-quiz-submit');
            submitBtn.disabled = true;
            var originalLabel = submitBtn.textContent;
            submitBtn.textContent = 'Grading…';

            fetch('/agent-training/module/' + moduleNum + '/quiz', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ answers: answers })
            })
            .then(function (res) {
                return res.json().then(function (data) { return { ok: res.ok, data: data }; });
            })
            .then(function (r) {
                submitBtn.disabled = false;
                submitBtn.textContent = originalLabel;

                if (!r.ok) {
                    errorBox.textContent = r.data.message || 'Something went wrong. Please try again.';
                    errorBox.hidden = false;
                    return;
                }

                renderQuizResult(quizEl, questionEls, r.data);
            })
            .catch(function () {
                submitBtn.disabled = false;
                submitBtn.textContent = originalLabel;
                errorBox.textContent = 'Network error — please check your connection and try again.';
                errorBox.hidden = false;
            });
        });
    });

    function renderQuizResult(quizEl, questionEls, data) {
        data.results.forEach(function (r, i) {
            var qEl = questionEls[i];
            var optionLabels = qEl.querySelectorAll('.crs-quiz-option');
            optionLabels.forEach(function (label, oi) {
                label.classList.remove('is-correct', 'is-wrong');
                var input = label.querySelector('input');
                input.disabled = true;
                if (oi === r.correct) {
                    label.classList.add('is-correct');
                } else if (oi === r.selected && !r.is_correct) {
                    label.classList.add('is-wrong');
                }
            });
        });

        var resultEl = quizEl.querySelector('.crs-quiz-result');
        resultEl.hidden = false;
        resultEl.className = 'crs-quiz-result ' + (data.passed ? 'is-pass' : 'is-fail');

        var html = '<strong>' + data.score + '%</strong> — ' + data.correct + '/' + data.total + ' correct.<br>';
        html += data.passed
            ? 'Passed! This module is now marked complete.'
            : 'Not quite — you need ' + data.passing_score + '% to pass. Review the highlighted answers and try again.';
        resultEl.innerHTML = html;

        if (data.passed) {
            var note = document.createElement('div');
            note.className = 'crs-unlock-note';
            note.textContent = data.next_unlocked ? 'Reloading to unlock the next module…' : 'Reloading…';
            resultEl.appendChild(note);
            setTimeout(function () { window.location.reload(); }, 1600);
        } else {
            var retakeBtn = document.createElement('button');
            retakeBtn.type = 'button';
            retakeBtn.className = 'crs-quiz-retake';
            retakeBtn.style.marginTop = '12px';
            retakeBtn.textContent = 'Retake Quiz';
            retakeBtn.addEventListener('click', function () { resetQuiz(quizEl, questionEls); });
            resultEl.appendChild(retakeBtn);
        }
    }

    function resetQuiz(quizEl, questionEls) {
        questionEls.forEach(function (qEl) {
            qEl.classList.remove('crs-q-missing');
            qEl.querySelectorAll('.crs-quiz-option').forEach(function (label) {
                label.classList.remove('is-correct', 'is-wrong');
                var input = label.querySelector('input');
                input.disabled = false;
                input.checked = false;
            });
        });
        var resultEl = quizEl.querySelector('.crs-quiz-result');
        resultEl.hidden = true;
        resultEl.innerHTML = '';
    }
})();
</script>
@endpush