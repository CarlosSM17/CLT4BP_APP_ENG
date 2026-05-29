import pytest
from httpx import AsyncClient
from app.main import app, verify_api_token

# Override authentication for testing
async def mock_verify_api_token():
    return True

app.dependency_overrides[verify_api_token] = mock_verify_api_token

@pytest.mark.asyncio
async def test_health_endpoint():
    async with AsyncClient(app=app, base_url="http://test") as ac:
        response = await ac.get("/health")
    assert response.status_code == 200
    assert response.json()["status"] == "healthy"

@pytest.mark.asyncio
async def test_clt_effects_endpoint():
    async with AsyncClient(app=app, base_url="http://test") as ac:
        response = await ac.get("/api/v1/materials/clt-effects")

    assert response.status_code == 200
    data = response.json()
    assert "effects" in data
    assert len(data["effects"]) > 0