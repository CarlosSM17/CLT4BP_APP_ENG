# app/main.py
from fastapi import FastAPI, HTTPException, Depends, Header
from fastapi.middleware.cors import CORSMiddleware
from contextlib import asynccontextmanager
import logging
from app.config import get_settings
from app.routers import materials

# Configurar logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
)

settings = get_settings()

@asynccontextmanager
async def lifespan(app: FastAPI):
    # Startup
    logging.info(f"Getting started {settings.app_name}...")
    yield
    # Shutdown
    logging.info("Closing application...")

app = FastAPI(
    title=settings.app_name,
    version=settings.api_version,
    lifespan=lifespan
)

# CORS
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # Configurar apropiadamente en producción
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Dependency para autenticación
async def verify_api_token(x_api_token: str = Header(...)):
    if x_api_token != settings.laravel_api_token:
        raise HTTPException(status_code=401, detail="Invalid API token")
    return True

# Routers
app.include_router(
    materials.router,
    prefix="/api/v1/materials",
    tags=["materials"],
    dependencies=[Depends(verify_api_token)]
)

@app.get("/")
async def root():
    return {
        "service": settings.app_name,
        "version": settings.api_version,
        "status": "running"
    }

@app.get("/health")
async def health_check():
    return {"status": "healthy"}