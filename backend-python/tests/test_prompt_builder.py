import pytest
from app.services.prompt_builder import PromptBuilder
from app.models.prompts import MaterialGenerationRequest, MaterialType, ProfileType, LearningObjective

def test_build_system_prompt():
    builder = PromptBuilder()
    prompt = builder.build_system_prompt()
    
    assert "diseñador instruccional" in prompt.lower()
    assert "clt" in prompt.lower() or "carga cognitiva" in prompt.lower()
    assert "arcs" in prompt.lower()

def test_build_complete_prompt():
    builder = PromptBuilder()
    
    request = MaterialGenerationRequest(
        course_id=1,
        profile_type=ProfileType.GROUP,
        profile_data={
            "group_summary": {
                "predominant_knowledge": "bajo",
                "predominant_motivation": "medio",
                "predominant_strategies": "medio",
                "group_characteristics": "Grupo heterogéneo"
            },
            "knowledge_averages": {
                "overall": {"level": "bajo", "average": 45}
            },
            "teaching_recommendations": ["Usar ejemplos resueltos"]
        },
        learning_objectives=[
            LearningObjective(id=1, description="Comprender variables")
        ],
        selected_clt_effects=["worked_example", "split_attention"],
        material_type=MaterialType.EXAMPLE,
        topic="Variables en Python"
    )
    
    system_prompt, user_prompt = builder.build_complete_prompt(request)
    
    assert len(system_prompt) > 0
    assert len(user_prompt) > 0
    assert "Variables en Python" in user_prompt
    assert "ejemplo resuelto" in user_prompt.lower() or "worked example" in user_prompt.lower()