<?php

namespace App\Services;

use App\Models\TrainingModuleProgress;
use App\Models\User;

/**
 * Central source of truth for the "Real Estate Agent Training" course
 * (resources/views/training-course.blade.php).
 *
 * Holds the module list, the quiz question bank (with server-side answer
 * keys — never sent to the browser), grading logic, and the sequential
 * unlock rules. Used by both the TrainingCourseController (the training
 * page + quiz submission) and the AppServiceProvider view composer (which
 * feeds the always-visible sidebar on the academy layout).
 */
class AgentTrainingCourseService
{
    /** A module is marked "completed" the first time its quiz score reaches this percentage. */
    public const PASSING_SCORE = 70;

    public const TOTAL_MODULES = 6;

    /**
     * Course outline for the 6-module Real Estate Agent Training curriculum.
     * Each key N here must describe resources/views/training-modules/module-0N.blade.php.
     */
    public static function modules(): array
    {
        return [
            1 => [
                'title'       => 'Real Estate Sales Fundamentals',
                'summary'     => "Understand the agent's role, the full sales cycle, buyer expectations, and professional conduct.",
                'minutes'     => 35,
                'lessons'     => 3,
                'implemented' => true,
            ],
            2 => [
                'title'       => 'Property and Market Knowledge',
                'summary'     => 'Present developments clearly, explain value drivers, and match properties to client goals.',
                'minutes'     => 45,
                'lessons'     => 3,
                'implemented' => true,
            ],
            3 => [
                'title'       => 'Client Discovery and Qualification',
                'summary'     => 'Ask better questions, identify priorities, qualify leads, and prepare relevant recommendations.',
                'minutes'     => 40,
                'lessons'     => 3,
                'implemented' => true,
            ],
            4 => [
                'title'       => 'Site Visits and Property Presentation',
                'summary'     => 'Prepare professional site visits and communicate features, benefits, and investment potential.',
                'minutes'     => 50,
                'lessons'     => 3,
                'implemented' => true,
            ],
            5 => [
                'title'       => 'Documentation and Ethical Selling',
                'summary'     => 'Follow responsible documentation practices and protect the client through transparent communication.',
                'minutes'     => 45,
                'lessons'     => 3,
                'implemented' => true,
            ],
            6 => [
                'title'       => 'Closing and After-Sales Service',
                'summary'     => 'Handle final-hour objections with the LAER method, coordinate the closing process across lenders and title officers, and build a long-term client relationship system.',
                'minutes'     => 45,
                'lessons'     => 3,
                'implemented' => true,
            ],
        ];
    }

