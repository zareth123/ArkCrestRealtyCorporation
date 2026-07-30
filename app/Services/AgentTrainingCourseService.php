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
     * Course outline. Module 1 (Sales & Lead-Generation Toolkit) is a
     * standalone digital-sales primer; Modules 2-7 are the core Real Estate
     * Agent Training curriculum.
     */
    public static function modules(): array
    {
        return [
            1 => [
                'title'       => 'Sales & Lead-Generation Toolkit',
                'summary'     => 'Read a lot plan, compute payment terms, run a professional Facebook page, and turn inquiries into booked site visits.',
                'minutes'     => 50,
                'lessons'     => 4,
                'implemented' => true,
            ],
            2 => [
                'title'       => 'Real Estate Sales Fundamentals',
                'summary'     => "Understand the agent's role, the full sales cycle, buyer expectations, and professional conduct.",
                'minutes'     => 35,
                'lessons'     => 3,
                'implemented' => true,
            ],
            3 => [
                'title'       => 'Property and Market Knowledge',
                'summary'     => 'Present developments clearly, explain value drivers, and match properties to client goals.',
                'minutes'     => 45,
                'lessons'     => 3,
                'implemented' => true,
            ],
            4 => [
                'title'       => 'Client Discovery and Qualification',
                'summary'     => 'Ask better questions, identify priorities, qualify leads, and prepare relevant recommendations.',
                'minutes'     => 40,
                'lessons'     => 3,
                'implemented' => true,
            ],
            5 => [
                'title'       => 'Site Visits and Property Presentation',
                'summary'     => 'Prepare professional site visits and communicate features, benefits, and investment potential.',
                'minutes'     => 50,
                'lessons'     => 3,
                'implemented' => true,
            ],
            6 => [
                'title'       => 'Documentation and Ethical Selling',
                'summary'     => 'Follow responsible documentation practices and protect the client through transparent communication.',
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
    public static function quizBank(): array
    {
        return [
            1 => [
                [
                    'question' => "In a lot plan legend, what does a yellow/gold marker typically indicate?",
                    'options' => [
                        'Sold',
                        'Reserved',
                        'Not for sale',
                        'Regular corner lot',
                    ],
                    'correct' => 2,
                ],
                [
                    'question' => "What is the correct formula for computing a lot's Total Contract Price (TCP)?",
                    'options' => [
                        'Price per sqm + lot area',
                        'Price per sqm x lot area',
                        'Reservation fee x lot area',
                        'Down payment ÷ lot area',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'When posting a property on the company Facebook page, which of these is a recommended practice?',
                    'options' => [
                        'Post the exact price per sqm publicly',
                        'Use the monthly payment instead of the full Total Contract Price to make the property more approachable',
                        'Post the Lot Plan publicly',
                        'Share the exact address publicly',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'According to the "Golden Rule," what happens to a lead if you reply after 2 minutes?',
                    'options' => [
                        "Nothing changes, response time doesn't matter",
                        'It is considered a cold inquiry with a lower chance of the client responding',
                        'The client is automatically disqualified',
                        'The lead moves to a different agent',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'What is the recommended order of a proper inquiry conversation flow?',
                    'options' => [
                        'Computation, then intro, then details',
                        'Intro, then details, then computation — each with room for Q&A',
                        'Details only, skipping intro and computation',
                        'Computation immediately, followed by intro',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'Which of these are considered "must-have" materials to send an inquiring client?',
                    'options' => [
                        'Property details, lot plan, vicinity map, and computation',
                        'Only the price per sqm',
                        'Only a verbal description of the property',
                        "The developer's internal financial reports",
                    ],
                    'correct' => 0,
                ],
                [
                    'question' => 'When a client says "Kausapin ko muna asawa ko" (I\'ll ask my spouse first), what is a recommended response?',
                    'options' => [
                        'End the conversation and wait for them to come back',
                        'Offer to create a group chat or online meeting so both spouses can see the details together',
                        'Immediately send a lower price to close faster',
                        'Stop replying to avoid pressuring them',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => "When following up with an OFW client who says they'll wait until they're back in the country, what should you avoid doing?",
                    'options' => [
                        'Simply accepting "no problem" and waiting passively with no further engagement',
                        'Offering a video call or asking if a trusted relative can view the property on their behalf',
                        'Giving an honest update about availability or upcoming price changes',
                        "Politely explaining that availability isn't guaranteed",
                    ],
                    'correct' => 0,
                ],
                [
                    'question' => 'What distinguishes a "strong" follow-up message from a "weak" one?',
                    'options' => [
                        'A strong follow-up is engaging and shows a real sense of urgency or a new option; a weak one is a generic "Interested pa po kayo?"',
                        'A strong follow-up should always be shorter than a weak one',
                        'A strong follow-up avoids mentioning promos or updates',
                        "There's no real difference; both are equally effective",
                    ],
                    'correct' => 0,
                ],
                [
                    'question' => 'When boosting a post on Facebook, what should be monitored closely to keep the ad cost-effective?',
                    'options' => [
                        'The number of comments only',
                        "CPM (Cost Per 1,000 Impressions) — if it keeps rising, it's time to refresh the ad",
                        'The time zone of the page admin',
                        'The font used in the ad caption',
                    ],
                    'correct' => 1,
                ],
            ],
            2 => [
                [
                    'question' => 'What is the primary value a modern real estate agent provides, beyond being a "tour guide"?',
                    'options' => [
                        'Interpreting market data and managing the transaction from start to finish',
                        'Simply unlocking units and showing whichever ones are available',
                        'Setting the final price of the property',
                        'Handling paperwork only after the sale is done',
                    ],
                    'correct' => 0,
                ],
                [
                    'question' => 'In the sales cycle taught in this module, what step comes immediately after "Discovery"?',
                    'options' => [
                        'Closing',
                        'Showing',
                        'Prospecting',
                        'Negotiating',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => "Buyers today usually research listings, photos, and price ranges online before meeting an agent. Because of this, what do they now expect from you?",
                    'options' => [
                        'Nothing — they no longer need an agent at all',
                        'A lower commission rate to make up for their own research',
                        'Interpretation of the data, transparency, and local market expertise',
                        'Confirmation of only what they already found online',
                    ],
                    'correct' => 2,
                ],
                [
                    'question' => 'Which daily habit is described as separating top-producing agents from amateurs?',
                    'options' => [
                        'Only replying to clients who message first',
                        'Time blocking, maintaining a CRM, and standardized communication protocols',
                        'Avoiding site visits until the buyer is fully decided',
                        'Focusing exclusively on paid social media ads',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'What does adopting the "Consultant Mindset" mean for a real estate agent?',
                    'options' => [
                        'Reading the developer\'s brochure to the client word for word',
                        'Letting the client make every decision with zero guidance',
                        'Focusing only on how quickly the deal can close',
                        "Acting as a trusted advisor who protects the client's financial interests",
                    ],
                    'correct' => 3,
                ],
            ],
            3 => [
                [
                    'question' => 'When presenting a master-planned development, condo, or subdivision, what should an agent sell first?',
                    'options' => [
                        'The exact tile brand used in the unit',
                        'The macro — community lifestyle and amenities — before the micro unit details',
                        'The developer\'s corporate history',
                        'The available parking slot numbers',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'Which of the following is a major driver of property value appreciation, according to this module?',
                    'options' => [
                        'The exterior paint color of the building',
                        'Proximity to infrastructure projects, commercial zoning, and good schools',
                        'The number of floors in the building',
                        "The listing agent's number of years in the industry",
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => "To explain value drivers well, what should an agent stay informed about?",
                    'options' => [
                        'Nothing — property value never changes once listed',
                        "The local municipality's future infrastructure and development plans",
                        'Only the current year\'s zonal tax rate',
                        "The developer's internal marketing budget",
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'What is the recommended strategy when matching properties to a client\'s goals?',
                    'options' => [
                        'Show as many listings as possible so the client has full options',
                        'Show only the most expensive units available',
                        'Show fewer, highly targeted properties that fit the buyer persona',
                        'Send a list of properties and let the client decide without a showing',
                    ],
                    'correct' => 2,
                ],
                [
                    'question' => 'Based on this module\'s buyer-persona examples, which property type best fits a young professional working in a business district?',
                    'options' => [
                        'A large suburban family home far from the city',
                        'A high-density, transit-oriented condo',
                        'A rural farm lot',
                        'An industrial warehouse space',
                    ],
                    'correct' => 1,
                ],
            ],
            4 => [
                [
                    'question' => 'Which type of question best exemplifies the "Discovery Phase" approach taught in this module?',
                    'options' => [
                        '"Do you want 3 bedrooms?"',
                        '"Walk me through how you use your current space on a weekend."',
                        '"Is your budget above 5 million pesos?"',
                        '"Do you prefer a condo or a house?"',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => "Why does deep discovery matter beyond the client's initial request?",
                    'options' => [
                        'It lets you close the sale faster without qualifying',
                        'It uncovers the emotional drivers — like security, status, or convenience — behind the purchase',
                        'It replaces the need for a shortlist',
                        "It focuses only on the client's stated budget",
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'In the BANT framework, what does the "A" stand for?',
                    'options' => [
                        'Availability',
                        'Authority',
                        'Appraisal',
                        'Agreement',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'A couple is interested in a unit, but only the husband is present and says "my wife needs to see it too." Which BANT component does this raise?',
                    'options' => [
                        'Budget',
                        'Need',
                        'Authority',
                        'Timeline',
                    ],
                    'correct' => 2,
                ],
                [
                    'question' => 'Why is qualifying leads with BANT described as protecting your most valuable asset?',
                    'options' => [
                        'It guarantees a commission',
                        "It prevents you from spending weeks on buyers who can't or won't purchase",
                        'It replaces the need for site visits',
                        "It automatically improves the property's price",
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'What does the "Timeline" component of BANT help you determine?',
                    'options' => [
                        'Whether the client is pre-approved for financing',
                        'Whether all decision-makers are present',
                        'Whether the client needs to move in 30 days or 6 months',
                        'What pain point is driving their purchase',
                    ],
                    'correct' => 2,
                ],
                [
                    'question' => 'When building a "Shortlist" in Lesson 3.3, how many properties should you typically present?',
                    'options' => [
                        'As many as possible, to show every option',
                        'Only 1, to avoid confusing the client',
                        '3 to 4 properties that directly match the qualification findings',
                        "Every unit currently in the developer's inventory",
                    ],
                    'correct' => 2,
                ],
                [
                    'question' => 'Which statement best reflects the recommended way to present a shortlisted property?',
                    'options' => [
                        '"Here\'s a list of units, let me know if you like any."',
                        '"I selected this property specifically because it solves your need for a home office while staying under your budget."',
                        '"This is the cheapest unit we have available."',
                        '"I\'m showing you this because it\'s the newest listing."',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'A client says they "just want a bigger place." According to Lesson 3.1, what should you do next?',
                    'options' => [
                        'Immediately send listings of larger units',
                        'Assume it is about status and move straight to negotiation',
                        'Ask deeper, lifestyle-focused questions to uncover the real motive behind the request',
                        'Tell them their current unit is fine',
                    ],
                    'correct' => 2,
                ],
                [
                    'question' => 'What is the core risk of skipping the qualification step before scheduling site visits?',
                    'options' => [
                        'The client may feel too informed',
                        'You risk investing significant time with buyers who are not financially ready or motivated to move forward',
                        "The developer's inventory list becomes outdated faster",
                        'The BANT framework becomes unnecessary',
                    ],
                    'correct' => 1,
                ],
            ],
            5 => [
                [
                    'question' => 'According to Lesson 4.1, roughly how early should an agent arrive before a scheduled site visit?',
                    'options' => [
                        'Right on time',
                        '5 minutes early',
                        '30 minutes early',
                        'The day before',
                    ],
                    'correct' => 2,
                ],
                [
                    'question' => 'Which of the following is part of the recommended pre-showing routine?',
                    'options' => [
                        'Leaving the blinds closed for privacy',
                        'Turning on all the lights and adjusting the temperature',
                        'Letting the buyer find the unit on their own',
                        'Skipping the driving-route planning between properties',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'Why does "controlling the environment" matter so much during a site visit?',
                    'options' => [
                        "It has no real impact on the buyer's decision",
                        'A dark, stuffy home with an agent fumbling with the lockbox puts the buyer in a negative, defensive mindset',
                        "It is only about the agent's convenience",
                        'Buyers never notice lighting or temperature',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'Which statement is an example of communicating a benefit rather than just a feature?',
                    'options' => [
                        '"This kitchen has a large island."',
                        '"The unit is 65 square meters."',
                        '"A place where your kids can do homework while you cook, without feeling crowded."',
                        '"The building has 20 floors."',
                    ],
                    'correct' => 2,
                ],
                [
                    'question' => 'What is the key difference between a feature and a benefit, as taught in Lesson 4.2?',
                    'options' => [
                        'Features are lifestyle upgrades; benefits are technical specs',
                        'Features are architectural specs; benefits translate those specs into a lifestyle upgrade for the client',
                        'There is no real difference',
                        'Benefits only apply to investment buyers',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'When discussing investment potential during a tour, which topics does Lesson 4.3 recommend?',
                    'options' => [
                        'Historical appreciation rates, potential rental yields, and ways to force equity',
                        "The agent's personal investment portfolio",
                        "Only the developer's marketing budget",
                        "The buyer's other properties",
                    ],
                    'correct' => 0,
                ],
                [
                    'question' => 'What is meant by "forcing equity," based on the example given in this module?',
                    'options' => [
                        'Waiting years for the market to naturally appreciate',
                        'Making improvements, like finishing a basement, that instantly add value to the property',
                        'Refusing to negotiate on price',
                        'Selling the property below market value',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'Even primary homebuyers, not just investors, want reassurance about which of the following?',
                    'options' => [
                        'That they are making a financially safe long-term decision',
                        "That the agent's commission is fair",
                        'That the developer has won industry awards',
                        'That the unit will never need maintenance',
                    ],
                    'correct' => 0,
                ],
                [
                    'question' => 'What does the "Silent Tour" principle recommend an agent do?',
                    'options' => [
                        'Talk continuously throughout the entire showing to fill any silence',
                        'Point out key benefits, then give buyers physical space and quiet to explore and emotionally connect with the property',
                        'Avoid speaking at all during the visit',
                        'Leave the buyer alone in the unit without any guidance at all',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'Which best describes the overall objective of Module 4 in this course?',
                    'options' => [
                        'To teach agents how to close a sale on the spot',
                        'To execute flawless property showings that maximize appeal and help clients visualize ownership',
                        'To handle post-sale turnover concerns',
                        'To draft the Deed of Absolute Sale',
                    ],
                    'correct' => 1,
                ],
            ],
            6 => [
                [
                    'question' => 'What can happen if a real estate contract has a missing initial or an incorrectly typed deadline?',
                    'options' => [
                        'Nothing, as long as the price is correct',
                        "It can cost the client their earnest money deposit or kill the deal entirely",
                        "It only affects the agent's commission timeline",
                        'It automatically extends the closing date',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'Which of the following are examples of the documents covered in Lesson 5.1?',
                    'options' => [
                        'Purchase Agreements, Agency Disclosures, and Addendums',
                        'Only verbal agreements',
                        'Marketing brochures',
                        'Social media posts',
                    ],
                    'correct' => 0,
                ],
                [
                    'question' => 'What is the core takeaway of Lesson 5.1 regarding documentation?',
                    'options' => [
                        'Speed matters more than accuracy',
                        'Attention to detail is non-negotiable',
                        "Only the buyer's signature matters",
                        'Addendums are optional paperwork',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => "A buyer asks about a property's flood history. According to Lesson 5.2, what should you do?",
                    'options' => [
                        'Avoid the topic to protect the sale',
                        'Disclose known material facts honestly, such as past flood damage',
                        'Tell them to ask the developer directly and say nothing yourself',
                        'Only disclose if legally forced to in writing',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'Which of these counts as a "material fact" that must be disclosed under Lesson 5.2?',
                    'options' => [
                        "The agent's favorite paint color",
                        'Past flood damage, roof leaks, or upcoming zoning changes nearby',
                        "The seller's reason for moving",
                        "The buyer's negotiation strategy",
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'What does Lesson 5.2 say happens when an agent hides a known flaw to get a sale?',
                    'options' => [
                        'It has no real consequence if the buyer never finds out',
                        'It builds trust with the buyer',
                        'It is a shortcut to losing your license',
                        'It is standard, accepted practice',
                    ],
                    'correct' => 2,
                ],
                [
                    'question' => 'What does "fiduciary duty" mean for an agent, as described in Lesson 5.3?',
                    'options' => [
                        "The agent's commission must be disclosed to the other party",
                        "The client's financial well-being must legally and ethically be placed above the agent's desire to earn a commission",
                        'The agent should always represent both buyer and seller equally',
                        'The agent has no legal obligation to the client',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'Which of the following is part of fiduciary duty according to Lesson 5.3?',
                    'options' => [
                        'Confidentiality, loyalty, and accounting',
                        'Advertising, pricing, and staging',
                        'Financing, appraisal, and inspection',
                        'Prospecting, showing, and closing',
                    ],
                    'correct' => 0,
                ],
                [
                    'question' => "While negotiating on a client's behalf, what should you avoid revealing to the other party?",
                    'options' => [
                        "The property's asking price",
                        "The client's underlying motivations or maximum budget",
                        'The proposed move-in date',
                        'The names of the decision-makers',
                    ],
                    'correct' => 1,
                ],
                [
                    'question' => 'What is the overall objective of Module 5?',
                    'options' => [
                        'To teach agents how to advertise listings online',
                        'To protect the client and the brokerage through rigorous documentation, honest communication, and strict adherence to real estate law',
                        'To teach agents how to negotiate price reductions',
                        'To teach agents how to close deals faster',
                    ],
                    'correct' => 1,
                ],
            ],
        ];
    }

    /** Quiz questions for a module with the `correct` answer key stripped out — safe to send to the browser. */
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