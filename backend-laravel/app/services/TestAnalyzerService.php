<?php

namespace App\Services;

class TestAnalyzerService
{
    public function analyzeTest(array $responses, array $correctAnswers): array
    {
        $totalQuestions = count($correctAnswers);
        $correctCount = 0;
        $incorrectQuestions = [];

        // Si no hay preguntas con respuestas correctas, devolver valores por defecto
        if ($totalQuestions === 0) {
            return [
                'total_questions' => 0,
                'correct_answers' => 0,
                'incorrect_answers' => 0,
                'percentage' => 0,
                'level' => 'no_data',
                'incorrect_questions' => []
            ];
        }

        foreach ($correctAnswers as $questionId => $correctAnswer) {
            if (isset($responses[$questionId])) {
                if ($responses[$questionId] === $correctAnswer) {
                    $correctCount++;
                } else {
                    $incorrectQuestions[] = $questionId;
                }
            }
        }

        $percentage = ($correctCount / $totalQuestions) * 100;

        return [
            'total_questions' => $totalQuestions,
            'correct_answers' => $correctCount,
            'incorrect_answers' => $totalQuestions - $correctCount,
            'percentage' => round($percentage, 2),
            'level' => $this->determineKnowledgeLevel($percentage),
            'incorrect_questions' => $incorrectQuestions
        ];
    }

    private function determineKnowledgeLevel(float $percentage): string
    {
        if ($percentage >= 75) return 'alto';
        if ($percentage >= 50) return 'medio';
        return 'bajo';
    }

    public function analyzeBothTests(array $recallResults, array $comprehensionResults): array
    {
        $overallPercentage = ($recallResults['percentage'] + $comprehensionResults['percentage']) / 2;

        return [
            'recall' => $recallResults,
            'comprehension' => $comprehensionResults,
            'overall_level' => $this->determineKnowledgeLevel($overallPercentage),
            'overall_percentage' => round($overallPercentage, 2),
            'analysis' => $this->generateAnalysis($recallResults, $comprehensionResults)
        ];
    }

    private function generateAnalysis(array $recall, array $comprehension): string
    {
        if ($recall['level'] === 'alto' && $comprehension['level'] === 'alto') {
            return 'El estudiante demuestra excelente conocimiento previo tanto en recordar información como en comprenderla.';
        }

        if ($recall['level'] === 'bajo' && $comprehension['level'] === 'bajo') {
            return 'El estudiante requiere instrucción fundamental en ambos aspectos: recordar y comprender.';
        }

        if ($recall['level'] > $comprehension['level']) {
            return 'El estudiante puede recordar información pero necesita apoyo en comprensión profunda.';
        }

        return 'El estudiante comprende conceptos pero necesita reforzar la capacidad de recordar detalles.';
    }
}