    /**
     * Quiz question bank. `correct` is a zero-based index into `options`
     * and is stripped out before anything is sent to the view/JSON so the
     * client never receives the answer key.
     */
    /**
     * Quiz question bank. `correct` is a zero-based index into `options`
     * and is stripped out before anything is sent to the view/JSON so the
     * client never receives the answer key.
     */
    public static function quizBank(): array
    {
        return [
            1 => [
                [
                    'question' => 'What percentage of a real estate transaction is described as the "visible" part of the job (showings) versus the work that happens off-camera?',
                    'options' => [
                        '50% visible, 50% invisible',
                        '10% visible, 90% invisible',
                        '90% visible, 10% invisible',
                        '25% visible, 75% invisible',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'Which of the "Three Roles You Play" involves protecting the client\'s interests at the table on price, payment terms, and timelines?',
                    'options' => [
                        'The Consultant',
                        'The Project Manager',
                        'The Negotiator',
                        'The Closer',
                    ],
                    'correct' => 2,
                ],
                [
                    'question' => 'Which stage of the Complete Sales Cycle involves understanding the buyer\'s budget, must-haves, financing plan, and timeline?',
                    'options' => [
                        'Prospecting',
                        'Discovery',
                        'Showing',
                        'Negotiating',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'In the Philippine Scenario for Lesson 1.1, what does the agent do instead of just sending the price list to a Facebook inquiry?',
                    'options' => [
                        'Asks about financing plan, move-in date, and must-haves to build a targeted shortlist',
                        'Sends every unit in the current inventory',
                        'Ignores the inquiry until the buyer follows up',
                        'Asks for a reservation payment before discussing details',
                    ],
                    'correct' => 0,
                ],
                [
                    'question' => 'According to Lesson 1.2, what has changed about buyer behavior over the past decade?',
                    'options' => [
                        'Buyers now rely entirely on agents for listings and pricing',
                        'Buyers already research listings, photos, and pricing online before contacting an agent',
                        'Buyers no longer use online amortization calculators',
                        'Buyers prefer to skip site visits entirely',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'Now that the "information gap" has closed, what does Lesson 1.2 say buyers actually need from an agent?',
                    'options' => [
                        'More raw listing data',
                        'Judgment, honesty, and speed',
                        'A lower commission rate',
                        'A printed brochure',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'In the Philippine Scenario for Lesson 1.2, how does the agent handle a client\'s question about flood risk in a Cavite subdivision?',
                    'options' => [
                        'Avoids the topic to protect the sale',
                        'Shares known drainage history and elevation, and offers to confirm specifics with the developer',
                        'Tells the client to research it themselves',
                        'Claims the property has zero risk without checking',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'Which of the following is one of the "Three Daily Habits" described in Lesson 1.3?',
                    'options' => [
                        'Time Blocking',
                        'Cold Calling Quotas',
                        'Paid Social Media Advertising',
                        'Weekly Team Meetings',
                    ],
                    'correct' => 0,
                ],
                [
                    'question' => 'What is the purpose of "CRM Discipline" as described in Lesson 1.3?',
                    'options' => [
                        'To automate all client replies without agent involvement',
                        'To log every lead, conversation, and follow-up date so nobody falls through the cracks',
                        'To reduce the number of active clients an agent handles',
                        'To replace the need for time blocking',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'What core mindset does the Module 1 "Take Note" callout encourage agents to adopt?',
                    'options' => [
                        'Memorize scripts word-for-word from the brochure',
                        'Act as a trusted advisor who protects the client\'s financial interests',
                        'Focus only on closing as quickly as possible',
                        'Avoid daily habits until an active deal exists',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'A client asks the agent to explain, in plain language, why a nearby lot has been appreciating in value — rather than just reading them the listing sheet. Which of the "Three Roles" is the agent performing here?',
                    'options' => [
                        'The Project Manager',
                        'The Consultant',
                        'The Negotiator',
                        'The Closer',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'An agent notices a buyer\'s loan approval has stalled and proactively follows up with both the lender and the title officer without being asked, so the client never even realizes there was a delay. Which role is this?',
                    'options' => [
                        'The Consultant',
                        'The Negotiator',
                        'The Project Manager',
                        'The Closer',
                    ],
                    'correct' => 2,
                ],
                [
                    'question' => 'A newly licensed agent tells a colleague, "Once the client and seller agree on a price, my job is basically done." Based on the Complete Sales Cycle in Lesson 1.1, why is this a mistake?',
                    'options' => [
                        'Negotiating is only the midpoint — Escrow/Closing and Post-Sale still remain',
                        'Negotiating is actually the very first stage of the cycle',
                        'Price agreements are not part of the six-stage cycle at all',
                        'The cycle only contains four stages in total',
                    ],
                    'correct' => 0,
                ],
                [
                    'question' => 'A buyer has already compared photos across listing sites and even estimated their own monthly amortization before ever messaging an agent. When they finally reach out, what should the agent avoid doing, per Lesson 1.2?',
                    'options' => [
                        'Repeating information the buyer has clearly already gathered on their own',
                        'Offering their own judgment on which unit actually fits the buyer\'s situation',
                        'Replying within a few hours instead of days',
                        'Being transparent about the property\'s flaws',
                    ],
                    'correct' => 0,
                ],
                [
                    'question' => 'An agent highlights only a unit\'s best features to a buyer and stays quiet about a recent increase in association dues. The buyer discovers the increase after moving in and feels misled. Which "Old Way" mistake from Lesson 1.2 does this illustrate?',
                    'options' => [
                        'Replying within a few hours instead of days',
                        'Staying quiet about unflattering details instead of volunteering them upfront',
                        'Asking about budget and must-haves before shortlisting',
                        'Giving clear next steps at the end of the conversation',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'A client ends a call with an agent unsure of what happens next or when. According to Lesson 1.2, what simple habit would have prevented this?',
                    'options' => [
                        'Sending the full price list immediately',
                        'Ending every conversation with a clear "here\'s what happens next, and roughly when"',
                        'Waiting for the client to ask what comes next',
                        'Scheduling a same-day site visit regardless of readiness',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'A buyer using a listing app asks an agent whether a planned road widening nearby will actually affect their commute. What does Lesson 1.2 say the agent can offer here that the app cannot?',
                    'options' => [
                        'A lower asking price',
                        'Local insight — traffic patterns, nearby developments, and infrastructure trends',
                        'A faster loan approval',
                        'A longer property viewing window',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'An agent constantly drops whatever they\'re doing to answer every incoming message the instant it arrives, even during scheduled site visits with other clients. Which of the "Three Daily Habits" from Lesson 1.3 is this agent neglecting?',
                    'options' => [
                        'CRM Discipline',
                        'Standardized Communication',
                        'Time Blocking',
                        'Professional Conduct',
                    ],
                    'correct' => 2,
                ],
                [
                    'question' => 'Two clients who inquire about similar units on the same day receive noticeably different quality showing-confirmation messages — one polished, one rushed and inconsistent — depending on how busy the agent was that day. Which habit from Lesson 1.3 addresses this?',
                    'options' => [
                        'Time Blocking',
                        'Standardized Communication',
                        'CRM Discipline',
                        'Prospecting',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'According to Lesson 1.3, what is the combined effect of Time Blocking, CRM Discipline, and Standardized Communication on how a client perceives an agent?',
                    'options' => [
                        'They make the agent appear less accessible to clients',
                        'They make an agent\'s professionalism visible before the client ever verifies it themselves',
                        'They are only useful once an active deal already exists',
                        'They primarily reduce the agent\'s workload, with no client-facing benefit',
                    ],
                    'correct' => 1,
                ],
            ],
            2 => [
                [
                    'question' => 'According to Lesson 2.1, what should be presented to a buyer before the specific unit details?',
                    'options' => [
                        'The exact payment terms',
                        'The community and lifestyle',
                        'The developer\'s company history',
                        'The unit\'s exact square footage',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'Why does the "lifestyle story" matter especially in the Philippine market, according to Lesson 2.1?',
                    'options' => [
                        'Philippine law requires lifestyle marketing',
                        'A large share of demand comes from OFWs and young professionals buying sight-unseen or on a single trip home',
                        'Most Philippine buyers pay entirely in cash',
                        'Condo living is mandatory in most cities',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'Which of the following is one of the "Three Things You Must Know Cold" about a development in Lesson 2.1?',
                    'options' => [
                        'The developer\'s stock price',
                        'Turnover status per phase',
                        'The buyer\'s exact salary',
                        'The architect\'s personal portfolio',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'Why is knowing "Turnover Status per Phase" important, according to Lesson 2.1?',
                    'options' => [
                        'It affects the agent\'s commission percentage',
                        'Mixing up which phases are lived-in vs. still under construction damages credibility',
                        'It determines the buyer\'s loan interest rate',
                        'It is only relevant for house-and-lot developments',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'According to Lesson 2.2, what primarily drives property value appreciation in the Philippines?',
                    'options' => [
                        'The house\'s architectural style alone',
                        'What\'s happening around the property — infrastructure, commercial growth, institutions',
                        'The age of the building',
                        'The number of previous owners',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'Which of these is listed in Lesson 2.2 as a "value driver" signal worth watching?',
                    'options' => [
                        'A new expressway exit or MRT/LRT extension',
                        'The building\'s exterior paint color',
                        'The developer\'s social media follower count',
                        'The unit\'s floor number',
                    ],
                    'correct' => 0,
                ],
                [
                    'question' => 'In the Philippine Scenario for Lesson 2.2, what helps a client see the investment angle of a property that looks "in the middle of nowhere"?',
                    'options' => [
                        'A discount on the reservation fee',
                        'Explaining a planned expressway interchange\'s completion date and its commute impact',
                        'Offering free furniture',
                        'Comparing it to condos in Metro Manila',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'What usually matters most to "The Growing Family" buyer profile in Lesson 2.3?',
                    'options' => [
                        'Lock-and-leave lifestyle and short commute',
                        'Space, nearby schools, and a subdivision with a playground',
                        'Proximity to nightlife',
                        'A small, low-maintenance unit',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'What usually matters most to "The Young Professional" buyer profile in Lesson 2.3?',
                    'options' => [
                        'A large backyard for children',
                        'Commute time, security, and a lock-and-leave lifestyle',
                        'Multiple guest bedrooms',
                        'Proximity to a provincial hometown',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'What is the Core Takeaway of Lesson 2.3 regarding shortlists?',
                    'options' => [
                        'Showing more properties always increases the chance of a sale',
                        'Showing fewer, highly targeted properties beats showing 15 random homes',
                        'Buyer profiles should never be blended',
                        'Agents should let clients build their own shortlist',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'A client asks an agent "which block faces the main road?" and "how far is my unit from the entrance?" and the agent has to fumble for a map to answer. Which of the "Three Things You Must Know Cold" does this expose a gap in?',
                    'options' => [
                        'Amenity Locations',
                        'Turnover Status per Phase',
                        'Site Development Plan',
                        'The developer\'s marketing budget',
                    ],
                    'correct' => 2,
                ],
                [
                    'question' => 'During a tour, a buyer becomes visibly more interested after the agent points out the resort-style pool, the co-working lounge, and the retail row — even before discussing the unit itself. Which of the "Three Things You Must Know Cold" is the agent leveraging?',
                    'options' => [
                        'Turnover Status per Phase',
                        'Amenity Locations',
                        'Site Development Plan',
                        'The unit\'s exact floor plan',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'An OFW client is purchasing a unit sight-unseen during a short trip home. The agent opens the presentation with the unit\'s exact floor plan and square meterage. What does Lesson 2.1 suggest the agent should have led with instead?',
                    'options' => [
                        'The developer\'s company history and financial standing',
                        'The community lifestyle — amenities, security, and proximity to key destinations',
                        'A comparison against every other unit currently listed',
                        'The exact turnover date of every phase in the development',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'A well-regarded private school announces plans to open in a previously overlooked area. According to Lesson 2.2, what long-term effect does the module say this kind of institution tends to have?',
                    'options' => [
                        'It has little to no lasting effect on buyer interest',
                        'It pulls families and long-term buyers toward the area',
                        'It only affects rental prices, not resale value',
                        'It primarily affects commercial, not residential, demand',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'A property currently has little visible appeal, but a new business district is under construction a few kilometers away. According to Lesson 2.2, what should the agent explain to help the client understand the area\'s potential?',
                    'options' => [
                        'That the commercial growth will likely bring jobs, foot traffic, and housing demand',
                        'That the unit\'s floor plan will change once construction finishes',
                        'That the developer will lower the price once the district opens',
                        'That property values never move due to nearby construction',
                    ],
                    'correct' => 0,
                ],
                [
                    'question' => 'A client asks an agent, "Why is this specific area worth investing in?" and the agent replies only with, "It\'s a good investment, trust me." What is missing from this response, according to Lesson 2.2?',
                    'options' => [
                        'A specific answer tied to real infrastructure, zoning, or growth signals',
                        'A mention of the unit\'s interior finishes',
                        'A discount on the reservation fee',
                        'Nothing — this response is sufficient per the lesson',
                    ],
                    'correct' => 0,
                ],
                [
                    'question' => 'Before their next site visit, an agent wants to be ready to answer "why will this area be worth more in five years?" According to the Module 2 "Take Note" callout, what should they do to prepare?',
                    'options' => [
                        'Memorize the developer\'s sales script',
                        'Spend time with the municipality\'s zoning maps and infrastructure roadmap',
                        'Wait until the client asks before researching anything',
                        'Rely only on what the developer\'s brochure says',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'A buyer\'s needs blend traits of both "The Growing Family" and "The Young Professional" profiles — they want space for children but also a short commute. According to Lesson 2.3, how should the agent treat this?',
                    'options' => [
                        'Force the buyer into whichever single profile seems closest',
                        'Notice which needs are actually driving the decision, rather than fitting the buyer into one category',
                        'Ignore the commute concern since family needs take priority',
                        'Tell the client to choose one profile before continuing',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'A young professional client says their main concern is commute time to their office in Ortigas, but the agent\'s shortlist includes several house-and-lot options requiring an hour-long drive. What does this mismatch represent, per Lesson 2.3?',
                    'options' => [
                        'A well-matched shortlist based on strong value drivers',
                        'A shortlist built from available inventory rather than the client\'s actual stated priority',
                        'An appropriate shortlist for "The Growing Family" profile',
                        'Exactly what Lesson 2.3 recommends for young professionals',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'A newer agent shows a client every single listing available under their budget instead of narrowing the options based on stated priorities. What does Lesson 2.3 say about this approach?',
                    'options' => [
                        'It is the most effective way to guarantee a sale',
                        'Quality of matching beats quantity of showings',
                        'It saves the client time compared to a shorter list',
                        'It should only be avoided for investor clients',
                    ],
                    'correct' => 1,
                ],
            ],
            3 => [
                [
                    'question' => 'What is the main problem with a "checklist question" like "Do you want 3 bedrooms?" according to Lesson 3.1?',
                    'options' => [
                        'It takes too long to ask',
                        'It only confirms a box on a spec sheet but reveals nothing about why it matters',
                        'It is considered rude in the Philippine market',
                        'It cannot be asked over text message',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'What makes a question a true "Discovery Question" in Lesson 3.1?',
                    'options' => [
                        'It is open-ended and lifestyle-focused, like asking how they use their current space',
                        'It only requires a yes/no answer',
                        'It focuses only on the client\'s budget',
                        'It is asked only at the very end of the conversation',
                    ],
                    'correct' => 0,
                ],
                [
                    'question' => 'What do deep discovery questions actually uncover, according to Lesson 3.1?',
                    'options' => [
                        'The client\'s exact monthly income',
                        'Emotional drivers — security, status, or convenience — behind the purchase',
                        'The client\'s preferred bank',
                        'The developer\'s construction schedule',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'What does the "B" in the BANT framework stand for, according to Lesson 3.2?',
                    'options' => [
                        'Budget',
                        'Belief',
                        'Broker',
                        'Building',
                    ],
                    'correct' => 0,
                ],
                [
                    'question' => 'What does the "A" in BANT help an agent confirm, according to Lesson 3.2?',
                    'options' => [
                        'Whether all decision-makers are present in the conversation',
                        'The buyer\'s architectural preferences',
                        'The agent\'s commission split',
                        'The property\'s appraised value',
                    ],
                    'correct' => 0,
                ],
                [
                    'question' => 'In the Philippine Scenario for Lesson 3.2, what BANT flag does the prospect show by repeatedly changing the subject about financing and mentioning needing to "talk to my parents"?',
                    'options' => [
                        'Need and Timeline',
                        'Budget and Authority',
                        'Timeline only',
                        'No flag — the client is fully qualified',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'According to Lesson 3.2, why does qualifying leads with BANT matter?',
                    'options' => [
                        'It guarantees a faster closing',
                        'It protects the agent\'s most valuable asset — time — from unmotivated or unable buyers',
                        'It is a legal requirement before any showing',
                        'It replaces the need for site visits',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'How many properties does Lesson 3.3 recommend including in a shortlist?',
                    'options' => [
                        '1 to 2',
                        '3 to 4',
                        '8 to 10',
                        'As many as fit the client\'s budget',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'What should accompany every property recommendation in a shortlist, according to Lesson 3.3?',
                    'options' => [
                        'A discount offer',
                        'A justification tying it back to the client\'s stated needs',
                        'A testimonial from the developer',
                        'A comparison to the agent\'s own home',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'What is the overall order of operations emphasized in the Module 3 "Take Note" callout?',
                    'options' => [
                        'Recommend, then ask, then qualify',
                        'Ask, qualify, then recommend',
                        'Qualify, recommend, then ask',
                        'There is no set order',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'A client mentions wanting "a house with a garden" but doesn\'t explain why. Per Lesson 3.1, what should the agent do before assuming garden size alone is the deciding factor?',
                    'options' => [
                        'Immediately shortlist the largest gardens available in budget',
                        'Ask an open-ended question about how they\'d actually use the space, to uncover the real motivation',
                        'Tell the client gardens aren\'t a common feature in the local market',
                        'Skip the topic since it wasn\'t asked as a direct question',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'An agent asks a client, "Do you want a balcony?" and receives only a "yes" with no further explanation. What does Lesson 3.1 call this type of question?',
                    'options' => [
                        'A discovery question',
                        'A checklist question',
                        'A BANT question',
                        'A closing question',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'A client says they want "a bigger condo" without further detail. Per Lesson 3.1, what should the agent ask to uncover what\'s actually driving this request?',
                    'options' => [
                        '"What\'s your maximum budget?"',
                        'A question about how they use their current space day-to-day',
                        '"How many square meters do you want exactly?"',
                        'Nothing further — the request is already clear enough to shortlist',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'A prospect has pre-approved financing and clearly needs to move due to a growing family, but every time move-in timing comes up, they say "let\'s revisit this next year." Which BANT element does this reveal a gap in?',
                    'options' => [
                        'Budget',
                        'Authority',
                        'Timeline',
                        'Need',
                    ],
                    'correct' => 2,
                ],
                [
                    'question' => 'A lead is clearly motivated (strong Need) and the spouse is present in every conversation (strong Authority), but the lead keeps avoiding any discussion of pre-approval or proof of funds. Which BANT element needs a more direct conversation before investing further time?',
                    'options' => [
                        'Timeline',
                        'Need',
                        'Authority',
                        'Budget',
                    ],
                    'correct' => 3,
                ],
                [
                    'question' => 'A buyer verbally agrees to move forward on a unit, but weeks later their spouse — who was never part of any prior conversation — objects and the deal falls apart. Which BANT element was missed early on?',
                    'options' => [
                        'Need',
                        'Authority',
                        'Budget',
                        'Timeline',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'An agent spends three weekends driving an enthusiastic lead to site visits before ever discussing financing — only to later learn the lead can\'t actually qualify for a loan. What does Lesson 3.2 say this illustrates?',
                    'options' => [
                        'That BANT is unnecessary once a lead seems enthusiastic',
                        'The risk of losing significant time by not qualifying a lead early',
                        'That site visits should always come before any other step',
                        'That financing should never be discussed with a new lead',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'An agent hands a client a shortlist of four properties with only the sentence, "Here are four options within your budget." What is missing from this presentation, per Lesson 3.3?',
                    'options' => [
                        'A larger number of options to choose from',
                        'A justification tying each property back to the client\'s stated needs',
                        'A discount on at least one of the properties',
                        'Nothing — budget fit alone is sufficient justification',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'An investor client needs a unit under a set budget with strong rental yield potential in Makati, move-in ready within a month. The agent sends a shortlist of ten units spanning several unrelated cities regardless of move-in readiness. What does this violate, per Lesson 3.3?',
                    'options' => [
                        'The recommendation to build a tight 3–4 property shortlist that directly matches identified needs',
                        'The BANT framework from Lesson 3.2',
                        'The recommendation to only show pre-selling units',
                        'Nothing — more options always serve investor clients better',
                    ],
                    'correct' => 0,
                ],
                [
                    'question' => 'A new agent, excited about a promising lead, begins describing three specific units in the first five minutes of their very first conversation, before asking any questions. What does the Module 3 "Take Note" callout say about this approach?',
                    'options' => [
                        'It reflects the correct order: recommend first, then qualify',
                        'Ask, qualify, then recommend — in that order',
                        'Recommending early builds urgency and should be encouraged',
                        'The order of these steps doesn\'t matter as long as a sale eventually closes',
                    ],
                    'correct' => 1,
                ],
            ],
            4 => [
                [
                    'question' => 'A great property showing, according to Lesson 4.1, is won or lost when?',
                    'options' => [
                        'Before the client even steps through the door',
                        'Only after the client sees the price list',
                        'During the final walkthrough before closing',
                        'The moment the buyer signs the reservation',
                    ],
                    'correct' => 0,
                ],
                [
                    'question' => 'How far in advance should an agent arrive to prepare a unit for a showing, according to Lesson 4.1?',
                    'options' => [
                        '5 minutes',
                        'About 30 minutes',
                        '2 hours',
                        'The night before',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'Which of the following is part of the pre-showing routine described in Lesson 4.1?',
                    'options' => [
                        'Route planning for back-to-back showings',
                        'Negotiating the final sale price',
                        'Drafting the Purchase Agreement',
                        'Running a full BANT qualification',
                    ],
                    'correct' => 0,
                ],
                [
                    'question' => 'What does "Confirm Access" help an agent avoid, according to Lesson 4.1?',
                    'options' => [
                        'Overpricing the unit',
                        'Fumbling with lockbox codes, keys, or guard clearance in front of the client',
                        'Missing a financing deadline',
                        'Losing the listing to another agent',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'What is the key difference between a "feature" and a "benefit" in Lesson 4.2?',
                    'options' => [
                        'A feature is a spec; a benefit is what that spec actually does for the client\'s life',
                        'A feature is expensive; a benefit is free',
                        'A feature is emotional; a benefit is technical',
                        'There is no meaningful difference',
                    ],
                    'correct' => 0,
                ],
                [
                    'question' => 'In the Philippine Scenario for Lesson 4.2, what does the agent say instead of "this unit has a balcony"?',
                    'options' => [
                        '"This balcony adds resale value"',
                        '"This is where you\'ll have your morning coffee before the Manila heat sets in"',
                        '"This balcony meets the minimum size requirement"',
                        '"The balcony faces north"',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'What habit helps an agent consistently translate features into benefits, according to Lesson 4.2?',
                    'options' => [
                        'Silently asking "so what?" after describing a feature and answering it for the client',
                        'Memorizing a fixed script for every unit',
                        'Avoiding mentioning specs altogether',
                        'Asking the client to guess the benefit themselves',
                    ],
                    'correct' => 0,
                ],
                [
                    'question' => 'Which of the following is one of the "Three Angles" for highlighting investment potential in Lesson 4.3?',
                    'options' => [
                        'Rental yield',
                        'Homeowners\' association fees',
                        'Property tax rate',
                        'Title insurance cost',
                    ],
                    'correct' => 0,
                ],
                [
                    'question' => 'Why might even a primary homebuyer care about investment potential, according to Lesson 4.3?',
                    'options' => [
                        'They are legally required to review appreciation data',
                        'They want reassurance they are making a sound financial decision, not just a comfortable one',
                        'They plan to resell the unit within a month',
                        'The bank requires proof of appreciation before releasing funds',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'What does the "Silent Tour" concept in the Module 4 callout recommend?',
                    'options' => [
                        'Never speaking at all during the showing',
                        'Highlighting key benefits, then giving buyers space and silence to explore and connect with the property',
                        'Communicating only through text messages during the tour',
                        'Letting the buyer lead the entire tour with no agent input at all',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'A buyer walks into a unit that\'s dim, warm, and stuffy right as the agent arrives at the same time as the client — with no time to prepare beforehand. According to Lesson 4.1, what does this likely do to the buyer\'s mindset?',
                    'options' => [
                        'It has no real effect on the buyer\'s impression',
                        'It puts the buyer in a negative, defensive mindset before the agent has said a word',
                        'It makes the buyer more curious about the unit',
                        'It only matters if the buyer already disliked the property beforehand',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'An agent has three back-to-back showings across two cities on a Saturday and ends up improvising directions with the client sitting in the car, causing delays between visits. Which part of the pre-showing routine did they skip, per Lesson 4.1?',
                    'options' => [
                        'Confirm Access',
                        'Light, Temperature & Blinds',
                        'Route Planning',
                        'Feature-to-benefit translation',
                    ],
                    'correct' => 2,
                ],
                [
                    'question' => 'A buyer walks into a unit that feels stuffy, with the blinds closed and lights off, even though the agent arrived exactly on time rather than early. What did the agent most likely fail to do, per Lesson 4.1?',
                    'options' => [
                        'Confirm the lockbox code with the developer',
                        'Arrive around 30 minutes early to turn on lights, adjust temperature, and open blinds',
                        'Map the driving route to the property',
                        'Translate the unit\'s features into benefits',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'While touring a unit, an agent says, "This unit has floor-to-ceiling windows," and moves on without further comment. Per the "so what?" habit in Lesson 4.2, what should the agent add next?',
                    'options' => [
                        'The exact square footage of the windows',
                        'A translation of that feature into what it actually means for the client\'s daily life',
                        'A comparison to a competitor\'s units',
                        'Nothing further is needed once a feature is mentioned',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'A buyer nods politely while an agent lists off a unit\'s specifications but doesn\'t seem emotionally engaged with the property. According to Lesson 4.2, what is most likely missing from the agent\'s presentation?',
                    'options' => [
                        'More technical specifications',
                        'Benefits — specs translated into feelings the client can picture living',
                        'A lower asking price',
                        'A faster pace through the tour',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'An agent tells a client, "This unit has a two-car garage." Which of the following best reflects the kind of benefit translation Lesson 4.2 recommends adding to that statement?',
                    'options' => [
                        '"The garage meets the minimum required parking size."',
                        '"That\'s room for both your vehicles, plus space to store things without ever feeling cramped."',
                        '"The garage was built in the same year as the rest of the unit."',
                        'No translation is needed — the feature already speaks for itself.',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'While touring a pre-selling unit, an agent mentions that similar units in nearby completed phases have appreciated in value over the past few years. Which of the "Three Angles" for highlighting investment potential is this?',
                    'options' => [
                        'Rental Yield',
                        'Ways to Force Equity',
                        'Historical Appreciation',
                        'Agency Disclosure',
                    ],
                    'correct' => 2,
                ],
                [
                    'question' => 'An agent points out to a buyer that finishing an unfinished den in the unit could add resale value down the line. Which investment angle from Lesson 4.3 is this?',
                    'options' => [
                        'Historical Appreciation',
                        'Ways to Force Equity',
                        'Rental Yield',
                        'Fiduciary Duty',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'A buyer insists they will never rent out their unit, but still seems reassured after learning it could realistically be leased for a certain monthly amount if needed. Which investment angle from Lesson 4.3 addresses this?',
                    'options' => [
                        'Historical Appreciation',
                        'Ways to Force Equity',
                        'Rental Yield',
                        'Agency Disclosure',
                    ],
                    'correct' => 2,
                ],
                [
                    'question' => 'After highlighting a unit\'s key benefits, an agent notices the buyers have started quietly discussing the space between themselves. What does the Module 4 "Take Note" callout recommend the agent do?',
                    'options' => [
                        'Keep talking to fill the silence',
                        'Interrupt to ask if they have questions',
                        'Give them physical space and silence to connect with the property',
                        'Move immediately to the next room',
                    ],
                    'correct' => 2,
                ],
            ],
            5 => [
                [
                    'question' => 'What can a single missing initial or an incorrectly typed deadline cost a client, according to Lesson 5.1?',
                    'options' => [
                        'A minor delay only',
                        'Their earnest money deposit, or it could kill the deal entirely',
                        'Nothing significant',
                        'Only a small administrative fee',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'What is the primary purpose of a Purchase Agreement, according to Lesson 5.1?',
                    'options' => [
                        'To clarify who the agent represents',
                        'To legally bind both parties to the sale — price, terms, and deadlines',
                        'To amend terms after signing',
                        'To disclose material facts about the property',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'What does an Agency Disclosure clarify, according to Lesson 5.1?',
                    'options' => [
                        'The property\'s material defects',
                        'Who the agent represents in the transaction',
                        'The final negotiated price',
                        'The commission split between agents',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'According to Lesson 5.1, do verbal agreements to change contract terms count as binding?',
                    'options' => [
                        'Yes, always',
                        'No — if it isn\'t in a signed addendum, it isn\'t binding',
                        'Only if a witness is present',
                        'Only for reservation agreements',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'According to Lesson 5.2, what are agents legally and ethically obligated to disclose?',
                    'options' => [
                        'Only facts the buyer directly asks about',
                        'Known material facts about a property, even ones that make the sale harder',
                        'Only facts that make the sale easier',
                        'Facts already listed in the marketing brochure',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'In the Philippine Scenario for Lesson 5.2, what does the agent do about the past minor flooding near the property?',
                    'options' => [
                        'Stays silent since the unit itself sits on higher ground',
                        'Discloses what they know and explains the drainage improvements made since',
                        'Denies any knowledge of it',
                        'Refers the buyer to the developer instead of answering',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'According to the Module 5 "Take Note" callout, what should an agent do if unsure whether something counts as a "material fact"?',
                    'options' => [
                        'Ignore it since it is unconfirmed',
                        'Disclose it and let the client decide',
                        'Ask the seller to decide for them',
                        'Wait until after closing to mention it',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'Which of the following is one of the three fiduciary duties described in Lesson 5.3?',
                    'options' => [
                        'Confidentiality',
                        'Rapid response time',
                        'Aggressive marketing',
                        'Discount negotiation',
                    ],
                    'correct' => 0,
                ],
                [
                    'question' => 'In the Philippine Scenario for Lesson 5.3, what does the agent do when the seller\'s agent probes for how flexible the buyer really is?',
                    'options' => [
                        'Reveals the buyer\'s maximum budget to speed up negotiation',
                        'Holds the line on the offer without revealing the confidential ceiling the client shared',
                        'Ends the negotiation immediately',
                        'Asks the buyer to raise the offer on the spot',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'What does "Honest Accounting" require of an agent as a fiduciary, according to Lesson 5.3?',
                    'options' => [
                        'Tracking and disclosing every peso that passes through them accurately',
                        'Rounding commission figures for simplicity',
                        'Only accounting for the final commission check',
                        'Keeping financial records private from the brokerage',
                    ],
                    'correct' => 0,
                ],
                [
                    'question' => 'A signed Contract to Sell needs its move-in date changed, and the agent updates it through a text message thread and a verbal confirmation with the developer instead of a signed document. What did the agent fail to use, according to Lesson 5.1?',
                    'options' => [
                        'A Purchase Agreement',
                        'An Agency Disclosure',
                        'An Addendum',
                        'A Reservation Agreement',
                    ],
                    'correct' => 2,
                ],
                [
                    'question' => 'Before sending a Reservation Agreement out for signing, an agent reviews every single field one at a time, reading it out loud as if their own deposit were on the line. What habit from Lesson 5.1 is this?',
                    'options' => [
                        'Honest Accounting',
                        'The "read every field once" discipline that catches costly mistakes before signing',
                        'Confirming access with the developer',
                        'The LAER method',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'An agent is helping both a buyer and has an informal relationship with a seller-side contact on the same deal, but no separate document was ever signed clarifying exactly who the agent formally represents. What should have been used, per Lesson 5.1?',
                    'options' => [
                        'An Addendum',
                        'A Purchase Agreement',
                        'An Agency Disclosure',
                        'A Certificate of Employment',
                    ],
                    'correct' => 2,
                ],
                [
                    'question' => 'An agent learns of a roof leak that was repaired a year ago in a house they\'re selling, decides it looks fine now, and doesn\'t mention it to speed up the sale. According to Lesson 5.2, what risk does this create?',
                    'options' => [
                        'None, since the repair was already completed',
                        'A shortcut to losing the buyer\'s trust, the agent\'s reputation, and potentially their license',
                        'A minor delay in the closing timeline only',
                        'A risk only if the buyer specifically asks about roof condition',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'A buyer discovers after moving in that association dues increased shortly before closing — something the agent knew but didn\'t mention, even though the sale closed successfully. What does Lesson 5.2 say is the likely long-term cost of this omission?',
                    'options' => [
                        'No lasting cost, since the sale already closed',
                        'Loss of the buyer\'s trust and future referrals, even though the deal closed',
                        'A legal requirement to refund the buyer\'s dues',
                        'An automatic decrease in the agent\'s commission',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'Instead of staying silent, an agent proactively tells a buyer about a past pest issue in a unit and explains the professional treatment that resolved it. What approach does Lesson 5.2 recommend this reflects?',
                    'options' => [
                        'Disclosing what\'s known and explaining remediation, letting the buyer decide with full information',
                        'Avoiding the topic unless directly asked',
                        'Downplaying the issue to keep the deal moving',
                        'Referring the buyer to the developer instead of answering directly',
                    ],
                    'correct' => 0,
                ],
                [
                    'question' => 'During negotiation, the seller\'s agent proposes splitting the difference to close quickly — which would be personally easier for the agent — but the agent keeps pushing for the client\'s original terms. Which fiduciary duty from Lesson 5.3 is being demonstrated?',
                    'options' => [
                        'Confidentiality',
                        'Honest Accounting',
                        'Loyalty',
                        'Standardized Communication',
                    ],
                    'correct' => 2,
                ],
                [
                    'question' => 'An agent receives a reservation payment and a partial commission advance, and carefully logs and discloses every peso accurately in the client\'s file, even though it\'s tedious. Which fiduciary duty from Lesson 5.3 does this reflect?',
                    'options' => [
                        'Loyalty',
                        'Confidentiality',
                        'Honest Accounting',
                        'Time Blocking',
                    ],
                    'correct' => 2,
                ],
                [
                    'question' => 'A friend outside the transaction casually asks an agent what their client\'s real maximum budget is. Even in this low-stakes, non-negotiation context, the agent declines to share. Which fiduciary duty does this reflect, per Lesson 5.3?',
                    'options' => [
                        'Confidentiality',
                        'Loyalty',
                        'Honest Accounting',
                        'Professional Conduct',
                    ],
                    'correct' => 0,
                ],
                [
                    'question' => 'An agent is tempted to steer a client toward a slightly higher price than the client actually wants, since it would increase the agent\'s own commission. According to Lesson 5.3, what should take priority in this moment?',
                    'options' => [
                        'The agent\'s commission',
                        'The client\'s financial well-being',
                        'The seller\'s preferred price',
                        'Closing the deal as quickly as possible, regardless of price',
                    ],
                    'correct' => 1,
                ],
            ],
            6 => [
                [
                    'question' => 'According to Lesson 6.1, what are final-hour objections about price or timing usually rooted in?',
                    'options' => [
                        'A flaw in the property itself',
                        'The fear of making a massive financial commitment',
                        'Dissatisfaction with the agent',
                        'The developer\'s pricing policy changing',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'What does the "L" in the LAER method stand for, according to Lesson 6.1?',
                    'options' => [
                        'Lead',
                        'Listen',
                        'Leverage',
                        'Log',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'In LAER, why does "Respond" come last rather than first, according to Lesson 6.1?',
                    'options' => [
                        'Because responding immediately is illegal under real estate law',
                        'Because Listen, Acknowledge, and Explore surface the real concern before you address it',
                        'Because the client should respond first',
                        'Because LAER only applies after the contract is signed',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'In the Philippine Scenario for Lesson 6.1, what data does the agent use to respond to the buyer\'s affordability anxiety?',
                    'options' => [
                        'A brand-new listing in a cheaper area',
                        'The amortization numbers calculated together back in Module 3',
                        'A discount from the developer',
                        'A testimonial from a past client',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'According to Lesson 6.2, what happens to an agent\'s workload once the contract is signed?',
                    'options' => [
                        'It ends almost entirely',
                        'It intensifies, as the agent coordinates lenders, title officers, and inspectors',
                        'It shifts entirely to the developer',
                        'It is handed off to a paralegal',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'Which of the following is NOT one of the parties an agent coordinates with during escrow in Lesson 6.2?',
                    'options' => [
                        'Mortgage lenders',
                        'Title officers',
                        'Home inspectors',
                        'The buyer\'s employer HR department',
                    ],
                    'correct' => 3,
                ],
                [
                    'question' => 'What simple tool does Lesson 6.2 recommend for staying ahead of closing deadlines?',
                    'options' => [
                        'A tracking sheet with one row per open item and a deadline per party',
                        'A weekly conference call with all parties',
                        'Relying on the developer to track everything',
                        'A shared spreadsheet only the buyer can edit',
                    ],
                    'correct' => 0,
                ],
                [
                    'question' => 'According to Lesson 6.3, what percentage of buyers say they would use their agent again, versus the percentage who actually do?',
                    'options' => [
                        '80% would; 20% actually do',
                        '50% would; 50% actually do',
                        '20% would; 80% actually do',
                        '90% would; 90% actually do',
                    ],
                    'correct' => 0,
                ],
                [
                    'question' => 'In the post-close check-in schedule from Lesson 6.3, what should an agent check at the 30-day mark?',
                    'options' => [
                        'Whether the client wants to sell again',
                        'That turnover went smoothly with no lingering warranty or move-in concerns',
                        'The client\'s updated annual income',
                        'Whether the client wants a referral bonus',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'What does Lesson 6.3 say usually causes agents to lose repeat business from past clients?',
                    'options' => [
                        'Client dissatisfaction with the property',
                        'The agent failing to stay in touch',
                        'Rising interest rates',
                        'Clients moving to a different city',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'A buyer objects the day before signing, saying prices might drop. The agent immediately begins explaining why prices won\'t drop, without letting the buyer finish speaking. Which LAER step did the agent skip?',
                    'options' => [
                        'Acknowledge',
                        'Explore',
                        'Listen',
                        'Respond',
                    ],
                    'correct' => 2,
                ],
                [
                    'question' => 'In response to an objection, an agent says, "That\'s a completely fair thing to be thinking about," before addressing anything else. Which LAER step is this?',
                    'options' => [
                        'Listen',
                        'Acknowledge',
                        'Explore',
                        'Respond',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'Instead of assuming an objection is purely about the market, an agent asks a follow-up question and discovers the buyer is actually worried about job security, not property prices. Which LAER step uncovered this?',
                    'options' => [
                        'Listen',
                        'Acknowledge',
                        'Explore',
                        'Respond',
                    ],
                    'correct' => 2,
                ],
                [
                    'question' => 'The moment an objection is raised, an agent immediately quotes specific amortization numbers without acknowledging or exploring first. What mistake does Lesson 6.1 say this represents?',
                    'options' => [
                        'Skipping straight to Respond, which turns objections into arguments instead of resolutions',
                        'Correctly following LAER in the proper order',
                        'An acceptable shortcut when the agent is confident in the numbers',
                        'A mistake only if the numbers turn out to be inaccurate',
                    ],
                    'correct' => 0,
                ],
                [
                    'question' => 'A title officer\'s paperwork stalls for two weeks, and neither the lender nor the developer notices, because no single party was tracking every deadline across all three. What role does Lesson 6.2 say the agent should play here?',
                    'options' => [
                        'A passive participant who waits for other parties to report issues',
                        'The "conductor" who proactively tracks every party\'s deadlines',
                        'A role limited only to buyer-side communication',
                        'A role that ends once the contract is signed',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'An agent keeps a simple sheet with one row per open item and a deadline column for each party involved, catching a missing document days before it becomes urgent. What does this exemplify, per Lesson 6.2?',
                    'options' => [
                        'The LAER method',
                        'Staying ahead of closing deadlines using a simple tracking tool',
                        'The BANT framework',
                        'The Silent Tour',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'A home inspection contingency is still open two days before the scheduled closing date, and the agent has been assuming it was already cleared without confirming. What should have happened, per Lesson 6.2?',
                    'options' => [
                        'The agent should have proactively confirmed the inspection contingency was cleared as part of coordinating third parties',
                        'The buyer should have been responsible for confirming this themselves',
                        'This is solely the inspector\'s responsibility, with no agent involvement expected',
                        'Nothing — assumptions are acceptable as long as the closing date hasn\'t passed yet',
                    ],
                    'correct' => 0,
                ],
                [
                    'question' => 'Seven months after turnover, an agent sends a light, no-agenda message simply asking how the neighborhood is settling in, without asking for anything in return. Which check-in milestone from Lesson 6.3 does this match?',
                    'options' => [
                        '30 Days',
                        '6 Months',
                        '1 Year',
                        'This doesn\'t match any recommended milestone',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'Exactly one year after turnover, an agent sends a client an updated property value estimate for their unit, and the client responds enthusiastically before referring a friend two weeks later. What does Lesson 6.3 say this moment often represents?',
                    'options' => [
                        'A rare coincidence unrelated to the check-in schedule',
                        'The moment a referral often happens naturally, since the estimate reminds the client their purchase was a good decision',
                        'A milestone the module recommends skipping in most cases',
                        'Something that should have been sent at the 30-day mark instead',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'An agent assumes that once commission is paid, following up with past clients isn\'t a priority, and rarely does so — then wonders why so few clients return. What does Lesson 6.3 say is the actual explanation for this pattern?',
                    'options' => [
                        'Most clients are simply dissatisfied with their purchase',
                        'Most repeat business is lost to silence, not dissatisfaction',
                        'Referrals are mostly a matter of luck and can\'t be systematized',
                        'Clients who don\'t return were never satisfied to begin with',
                    ],
                    'correct' => 1,
                ],
            ],
        ];
    }

    public static function quizForView(int $module): array
    {
        $questions = self::quizBank()[$module] ?? [];

        return array_map(function ($q) {
            return [
                'question' => $q['question'],
                'options'  => $q['options'],
            ];
        }, $questions);
    }

    /**
     * Grades submitted answers against the server-side answer key.
     * $answers is a zero-based array of selected option indices, one per question, in order.
     */
    public static function grade(int $module, array $answers): array
    {
        $questions = self::quizBank()[$module] ?? [];
        $total = count($questions);
        $correctCount = 0;
        $results = [];

        foreach ($questions as $i => $q) {
            $selected = array_key_exists($i, $answers) ? (int) $answers[$i] : null;
            $isCorrect = $selected !== null && $selected === $q['correct'];
            if ($isCorrect) {
                $correctCount++;
            }
            $results[] = [
                'question' => $q['question'],
                'options'  => $q['options'],
                'selected' => $selected,
                'correct'  => $q['correct'],
                'is_correct' => $isCorrect,
            ];
        }

        $score = $total > 0 ? (int) round(($correctCount / $total) * 100) : 0;

        return [
            'score'   => $score,
            'correct' => $correctCount,
            'total'   => $total,
            'passed'  => $score >= self::PASSING_SCORE,
            'results' => $results,
        ];
    }

    /**
     * Builds the full per-module state for a user: locked/unlocked/completed
     * status, best score, attempts, etc. Used to render both the training
     * page and the always-visible academy sidebar.
     *
     * @return array<int, array>
     */
    public static function progressFor(User $user): array
    {
        $rows = TrainingModuleProgress::where('user_id', $user->id)
            ->get()
            ->keyBy('module_number');

        $modules = self::modules();
        $state = [];
        $previousPassed = true; // Module 1 is always unlocked.

        foreach ($modules as $number => $meta) {
            $row = $rows->get($number);
            $passed = (bool) ($row->passed ?? false);

            $state[$number] = [
                'number'      => $number,
                'title'       => $meta['title'],
                'summary'     => $meta['summary'],
                'minutes'     => $meta['minutes'],
                'lessons'     => $meta['lessons'],
                'implemented' => $meta['implemented'],
                'unlocked'    => $meta['implemented'] && $previousPassed,
                'completed'   => $passed,
                'attempts'    => $row->attempts ?? 0,
                'best_score'  => $row->best_score ?? null,
                'last_score'  => $row->last_score ?? null,
            ];

            // The next module only unlocks once this one is implemented AND passed.
            $previousPassed = $meta['implemented'] && $passed;
        }

        return $state;
    }

    public static function completedCount(array $progressState): int
    {
        return count(array_filter($progressState, fn ($m) => $m['completed']));
    }

    public static function overallPercent(array $progressState): int
    {
        $count = self::completedCount($progressState);
        return (int) round(($count / self::TOTAL_MODULES) * 100);
    }
}