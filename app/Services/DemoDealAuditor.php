<?php

namespace App\Services;

class DemoDealAuditor
{
    private const SIGNALS = [
        [
            'key' => 'champion_departure',
            'type' => 'risk',
            'severity' => 'high',
            'label' => 'Customer champion may have left',
            'pattern' => '/\b(champion|main contact|primary contact|key contact)\b[^.!?\r\n]{0,70}\b(left|departed|resigned|no longer|was let go)\b|\b(left|departed|resigned|no longer with|was let go)\b[^.!?\r\n]{0,70}\b(champion|main contact|primary contact|key contact)\b/i',
            'impact' => 18,
            'action' => 'Re-map the buying group and confirm a new executive sponsor.',
        ],
        [
            'key' => 'security_concern',
            'type' => 'risk',
            'severity' => 'high',
            'label' => 'Security or compliance concern is open',
            'pattern' => '/\b(security|compliance|infosec|data privacy)\b[^.!?\r\n]{0,70}\b(concern|blocker|unanswered|unresolved|review|question|issue|blocked)\b|\b(concern|blocker|unanswered|unresolved|review|question|issue|blocked)\b[^.!?\r\n]{0,70}\b(security|compliance|infosec|data privacy)\b/i',
            'impact' => 18,
            'action' => 'Assign an owner and due date for the security response; share verified documentation.',
        ],
        [
            'key' => 'procurement_delay',
            'type' => 'risk',
            'severity' => 'medium',
            'label' => 'Procurement or approval is delayed',
            'pattern' => '/\b(procurement|legal|approval|purchase order|budget sign[- ]off)\b[^.!?\r\n]{0,70}\b(delayed|stalled|pending|blocked|waiting|slipped|not approved)\b|\b(delayed|stalled|pending|blocked|waiting|slipped|not approved)\b[^.!?\r\n]{0,70}\b(procurement|legal|approval|purchase order|budget sign[- ]off)\b/i',
            'impact' => 12,
            'action' => 'Confirm the approval owner, remaining steps, and a mutually agreed decision date.',
        ],
        [
            'key' => 'usage_decline',
            'type' => 'risk',
            'severity' => 'medium',
            'label' => 'Product usage is declining',
            'pattern' => '/\b(usage|active users|adoption|logins|activity)\b[^.!?\r\n]{0,70}\b(down|declin(?:e|ed|ing)|dropped|falling|decreased|lower)\b|\b(down|declin(?:e|ed|ing)|dropped|falling|decreased|lower)\b[^.!?\r\n]{0,70}\b(usage|active users|adoption|logins|activity)\b/i',
            'impact' => 12,
            'action' => 'Review the affected users and workflow, then agree on a focused adoption recovery plan.',
        ],
        [
            'key' => 'unresponsive_buyer',
            'type' => 'risk',
            'severity' => 'medium',
            'label' => 'Buyer engagement has stalled',
            'pattern' => '/\b(no response|not responding|unresponsive|stopped replying|gone quiet|missed (?:the )?last .{0,15}(?:two|three|several) (?:calls|meetings|emails))\b/i',
            'impact' => 12,
            'action' => 'Try a concise, value-focused check-in through an existing trusted contact.',
        ],
        [
            'key' => 'usage_growth',
            'type' => 'opportunity',
            'severity' => 'positive',
            'label' => 'Product usage is growing',
            'pattern' => '/\b(usage|active users|adoption|logins|activity)\b[^.!?\r\n]{0,70}\b(up|increas(?:e|ed|ing)|grew|growth|spike|higher|expanded)\b|\b(up|increas(?:e|ed|ing)|grew|growth|spike|higher|expanded)\b[^.!?\r\n]{0,70}\b(usage|active users|adoption|logins|activity)\b/i',
            'impact' => 0,
            'action' => 'Validate the expanded use case and discuss the next team or workflow to support.',
        ],
        [
            'key' => 'seat_capacity',
            'type' => 'opportunity',
            'severity' => 'positive',
            'label' => 'Licenses are near capacity',
            'pattern' => '/\b(seats?|licenses?|capacity)\b[^.!?\r\n]{0,70}\b(cap|limit|full|maximum|exceeded|near|reached)\b|\b(cap|limit|full|maximum|exceeded|near|reached)\b[^.!?\r\n]{0,70}\b(seats?|licenses?|capacity)\b/i',
            'impact' => 0,
            'action' => 'Confirm active seat demand and prepare a right-sized expansion option.',
        ],
        [
            'key' => 'executive_sponsor',
            'type' => 'opportunity',
            'severity' => 'positive',
            'label' => 'Executive sponsor is engaged',
            'pattern' => '/\b(executive sponsor|vp|chief|cfo|cro|ceo)\b[^.!?\r\n]{0,70}\b(engaged|joined|supportive|approved|aligned|championing)\b|\b(engaged|joined|supportive|approved|aligned|championing)\b[^.!?\r\n]{0,70}\b(executive sponsor|vp|chief|cfo|cro|ceo)\b/i',
            'impact' => 0,
            'action' => 'Document the sponsor’s target outcomes and align the renewal plan to them.',
        ],
    ];

