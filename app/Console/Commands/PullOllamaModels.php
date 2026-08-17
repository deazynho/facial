<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class PullOllamaModels extends Command
{
    protected $signature = 'ollama:pull-models';
    protected $description = 'Pull Ollama llama3.2 model';

    public function handle()
    {
        $baseUrl = env('OLLAMA_API_URL') ?? ('http://' . env('OLLAMA_HOST', '127.0.0.1:11434'));
        $model = 'llama3.2';

        $this->info("Pulling {$model} model...");
        
        try {
            $response = Http::timeout(600)
                ->post("{$baseUrl}/api/pull", [
                    'name' => $model,
                ]);

            if ($response->successful()) {
                $this->line("✓ {$model} pulled successfully", 'info');
            } else {
                $this->error("Failed to pull {$model}: " . $response->status());
            }
        } catch (\Exception $e) {
            $this->error("Error pulling {$model}: " . $e->getMessage());
        }

        // List all available models
        $this->info("\nAvailable models:");
        try {
            $response = Http::get("{$baseUrl}/api/tags");
            if ($response->successful()) {
                $models = $response->json()['models'] ?? [];
                foreach ($models as $model) {
                    $this->line("  - " . $model['name']);
                }
            }
        } catch (\Exception $e) {
            $this->error("Error fetching models: " . $e->getMessage());
        }
    }
}

