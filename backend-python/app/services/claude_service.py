# app/services/claude_service.py
import anthropic
import logging
import time
from typing import Dict, Optional
from app.config import get_settings

settings = get_settings()
logger = logging.getLogger(__name__)

class ClaudeService:
    def __init__(self):
        self.client = anthropic.Anthropic(api_key=settings.anthropic_api_key)
        self.model = settings.claude_model
        self.max_tokens = settings.max_tokens
        self.temperature = settings.temperature
    
    async def generate_content(
        self,
        prompt: str,
        system_prompt: Optional[str] = None,
        max_retries: int = None
    ) -> Dict:
        """
        Genera contenido usando Claude API con reintentos automáticos
        """
        max_retries = max_retries or settings.max_retries
        retry_delay = settings.retry_delay
        
        for attempt in range(max_retries):
            try:
                logger.info(f"Call to Claude API (attempt {attempt + 1}/{max_retries})")
                
                message_params = {
                    "model": self.model,
                    "max_tokens": self.max_tokens,
                    "temperature": self.temperature,
                    "messages": [
                        {
                            "role": "user",
                            "content": prompt
                        }
                    ]
                }
                
                if system_prompt:
                    message_params["system"] = system_prompt
                
                response = self.client.messages.create(**message_params)
                
                # Extraer contenido de la respuesta
                content = self._extract_content(response)
                
                # Información de uso de tokens
                usage = {
                    "input_tokens": response.usage.input_tokens,
                    "output_tokens": response.usage.output_tokens,
                    "total_tokens": response.usage.input_tokens + response.usage.output_tokens
                }
                
                logger.info(f"Successful generation. Tokens used: {usage['total_tokens']}")
                
                return {
                    "success": True,
                    "content": content,
                    "usage": usage,
                    "model": response.model,
                    "stop_reason": response.stop_reason
                }
                
            except anthropic.RateLimitError as e:
                logger.warning(f"Rate limit reached: {e}")
                if attempt < max_retries - 1:
                    wait_time = retry_delay * (2 ** attempt)  # Exponential backoff
                    logger.info(f"Waiting {wait_time} seconds before retrying...")
                    time.sleep(wait_time)
                else:
                    logger.error("Maximum number of retries reached due to rate limit")
                    raise
                    
            except anthropic.APIError as e:
                logger.error(f"Claude's API error: {e}")
                if attempt < max_retries - 1:
                    time.sleep(retry_delay)
                else:
                    raise
                    
            except Exception as e:
                logger.error(f"Unexpected error when calling Claude API: {e}")
                raise
        
        return {
            "success": False,
            "error": "Maximum number of retries reached"
        }
    
    def _extract_content(self, response) -> str:
        """
        Extrae el contenido de texto de la respuesta de Claude
        """
        if not response.content:
            return ""
        
        # Claude puede devolver múltiples bloques de contenido
        text_content = []
        for block in response.content:
            if hasattr(block, 'text'):
                text_content.append(block.text)
        
        return "\n".join(text_content)
    
    async def validate_api_connection(self) -> bool:
        """
        Valida que la conexión con Claude API funciona correctamente
        """
        try:
            test_prompt = "Reply with 'OK' if you receive this message."
            result = await self.generate_content(test_prompt)
            return result.get("success", False)
        except Exception as e:
            logger.error(f"Error validating connection with Claude API: {e}")
            return False