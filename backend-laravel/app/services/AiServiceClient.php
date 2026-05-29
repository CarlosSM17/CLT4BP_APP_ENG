<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class AiServiceClient
{
    private string $baseUrl;
    private ?string $apiToken;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('services.ai_service.url', 'http://localhost:8001');
        $this->apiToken = config('services.ai_service.token') ?? '';
        $this->timeout = config('services.ai_service.timeout', 120);
    }

    /**
     * Genera material instruccional
     *
     * @param array $requestData
     * @return array
     * @throws Exception
     */
    public function generateMaterial(array $requestData): array
    {
        try {
            Log::info('Llamando al servicio de IA para generar material', [
                'material_type' => $requestData['material_type']
            ]);

            /** @var Response $response */
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'X-Api-Token' => $this->apiToken,
                    'Accept' => 'application/json',
                ])
                ->post("{$this->baseUrl}/api/v1/materials/generate", $requestData);

            if (!$response->successful()) {
                throw new Exception(
                    "Error del servicio de IA: " . $response->body()
                );
            }

            $data = $response->json();

            Log::info('Material generado exitosamente', [
                'tokens_used' => $data['token_usage']['total_tokens'] ?? 0
            ]);

            return $data;

        } catch (Exception $e) {
            Log::error('Error al generar material con IA', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Valida conexión con el servicio de IA
     *
     * @return bool
     */
    public function validateConnection(): bool
    {
        try {
            /** @var Response $response */
            $response = Http::timeout(10)
                ->withHeaders(['X-Api-Token' => $this->apiToken])
                ->post("{$this->baseUrl}/api/v1/materials/validate");

            return $response->successful() && $response->json()['success'];
        } catch (Exception $e) {
            Log::error('Error al validar conexión con servicio de IA', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Obtiene lista de efectos CLT disponibles
     *
     * @return array
     * @throws Exception
     */
    public function getCltEffects(): array
    {
        try {
            /** @var Response $response */
            $response = Http::timeout(10)
                ->withHeaders(['X-Api-Token' => $this->apiToken])
                ->get("{$this->baseUrl}/api/v1/materials/clt-effects");

            if (!$response->successful()) {
                throw new Exception("Error al obtener efectos CLT");
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('Error al obtener efectos CLT', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
