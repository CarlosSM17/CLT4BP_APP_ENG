# app/routers/materials.py
from fastapi import APIRouter, HTTPException
from app.models.prompts import MaterialGenerationRequest, GeneratedMaterial
from app.services.material_generator import MaterialGeneratorService
import logging

router = APIRouter()
logger = logging.getLogger(__name__)

@router.post("/generate", response_model=GeneratedMaterial)
async def generate_material(request: MaterialGenerationRequest):
    """
    Genera material instruccional usando Claude API
    """
    try:
        generator = MaterialGeneratorService()
        material = await generator.generate_material(request)
        
        # Validar contenido generado
        validation = await generator.validate_generated_content(material)
        
        if not validation["valid"]:
            logger.warning(f"Material generated with validation errors: {validation['errors']}")
        
        return material
        
    except Exception as e:
        logger.error(f"Error generating material: {e}")
        raise HTTPException(status_code=500, detail=str(e))

@router.post("/validate")
async def validate_connection():
    """
    Valida la conexión con Claude API
    """
    try:
        from app.services.claude_service import ClaudeService
        claude = ClaudeService()
        is_valid = await claude.validate_api_connection()
        
        return {
            "success": is_valid,
            "message": "Successful connection" if is_valid else "Connection error"
        }
    except Exception as e:
        logger.error(f"Error validating connection: {e}")
        raise HTTPException(status_code=500, detail=str(e))

@router.get("/clt-effects")
async def get_clt_effects():
    """
    Obtiene la lista de efectos CLT disponibles
    """
    from app.services.prompt_builder import PromptBuilder
    builder = PromptBuilder()
    
    effects = []
    for effect_id, effect_data in builder.CLT_EFFECTS.items():
        effects.append({
            "id": effect_id,
            "name": effect_data.name,
            "description": effect_data.description,
            "category": effect_data.category,
            "application_guidance": effect_data.application_guidance
        })
    
    return {
        "effects": effects,
        "categories": {
            "new_knowledge": "Effects for New Knowledge",
            "prior_knowledge": "Effects for Prior Knowledge"
        }
    }