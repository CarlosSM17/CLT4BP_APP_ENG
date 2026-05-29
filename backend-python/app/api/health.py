from fastapi import APIRouter, HTTPException
from app.core.config import settings
import anthropic

router = APIRouter()

@router.get("/health")
async def health_check():
    """
    Endpoint de health check
    """
    return {
        "status": "healthy",
        "app": settings.APP_NAME,
        "version": settings.APP_VERSION
    }

@router.get("/health/claude")
async def claude_health_check():
    """
    Verificar conectividad con Claude API
    """
    if not settings.CLAUDE_API_KEY:
        raise HTTPException(
            status_code=500,
            detail="API Key Claude not configured"
        )
    
    try:
        client = anthropic.Anthropic(api_key=settings.CLAUDE_API_KEY)
        
        # Test básico de conexión
        message = client.messages.create(
            model=settings.CLAUDE_MODEL,
            max_tokens=10,
            messages=[
                {"role": "user", "content": "test"}
            ]
        )
        
        return {
            "status": "connected",
            "model": settings.CLAUDE_MODEL,
            "response": "OK"
        }
    except Exception as e:
        raise HTTPException(
            status_code=500,
            detail=f"Error connecting to Claude API: {str(e)}"
        )