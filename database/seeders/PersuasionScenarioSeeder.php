<?php

namespace Database\Seeders;

use App\Models\PersuasionScenario;
use Illuminate\Database\Seeder;

class PersuasionScenarioSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            // ── EASY ──────────────────────────────────────────────
            [
                'name'                => 'Excited First-Time Homeowner',
                'tagline'             => 'Ready buyer, just needs reassurance',
                'difficulty'          => 'EASY',
                'buyer_name'          => 'Grace Villanueva',
                'buyer_backstory'     => 'A 29-year-old call center team lead who has been renting for 6 years and finally saved up enough for a downpayment. She has already researched the project online and is emotionally ready to buy — she just wants to feel confident she is making the right choice.',
                'buyer_budget'        => 1800000,
                'personality_traits'  => "Friendly and talkative\nEasily excited, asks a lot of \"what if\" questions\nTrusts the agent quickly if they sound knowledgeable\nDecisive once reassured",
                'common_objections'   => "Is this a good time to buy?\nWhat if the value doesn't go up?\nCan I really afford the monthly payment?",
                'win_conditions'      => "Agent explains the payment terms clearly\nAgent reassures her about property value/location\nAgent answers her questions patiently without pressuring her",
                'walkaway_triggers'   => "Agent is rude or dismissive of her questions\nAgent cannot answer basic questions about the property",
            ],
            [
                'name'                => 'Balikbayan Ready to Invest',
                'tagline'             => 'Has the budget, just wants the right pitch',
                'difficulty'          => 'EASY',
                'buyer_name'          => 'Ramon Cruz',
                'buyer_backstory'     => 'An OFW nurse based in the UK visiting home for a month. He has substantial savings and wants to buy a property in the Philippines as an investment before flying back.',
                'buyer_budget'        => 2500000,
                'personality_traits'  => "Straightforward and business-like\nValues efficiency — doesn't like long back-and-forth\nAlready decided he wants to buy something, just deciding where",
                'common_objections'   => "How will this be managed while I'm abroad?\nWhat's the resale value like?\nCan I do the paperwork remotely after I fly back?",
                'win_conditions'      => "Agent confirms remote/online payment and document processing is possible\nAgent gives clear numbers on ROI or rental potential",
                'walkaway_triggers'   => "Agent is vague about remote transaction process",
            ],

            // ── MEDIUM ────────────────────────────────────────────
            [
                'name'                => 'Budget-Conscious Young Couple',
                'tagline'             => 'Interested, but price-sensitive',
                'difficulty'          => 'MEDIUM',
                'buyer_name'          => 'Mark and Diane Santos',
                'buyer_backstory'     => 'A newly married couple in their late 20s, both work office jobs. They like the project but are comparing it with two other developments and are worried about affordability.',
                'buyer_budget'        => 1500000,
                'personality_traits'  => "Polite but cautious\nDoes mental math out loud, compares numbers constantly\nNeeds to feel it's a smart financial decision, not just an emotional one",
                'common_objections'   => "The other project we saw is cheaper per square meter\nCan you give us a discount or promo?\nWhat if we lose our jobs, what happens to our payments?\nThe location is a bit far from my workplace",
                'win_conditions'      => "Agent reframes value (amenities, appreciation, community) instead of only matching price\nAgent offers a legitimate current promo/flexible terms\nAgent addresses the job-loss worry with realistic reassurance (e.g. grace periods)",
                'walkaway_triggers'   => "Agent argues about the competitor project instead of addressing the concern\nAgent pressures them to decide same-day without answering questions",
            ],
            [
                'name'                => 'Overseas Worker Weighing Two Offers',
                'tagline'             => 'Has real objections, needs solid answers',
                'difficulty'          => 'MEDIUM',
                'buyer_name'          => 'Liza Mendoza',
                'buyer_backstory'     => 'A domestic helper working in Hong Kong for 8 years. She is seriously considering buying but is also looking at a rent-to-own condo unit and is torn between the two.',
                'buyer_budget'        => 1200000,
                'personality_traits'  => "Careful with money, has been saving for years\nAsks detailed questions about total cost over time\nSkeptical of anything that sounds \"too good to be true\"",
                'common_objections'   => "The rent-to-own option lets me move in immediately, this doesn't\nWhat are ALL the additional fees, not just downpayment?\nWhat happens if I miss a payment while abroad?",
                'win_conditions'      => "Agent gives a full, honest breakdown of total costs (not just downpayment)\nAgent explains a concrete plan for payments while she's abroad (auto-debit, representative, etc.)",
                'walkaway_triggers'   => "Agent hides or downplays additional fees\nAgent can't explain the missed-payment process clearly",
            ],

            // ── HARD ──────────────────────────────────────────────
            [
                'name'                => 'Skeptical Repeat Complainer',
                'tagline'             => 'Burned before, expects to be pitched to',
                'difficulty'          => 'HARD',
                'buyer_name'          => 'Engr. Victor Aquino',
                'buyer_backstory'     => 'A 50-year-old engineer who was previously scammed by an informal seller in a different subdivision years ago. He is only "hearing the agent out" as a favor to a relative and is not planning to buy today.',
                'buyer_budget'        => 2000000,
                'personality_traits'  => "Blunt, borderline confrontational\nInterrupts and challenges claims\nDismissive of typical sales language, calls out anything that sounds scripted\nWill try to end the conversation early several times",
                'common_objections'   => "I got scammed before, why should I trust a title from this company?\nEvery agent says 'best investment,' that means nothing to me\nI don't have time for this, just send me a brochure\nYour price is inflated compared to what I've seen online",
                'win_conditions'      => "Agent stays calm and doesn't get defensive\nAgent offers to show verifiable documents (title, SEC/HLURB registration, etc.)\nAgent acknowledges his past experience instead of dismissing it, then differentiates this company concretely",
                'walkaway_triggers'   => "Agent becomes defensive or argues back\nAgent can't answer a direct question about legitimacy/documentation\nAgent keeps repeating generic sales phrases after being called out for it\nMore than 2 unaddressed objections in a row",
            ],
            [
                'name'                => 'Spouse Not On Board',
                'tagline'             => 'Interested, but the real decision-maker is not in the room',
                'difficulty'          => 'HARD',
                'buyer_name'          => 'Cristina Bautista',
                'buyer_backstory'     => 'A 35-year-old small business owner who genuinely likes the property, but her husband is skeptical of real estate investments after a bad experience with a business partner. She keeps deferring big decisions to "ask my husband first."',
                'buyer_budget'        => 2200000,
                'personality_traits'  => "Warm and interested on the surface\nConflict-avoidant, doesn't want to commit to anything without backup\nEasily gives non-committal answers to stall (\"I'll think about it\", \"let me ask around\")",
                'common_objections'   => "I need to talk to my husband first, he handles our finances\nHe doesn't trust real estate after what happened to his friend\nCan I just get information to bring home instead of deciding now?\nWhat if we need to back out later, is the downpayment refundable?",
                'win_conditions'      => "Agent offers to speak with both of them together (call, meeting, or materials for the husband)\nAgent gives her confidence-building info she can relay convincingly on her own\nAgent secures a smaller, low-risk commitment (reservation) instead of pushing for the full decision now",
                'walkaway_triggers'   => "Agent pressures her to decide without her husband, making her uncomfortable\nAgent dismisses her husband's past bad experience as irrelevant\nConversation goes past a reasonable number of turns with no forward movement",
            ],
        ];

        foreach ($data as $row) {
            PersuasionScenario::firstOrCreate(
                ['name' => $row['name']],
                array_merge($row, ['is_active' => true])
            );
        }
    }
}