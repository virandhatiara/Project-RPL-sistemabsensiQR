<?php
/**
 * AiSettingModel — Pengelolaan Konfigurasi AI API Key & Model di Database
 */
class AiSettingModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Ambil pengaturan AI aktif saat ini
     */
    public function getSetting(): array {
        $this->db->query("SELECT * FROM ai_settings WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
        $row = $this->db->single();
        if ($row) {
            return $row;
        }

        // Default jika tabel kosong
        return [
            'id'          => 0,
            'provider'    => 'nvidia',
            'api_key'     => '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',
            'api_url'     => 'https://integrate.api.nvidia.com/v1/chat/completions',
            'model'       => 'meta/llama-3.2-11b-vision-instruct',
            'temperature' => 0.7,
            'max_tokens'  => 1024,
            'is_active'   => 1
        ];
    }

    /**
     * Simpan atau perbarui konfigurasi AI
     */
    public function saveSetting(array $data): bool {
        $provider    = trim($data['provider'] ?? 'nvidia');
        $apiKey      = trim($data['api_key'] ?? '');
        $apiUrl      = trim($data['api_url'] ?? '');
        $model       = trim($data['model'] ?? '');
        $temperature = isset($data['temperature']) ? (float)$data['temperature'] : 0.7;
        $maxTokens   = isset($data['max_tokens']) ? (int)$data['max_tokens'] : 1024;

        if (empty($apiKey)) {
            return false;
        }

        // Tentukan default API URL jika kosong
        if (empty($apiUrl)) {
            $defaultUrls = [
                'nvidia'     => 'https://integrate.api.nvidia.com/v1/chat/completions',
                'groq'       => 'https://api.groq.com/openai/v1/chat/completions',
                'openai'     => 'https://api.openai.com/v1/chat/completions',
                'openrouter' => 'https://openrouter.ai/api/v1/chat/completions'
            ];
            $apiUrl = $defaultUrls[$provider] ?? 'https://integrate.api.nvidia.com/v1/chat/completions';
        }

        // Cek apakah ada record di tabel
        $this->db->query("SELECT id FROM ai_settings ORDER BY id DESC LIMIT 1");
        $existing = $this->db->single();

        if ($existing) {
            $this->db->query("
                UPDATE ai_settings 
                SET provider = :provider,
                    api_key = :api_key,
                    api_url = :api_url,
                    model = :model,
                    temperature = :temperature,
                    max_tokens = :max_tokens,
                    is_active = 1,
                    updated_at = NOW()
                WHERE id = :id
            ");
            $this->db->bind('id', $existing['id']);
        } else {
            $this->db->query("
                INSERT INTO ai_settings (provider, api_key, api_url, model, temperature, max_tokens, is_active)
                VALUES (:provider, :api_key, :api_url, :model, :temperature, :max_tokens, 1)
            ");
        }

        $this->db->bind('provider', $provider);
        $this->db->bind('api_key', $apiKey);
        $this->db->bind('api_url', $apiUrl);
        $this->db->bind('model', $model);
        $this->db->bind('temperature', $temperature);
        $this->db->bind('max_tokens', $maxTokens);

        return $this->db->execute();
    }

    /**
     * Memanggil endpoint API /v1/models untuk menarik daftar model yang didukung oleh API key tersebut
     */
    public function fetchAvailableModels(string $apiKey, string $apiUrl, string $provider = 'nvidia'): array {
        $apiKey = trim($apiKey);
        $apiUrl = trim($apiUrl);

        if (empty($apiKey)) {
            return ['success' => false, 'error' => 'API Key tidak boleh kosong.'];
        }

        // Tentukan endpoint models
        $modelsUrl = '';
        if ($provider === 'nvidia' || str_contains($apiUrl, 'nvidia.com')) {
            $modelsUrl = 'https://integrate.api.nvidia.com/v1/models';
        } elseif ($provider === 'groq' || str_contains($apiUrl, 'groq.com')) {
            $modelsUrl = 'https://api.groq.com/openai/v1/models';
        } elseif ($provider === 'openai' || str_contains($apiUrl, 'openai.com')) {
            $modelsUrl = 'https://api.openai.com/v1/models';
        } elseif ($provider === 'openrouter' || str_contains($apiUrl, 'openrouter.ai')) {
            $modelsUrl = 'https://openrouter.ai/api/v1/models';
        } else {
            // Coba derivasi dari apiUrl
            if (str_ends_with($apiUrl, '/chat/completions')) {
                $modelsUrl = substr($apiUrl, 0, -strlen('/chat/completions')) . '/models';
            } else {
                $modelsUrl = rtrim($apiUrl, '/') . '/models';
            }
        }

        $ch = curl_init($modelsUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $apiKey,
                'Accept: application/json'
            ],
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $response  = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return ['success' => false, 'error' => 'Gagal menghubungi server API: ' . $curlError];
        }

        if ($httpCode === 401 || $httpCode === 403) {
            return ['success' => false, 'error' => 'API Key tidak valid atau otorisasi ditolak (HTTP ' . $httpCode . ').'];
        }

        if ($httpCode !== 200) {
            return ['success' => false, 'error' => 'Server merespons dengan HTTP ' . $httpCode];
        }

        $data = json_decode($response, true);
        if (empty($data['data']) || !is_array($data['data'])) {
            return ['success' => false, 'error' => 'Format daftar model dari API tidak sesuai.'];
        }

        $models = [];
        foreach ($data['data'] as $item) {
            if (!empty($item['id'])) {
                $models[] = $item['id'];
            }
        }

        sort($models);

        return [
            'success' => true,
            'models'  => $models,
            'count'   => count($models)
        ];
    }

    /**
     * Daftar preset model rekomendasi untuk masing-masing provider
     */
    public function getPresetModels(): array {
        return [
            'nvidia' => [
                'meta/llama-3.2-11b-vision-instruct',
                'meta/llama-3.3-70b-instruct',
                'meta/llama-3.1-8b-instruct',
                'meta/llama-3.1-70b-instruct',
                'mistralai/mistral-large-2-instruct',
                'nvidia/nemotron-4-340b-instruct',
                'google/gemma-2-9b-it'
            ],
            'groq' => [
                'llama-3.3-70b-versatile',
                'llama-3.1-8b-instant',
                'llama3-70b-8192',
                'llama3-8b-8192',
                'mixtral-8x7b-32768',
                'gemma2-9b-it'
            ],
            'openai' => [
                'gpt-4o-mini',
                'gpt-4o',
                'gpt-4-turbo',
                'gpt-3.5-turbo'
            ],
            'openrouter' => [
                'meta-llama/llama-3.3-70b-instruct:free',
                'meta-llama/llama-3.2-3b-instruct:free',
                'google/gemini-2.0-flash-exp:free',
                'deepseek/deepseek-chat'
            ]
        ];
    }
}