    public function analyze(string $input): array
    {
        $startedAt = hrtime(true);
        $matches = [];
        $usedKeys = [];

        foreach (self::SIGNALS as $signal) {
            if (isset($usedKeys[$signal['key']]) || ! preg_match($signal['pattern'], $input, $match, PREG_OFFSET_CAPTURE)) {
                continue;
            }

            $matchedText = $match[0][0];
            $start = $match[0][1];
            $quote = $this->evidenceSentence($input, $start, strlen($matchedText));
            $matches[] = [
                'type' => $signal['type'],
                'severity' => $signal['severity'],
                'label' => $signal['label'],
                'evidence' => $quote,
                'action' => $signal['action'],
            ];
            $usedKeys[$signal['key']] = true;
        }

        $risks = array_values(array_filter($matches, fn (array $match): bool => $match['type'] === 'risk'));
        $opportunities = array_values(array_filter($matches, fn (array $match): bool => $match['type'] === 'opportunity'));
        $riskImpact = array_sum(array_map(
            fn (array $match): int => $this->impactForLabel($match['label']),
            $risks,
        ));
        $score = max(5, min(98, 84 - $riskImpact + min(8, count($opportunities) * 3)));
        $durationMs = max(1, (int) round((hrtime(true) - $startedAt) / 1_000_000));
        $wordCount = str_word_count($input);

        return [
            'score' => $score,
            'health' => $score >= 70 ? 'Healthy signals' : ($score >= 45 ? 'Needs attention' : 'Elevated risk'),
            'risks' => $risks,
            'opportunities' => $opportunities,
            'recommendations' => array_values(array_slice(array_map(fn (array $signal): string => $signal['action'], $matches), 0, 3)),
            'outreach' => $this->draftOutreach($risks, $opportunities),
            'metrics' => [
                'processing_ms' => $durationMs,
                'input_words' => $wordCount,
            ],
            'notice' => 'Illustrative rules-based output from the submitted text. This is not a churn prediction, and no text is saved.',
        ];
    }

    private function impactForLabel(string $label): int
    {
        foreach (self::SIGNALS as $signal) {
            if ($signal['label'] === $label) {
                return $signal['impact'];
            }
        }

        return 0;
    }

    private function evidenceSentence(string $input, int $matchStart, int $matchLength): string
    {
        preg_match_all('/[^.!?\r\n]+(?:[.!?]+|$)/u', $input, $sentences, PREG_OFFSET_CAPTURE);

        foreach ($sentences[0] as [$sentence, $offset]) {
            if ($offset <= $matchStart && $offset + strlen($sentence) >= $matchStart + $matchLength) {
                return trim($sentence);
            }
        }

        return trim(substr($input, max(0, $matchStart - 40), $matchLength + 80));
    }

    private function draftOutreach(array $risks, array $opportunities): string
    {
        if ($risks !== []) {
            return "Hi [Name],\n\nI wanted to follow up on the items your team raised. Could we align on the right owner and next step for: "
                .implode('; ', array_column($risks, 'label'))
                ."?\n\nI’m happy to coordinate a short working session at a time that suits you.\n\nBest,\n[Your name]";
        }

        if ($opportunities !== []) {
            return "Hi [Name],\n\nIt’s great to see the progress your team is making. Could we review the recent usage and capacity signals together and identify where we can support your next phase?\n\nBest,\n[Your name]";
        }

        return "Hi [Name],\n\nI’d value a brief check-in to understand your current priorities, what is working well, and where your team may need more support. Would you have 20 minutes this week?\n\nBest,\n[Your name]";
    }
}
