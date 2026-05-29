<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\StudentProfile;
use App\Models\StudentResponse;
use App\Models\User;

class StudentProfileGeneratorService
{
    private MslqProcessorService $mslqProcessor;
    private TestAnalyzerService $testAnalyzer;

    public function __construct(
        MslqProcessorService $mslqProcessor,
        TestAnalyzerService $testAnalyzer
    ) {
        $this->mslqProcessor = $mslqProcessor;
        $this->testAnalyzer = $testAnalyzer;
    }

    public function generateProfile(int $studentId, int $courseId): StudentProfile
    {
        // Obtener todas las respuestas necesarias
        // Nota: Los tipos de evaluación son 'mslq_motivation_initial' y 'mslq_strategies'
        $mslqMotivation = $this->getAssessmentResponses(
            $studentId, $courseId, 'mslq_motivation_initial'
        );
        $mslqStrategies = $this->getAssessmentResponses(
            $studentId, $courseId, 'mslq_strategies'
        );
        $recallTest = $this->getAssessmentResponses(
            $studentId, $courseId, 'recall_initial'
        );
        $comprehensionTest = $this->getAssessmentResponses(
            $studentId, $courseId, 'comprehension_initial'
        );

        // Procesar MSLQ completo
        $allMslqResponses = array_merge(
            $mslqMotivation['responses'] ?? [],
            $mslqStrategies['responses'] ?? []
        );
        $mslqAnalysis = $this->mslqProcessor->processResponses($allMslqResponses);

        // Analizar tests
        $recallAnalysis = $this->testAnalyzer->analyzeTest(
            $recallTest['responses'] ?? [],
            $recallTest['correct_answers'] ?? []
        );
        $comprehensionAnalysis = $this->testAnalyzer->analyzeTest(
            $comprehensionTest['responses'] ?? [],
            $comprehensionTest['correct_answers'] ?? []
        );
        $testsAnalysis = $this->testAnalyzer->analyzeBothTests(
            $recallAnalysis,
            $comprehensionAnalysis
        );

        // Extraer todas las puntuaciones MSLQ aplanadas para el frontend
        $mslqScores = $this->flattenMslqScores($mslqAnalysis);

        // Construir perfil completo
        $profileData = [
            'student_info' => [
                'student_id' => $studentId,
                'course_id' => $courseId,
                'name' => User::find($studentId)->name
            ],
            'mslq_analysis' => $mslqAnalysis,
            'mslq_scores' => $mslqScores, // Estructura aplanada para el frontend
            'knowledge_assessment' => $testsAnalysis,
            'profile_summary' => $this->generateProfileSummary(
                $mslqAnalysis,
                $testsAnalysis
            ),
            'recommendations' => $this->generateRecommendations(
                $mslqAnalysis,
                $testsAnalysis
            )
        ];

        // Guardar o actualizar perfil
        return StudentProfile::updateOrCreate(
            [
                'student_id' => $studentId,
                'course_id' => $courseId
            ],
            [
                'profile_data' => $profileData,
                'generated_at' => now()
            ]
        );
    }

    private function getAssessmentResponses(
        int $studentId,
        int $courseId,
        string $assessmentType
    ): array {
        $assessment = Assessment::where('course_id', $courseId)
            ->where('assessment_type', $assessmentType)
            ->first();

        if (!$assessment) {
            return ['responses' => [], 'correct_answers' => []];
        }

        $response = StudentResponse::where('assessment_id', $assessment->id)
            ->where('student_id', $studentId)
            ->first();

        $correctAnswers = collect($assessment->questions ?? [])
            ->filter(fn($q) => isset($q['correct_answer']))
            ->mapWithKeys(fn($q) => [$q['id'] => $q['correct_answer']])
            ->toArray();

        return [
            'responses' => $response ? $response->responses : [],
            'correct_answers' => $correctAnswers,
        ];
    }

