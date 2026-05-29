# app/services/material_generator.py
import json
import logging
from typing import Dict
from app.services.claude_service import ClaudeService
from app.services.prompt_builder import PromptBuilder
from app.models.prompts import MaterialGenerationRequest, GeneratedMaterial

logger = logging.getLogger(__name__)

class MaterialGeneratorService:
    def __init__(self):
        self.claude_service = ClaudeService()
        self.prompt_builder = PromptBuilder()
    
    async def generate_material(
        self,
        request: MaterialGenerationRequest
    ) -> GeneratedMaterial:
        """
        Genera material instruccional usando Claude API
        """
        try:
            logger.info(f"Generating type material {request.material_type} for course {request.course_id}")
            
            # Construir prompts
            system_prompt, user_prompt = self.prompt_builder.build_complete_prompt(request)
            
            logger.debug(f"System prompt length: {len(system_prompt)} chars")
            logger.debug(f"User prompt length: {len(user_prompt)} chars")
        
            # Llamar a Claude API
            response = await self.claude_service.generate_content(
                prompt=user_prompt,
                system_prompt=system_prompt
            )
            
            if not response.get("success"):
                raise Exception(f"Error in generation: {response.get('error')}")
            
            # Parsear respuesta JSON
            content_text = response["content"]
            content_json = self._parse_json_response(content_text)
            
            # Crear objeto de material generado
            material = GeneratedMaterial(
                material_type=request.material_type,
                content=content_json,
                metadata={
                    "course_id": request.course_id,
                    "profile_type": request.profile_type,
                    "student_id": request.student_id,
                    "topic": request.topic,
                    "clt_effects_applied": request.selected_clt_effects,
                    "generated_at": None  # Se agregará en Laravel
                },
                token_usage=response.get("usage")
            )
            
            logger.info(f"Material successfully generated. Tokens:{response.get('usage', {}).get('total_tokens')}")
            
            return material
            
        except json.JSONDecodeError as e:
            logger.error(f"Error parsing Claude's JSON: {e}")
            logger.error(f"Received response: {content_text[:500]}...")
            raise Exception(f"Claude's response is not valid JSON: {str(e)}")
            
        except Exception as e:
            logger.error(f"Error generating material: {e}")
            raise
    
    def _parse_json_response(self, response_text: str) -> Dict:
        """
        Parsea la respuesta de Claude, limpiando markdown si es necesario
        """
        # Limpiar markdown code blocks si existen
        cleaned = response_text.strip()
        
        if cleaned.startswith("```json"):
            cleaned = cleaned[7:]  # Remover ```json
        elif cleaned.startswith("```"):
            cleaned = cleaned[3:]  # Remover ```
        
        if cleaned.endswith("```"):
            cleaned = cleaned[:-3]
        
        cleaned = cleaned.strip()
        
        # Parsear JSON
        try:
            return json.loads(cleaned)
        except json.JSONDecodeError:
            # Intentar encontrar el JSON dentro del texto
            start = cleaned.find('{')
            end = cleaned.rfind('}') + 1
            if start != -1 and end > start:
                return json.loads(cleaned[start:end])
            raise
    
    async def validate_generated_content(self, material: GeneratedMaterial) -> Dict:
        """
        Valida que el contenido generado tenga la estructura esperada
        """
        validation_result = {
            "valid": True,
            "errors": [],
            "warnings": []
        }
        
        content = material.content
        material_type = material.material_type
        
        # Validaciones según tipo de material
        if material_type == "learning_tasks":
            if "tasks" not in content:
                validation_result["valid"] = False
                validation_result["errors"].append("Missing field 'tasks'")
            elif not isinstance(content["tasks"], list) or len(content["tasks"]) == 0:
                validation_result["valid"] = False
                validation_result["errors"].append("'tasks' must be a non-empty list")
        
        elif material_type == "support_info":
            if "sections" not in content:
                validation_result["valid"] = False
                validation_result["errors"].append("Missing field 'sections'")
        
        elif material_type == "procedural_info":
            if "worked_examples" not in content:
                validation_result["warnings"].append("It is recommended to include 'worked_examples'")
        
        elif material_type == "verbal_protocols":
            if "think_aloud_transcript" not in content:
                validation_result["valid"] = False
                validation_result["errors"].append("Missing field 'think_aloud_transcript'")
        
        elif material_type == "example":
            if "code" not in content:
                validation_result["warnings"].append("It is recommended to include field 'code'")
        
        return validation_result