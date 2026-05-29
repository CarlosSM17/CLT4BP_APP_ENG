from pydantic_settings import BaseSettings, SettingsConfigDict
from functools import lru_cache

class Settings(BaseSettings):
    # API Configuration
    app_name: str = "CLT4BP AI Service"
    app_version: str = "1.0.0"
    api_version: str = "v1"
    debug: bool = False

    # Claude API
    anthropic_api_key: str
    claude_model: str = "claude-sonnet-4-20250514"
    max_tokens: int = 4000
    temperature: float = 0.7

    # Laravel Communication
    laravel_api_url: str
    laravel_api_token: str

    # Rate Limiting
    max_retries: int = 3
    retry_delay: int = 2  # seconds
    timeout: int = 120  # seconds

    # Logging
    log_level: str = "INFO"
    log_file: str = "logs/ai_service.log"

    model_config = SettingsConfigDict(
        env_file=".env",
        case_sensitive=False,
        extra="ignore"
    )

@lru_cache()
def get_settings():
    return Settings()