    private function generateProfileSummary(array $mslq, array $tests): array
    {
        return [
            'overall_motivation' => $mslq['summary']['overall_motivation_level'],
            'overall_strategies' => $mslq['summary']['overall_strategies_level'],
            'prior_knowledge' => $tests['overall_level'],
            'key_strengths' => array_merge(
                $mslq['summary']['strengths'],
                $tests['overall_level'] === 'alto' ? ['prior_knowledge'] : []
            ),
            'areas_for_support' => array_merge(
                $mslq['summary']['weaknesses'],
                $tests['overall_level'] === 'bajo' ? ['prior_knowledge'] : []
            )
        ];
    }

    private function generateRecommendations(array $mslq, array $tests): array
    {
        $recommendations = [];

        // Recomendaciones basadas en motivación
        if ($mslq['summary']['overall_motivation_level'] === 'bajo') {
            $recommendations[] = 'Aplicar estrategias ARCS con énfasis en Atención y Relevancia';
            $recommendations[] = 'Incluir ejemplos del mundo real y aplicaciones prácticas';
        }

        // Recomendaciones basadas en estrategias
        if ($mslq['summary']['overall_strategies_level'] === 'bajo') {
            $recommendations[] = 'Proporcionar guías explícitas de estrategias de estudio';
            $recommendations[] = 'Modelar procesos metacognitivos mediante protocolos verbales';
        }

        // Recomendaciones basadas en conocimiento previo
        if ($tests['overall_level'] === 'bajo') {
            $recommendations[] = 'Usar efectos CLT para novatos: ejemplos resueltos, problemas por completar';
            $recommendations[] = 'Minimizar carga cognitiva extrínseca';
        } elseif ($tests['overall_level'] === 'alto') {
            $recommendations[] = 'Aplicar efectos CLT para expertos: auto-explicación, imaginación';
            $recommendations[] = 'Considerar reversión de experiencia para algunos materiales';
        }

        return $recommendations;
    }

    /**
     * Aplana las puntuaciones MSLQ para que el frontend pueda accederlas fácilmente
     */
    private function flattenMslqScores(array $mslqAnalysis): array
    {
        $flattened = [];

        // Extraer dimensiones de motivación
        if (isset($mslqAnalysis['motivation'])) {
            foreach ($mslqAnalysis['motivation'] as $dimension => $data) {
                $flattened[$dimension] = [
                    'average' => $data['average'] ?? 0,
                    'level' => $data['level'] ?? 'bajo',
                    'raw_score' => $data['raw_score'] ?? 0,
                    'items_answered' => $data['items_answered'] ?? 0,
                    'items_total' => $data['items_total'] ?? 0,
                ];
            }
        }

        // Extraer dimensiones de estrategias cognitivas
        if (isset($mslqAnalysis['strategies']['cognitive'])) {
            foreach ($mslqAnalysis['strategies']['cognitive'] as $dimension => $data) {
                $flattened[$dimension] = [
                    'average' => $data['average'] ?? 0,
                    'level' => $data['level'] ?? 'bajo',
                    'raw_score' => $data['raw_score'] ?? 0,
                    'items_answered' => $data['items_answered'] ?? 0,
                    'items_total' => $data['items_total'] ?? 0,
                ];
            }
        }

        // Extraer dimensiones metacognitivas
        if (isset($mslqAnalysis['strategies']['metacognitive'])) {
            foreach ($mslqAnalysis['strategies']['metacognitive'] as $dimension => $data) {
                $flattened[$dimension] = [
                    'average' => $data['average'] ?? 0,
                    'level' => $data['level'] ?? 'bajo',
                    'raw_score' => $data['raw_score'] ?? 0,
                    'items_answered' => $data['items_answered'] ?? 0,
                    'items_total' => $data['items_total'] ?? 0,
                ];
            }
        }

        // Extraer dimensiones de manejo de recursos
        if (isset($mslqAnalysis['strategies']['resource_management'])) {
            foreach ($mslqAnalysis['strategies']['resource_management'] as $dimension => $data) {
                $flattened[$dimension] = [
                    'average' => $data['average'] ?? 0,
                    'level' => $data['level'] ?? 'bajo',
                    'raw_score' => $data['raw_score'] ?? 0,
                    'items_answered' => $data['items_answered'] ?? 0,
                    'items_total' => $data['items_total'] ?? 0,
                ];
            }
        }

        return $flattened;
    }
}
