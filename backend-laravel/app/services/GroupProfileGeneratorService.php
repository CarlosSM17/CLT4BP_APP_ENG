<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\GroupProfile;
use App\Models\StudentProfile;
use App\Models\StudentResponse;
use App\Models\User;

class GroupProfileGeneratorService
{
    public function generateGroupProfile(int $courseId): GroupProfile
    {
        $studentProfiles = StudentProfile::where('course_id', $courseId)->get();

        if ($studentProfiles->isEmpty()) {
            throw new \Exception('No hay perfiles individuales para generar perfil grupal');
        }

        $groupData = [
            'mslq_averages' => $this->calculateMslqAverages($studentProfiles),
            'knowledge_averages' => $this->calculateKnowledgeAverages($studentProfiles),
            'distribution' => $this->calculateDistribution($studentProfiles),
            'group_summary' => $this->generateGroupSummary($studentProfiles),
            'teaching_recommendations' => $this->generateTeachingRecommendations($studentProfiles)
        ];

        return GroupProfile::updateOrCreate(
            ['course_id' => $courseId],
            [
                'profile_data' => $groupData,
                'student_count' => $studentProfiles->count(),
                'generated_at' => now()
            ]
        );
    }

    private function calculateMslqAverages($profiles): array
    {
        $dimensions = [
            'intrinsic_goal_orientation', 'extrinsic_goal_orientation',
            'task_value', 'control_beliefs', 'self_efficacy', 'test_anxiety',
            'rehearsal', 'elaboration', 'organization', 'critical_thinking',
            'metacognitive_self_regulation', 'time_management',
            'effort_regulation', 'peer_learning', 'help_seeking'
        ];

        $averages = [];

        foreach ($dimensions as $dimension) {
            $values = $profiles->map(function($profile) use ($dimension) {
                return $this->getDimensionValue($profile->profile_data, $dimension);
            })->filter()->values();

            if ($values->isNotEmpty()) {
                $average = $values->avg();
                $averages[$dimension] = [
                    'average' => round($average, 2),
                    'level' => $this->determineLevel($average)
                ];
            }
        }

        return $averages;
    }

    private function getDimensionValue(array $profileData, string $dimension): ?float
    {
        // Buscar en motivación
        if (isset($profileData['mslq_analysis']['motivation'][$dimension])) {
            return $profileData['mslq_analysis']['motivation'][$dimension]['average'];
        }

        // Buscar en estrategias
        foreach (['cognitive', 'metacognitive', 'resource_management'] as $category) {
            if (isset($profileData['mslq_analysis']['strategies'][$category][$dimension])) {
                return $profileData['mslq_analysis']['strategies'][$category][$dimension]['average'];
            }
        }

        return null;
    }

    private function calculateKnowledgeAverages($profiles): array
    {
        $recallScores = $profiles->pluck('profile_data.knowledge_assessment.recall.percentage');
        $comprehensionScores = $profiles->pluck('profile_data.knowledge_assessment.comprehension.percentage');

        $avgRecall = $recallScores->avg();
        $avgComprehension = $comprehensionScores->avg();
        $overall = ($avgRecall + $avgComprehension) / 2;

        return [
            'recall' => [
                'average' => round($avgRecall, 2),
                'level' => $this->determineKnowledgeLevel($avgRecall)
            ],
            'comprehension' => [
                'average' => round($avgComprehension, 2),
                'level' => $this->determineKnowledgeLevel($avgComprehension)
            ],
            'overall' => [
                'average' => round($overall, 2),
                'level' => $this->determineKnowledgeLevel($overall)
            ]
        ];
    }

    private function calculateDistribution($profiles): array
    {
        $distribution = [
            'motivation_levels' => ['alto' => 0, 'medio' => 0, 'bajo' => 0],
            'strategies_levels' => ['alto' => 0, 'medio' => 0, 'bajo' => 0],
            'knowledge_levels' => ['alto' => 0, 'medio' => 0, 'bajo' => 0]
        ];

        foreach ($profiles as $profile) {
            $data = $profile->profile_data;

            $motivationLevel = $data['profile_summary']['overall_motivation'] ?? 'medio';
            $strategiesLevel = $data['profile_summary']['overall_strategies'] ?? 'medio';
            $knowledgeLevel = $data['profile_summary']['prior_knowledge'] ?? 'medio';

            $distribution['motivation_levels'][$motivationLevel]++;
            $distribution['strategies_levels'][$strategiesLevel]++;
            $distribution['knowledge_levels'][$knowledgeLevel]++;
        }

        return $distribution;
    }

    private function generateGroupSummary($profiles): array
    {
        $distribution = $this->calculateDistribution($profiles);
        $total = $profiles->count();

        return [
            'predominant_motivation' => $this->getPredominantLevel($distribution['motivation_levels']),
            'predominant_strategies' => $this->getPredominantLevel($distribution['strategies_levels']),
            'predominant_knowledge' => $this->getPredominantLevel($distribution['knowledge_levels']),
            'group_characteristics' => $this->describeGroupCharacteristics($distribution, $total)
        ];
    }

    private function getPredominantLevel(array $levels): string
    {
        arsort($levels);
        return array_key_first($levels);
    }

    private function describeGroupCharacteristics(array $distribution, int $total): string
    {
        $motivationHigh = ($distribution['motivation_levels']['alto'] / $total) * 100;
        $knowledgeLow = ($distribution['knowledge_levels']['bajo'] / $total) * 100;

        if ($motivationHigh > 60 && $knowledgeLow < 30) {
            return 'Grupo altamente motivado con buen conocimiento previo';
        } elseif ($motivationHigh < 30 && $knowledgeLow > 50) {
            return 'Grupo que requiere apoyo motivacional y contenido fundamental';
        } elseif ($motivationHigh > 50) {
            return 'Grupo motivado con conocimiento previo variable';
        } else {
            return 'Grupo heterogéneo que requiere diferenciación instruccional';
        }
    }

    private function generateTeachingRecommendations($profiles): array
    {
        $distribution = $this->calculateDistribution($profiles);
        $recommendations = [];

        $predominantKnowledge = $this->getPredominantLevel($distribution['knowledge_levels']);

        if ($predominantKnowledge === 'bajo') {
            $recommendations[] = 'Priorizar efectos CLT para novatos en material grupal';
            $recommendations[] = 'Usar ejemplos resueltos y problemas por completar';
        } elseif ($predominantKnowledge === 'alto') {
            $recommendations[] = 'Aplicar efectos CLT para expertos en material grupal';
            $recommendations[] = 'Fomentar auto-explicación y resolución de problemas';
        } else {
            $recommendations[] = 'Combinar efectos CLT para diferentes niveles';
            $recommendations[] = 'Considerar material individual para casos extremos';
        }

        $predominantMotivation = $this->getPredominantLevel($distribution['motivation_levels']);

        if ($predominantMotivation === 'bajo') {
            $recommendations[] = 'Enfatizar estrategias ARCS en todo el material';
            $recommendations[] = 'Incluir múltiples ejemplos relevantes y aplicaciones prácticas';
        }

        return $recommendations;
    }

    private function determineLevel(float $average): string
    {
        if ($average >= 5.5) return 'alto';
        if ($average >= 3.5) return 'medio';
        return 'bajo';
    }

    private function determineKnowledgeLevel(float $percentage): string
    {
        if ($percentage >= 75) return 'alto';
        if ($percentage >= 50) return 'medio';
        return 'bajo';
    }
}
