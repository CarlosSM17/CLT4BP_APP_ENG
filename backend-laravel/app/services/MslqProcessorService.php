<?php

namespace App\Services;

class MslqProcessorService
{
    /**
     * Dimensiones MSLQ y sus ítems
     */
    private const DIMENSIONS = [
        'intrinsic_goal_orientation' => [1, 16, 22, 24],
        'extrinsic_goal_orientation' => [7, 11, 13, 30],
        'task_value' => [4, 10, 17, 23, 26, 27],
        'control_beliefs' => [2, 9, 18, 25],
        'self_efficacy' => [5, 6, 12, 15, 20, 21, 29, 31],
        'test_anxiety' => [3, 8, 14, 19, 28],
        // Estrategias
        'rehearsal' => [39, 46, 59, 72],
        'elaboration' => [53, 62, 64, 67, 69, 81],
        'organization' => [32, 42, 49, 63],
        'critical_thinking' => [38, 47, 51, 66, 71],
        'metacognitive_self_regulation' => [33, 36, 41, 44, 54, 55, 56, 57, 61, 76, 78, 79],
        'time_management' => [35, 43, 52, 65, 70, 73, 77, 80],
        'effort_regulation' => [37, 48, 60, 74],
        'peer_learning' => [34, 45, 50],
        'help_seeking' => [40, 58, 68, 75]
    ];

    public function processResponses(array $responses): array
    {
        $scores = [];

        foreach (self::DIMENSIONS as $dimension => $items) {
            $dimensionScores = [];
            foreach ($items as $item) {
                // Las respuestas pueden venir indexadas por ID de pregunta o por número de ítem
                $itemKey = (string) $item;
                if (isset($responses[$itemKey])) {
                    $dimensionScores[] = (float) $responses[$itemKey];
                } elseif (isset($responses[$item])) {
                    $dimensionScores[] = (float) $responses[$item];
                }
            }

            $average = count($dimensionScores) > 0
                ? array_sum($dimensionScores) / count($dimensionScores)
                : 0;

            $scores[$dimension] = [
                'raw_score' => array_sum($dimensionScores),
                'average' => round($average, 2),
                'level' => $this->determineLevel($average),
                'items_answered' => count($dimensionScores),
                'items_total' => count($items)
            ];
        }

        return [
            'motivation' => $this->extractMotivation($scores),
            'strategies' => $this->extractStrategies($scores),
            'summary' => $this->generateSummary($scores)
        ];
    }

    private function determineLevel(float $average): string
    {
        if ($average >= 5.5) return 'alto';
        if ($average >= 3.5) return 'medio';
        return 'bajo';
    }

    private function extractMotivation(array $scores): array
    {
        return [
            'intrinsic_goal_orientation' => $scores['intrinsic_goal_orientation'],
            'extrinsic_goal_orientation' => $scores['extrinsic_goal_orientation'],
            'task_value' => $scores['task_value'],
            'control_beliefs' => $scores['control_beliefs'],
            'self_efficacy' => $scores['self_efficacy'],
            'test_anxiety' => $scores['test_anxiety']
        ];
    }

    private function extractStrategies(array $scores): array
    {
        return [
            'cognitive' => [
                'rehearsal' => $scores['rehearsal'],
                'elaboration' => $scores['elaboration'],
                'organization' => $scores['organization'],
                'critical_thinking' => $scores['critical_thinking']
            ],
            'metacognitive' => [
                'metacognitive_self_regulation' => $scores['metacognitive_self_regulation']
            ],
            'resource_management' => [
                'time_management' => $scores['time_management'],
                'effort_regulation' => $scores['effort_regulation'],
                'peer_learning' => $scores['peer_learning'],
                'help_seeking' => $scores['help_seeking']
            ]
        ];
    }

    private function generateSummary(array $scores): array
    {
        $strengths = [];
        $weaknesses = [];

        foreach ($scores as $dimension => $data) {
            if ($data['level'] === 'alto') {
                $strengths[] = $dimension;
            } elseif ($data['level'] === 'bajo') {
                $weaknesses[] = $dimension;
            }
        }

        return [
            'strengths' => $strengths,
            'weaknesses' => $weaknesses,
            'overall_motivation_level' => $this->calculateOverallLevel($scores, 'motivation'),
            'overall_strategies_level' => $this->calculateOverallLevel($scores, 'strategies')
        ];
    }

    private function calculateOverallLevel(array $scores, string $type): string
    {
        // Lógica para determinar nivel general
        $relevantDimensions = $type === 'motivation'
            ? ['intrinsic_goal_orientation', 'task_value', 'self_efficacy']
            : ['elaboration', 'organization', 'metacognitive_self_regulation'];

        $levels = array_map(
            fn($dim) => $scores[$dim]['average'],
            $relevantDimensions
        );

        $average = array_sum($levels) / count($levels);
        return $this->determineLevel($average);
    }
}
